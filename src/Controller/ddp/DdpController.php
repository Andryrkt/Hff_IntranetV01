<?php

namespace App\Controller\ddp;

use App\Constants\ddp\StatutConstants;
use App\Constants\ddp\TypeDemandePaiementConstants;
use App\Controller\Controller;
use App\Controller\Traits\PdfConversionTrait;
use App\Dto\ddp\DdpDto;
use App\Entity\admin\ddp\TypeDemande;
use App\Entity\cde\CdefnrSoumisAValidation;
use App\Entity\ddp\DemandePaiement;
use App\Factory\ddp\DdpFactory;
use App\Form\ddp\DdpType;
use App\Model\da\DaModel;
use App\Model\ddp\DemandePaiementModel;
use App\Service\ddp\CommandeLivraisonService;
use App\Service\ddp\DemandePaiementCommandeService;
use App\Service\ddp\Magasin\DdpDocService;
use App\Service\ddp\Magasin\DdpLigneService;
use App\Service\ddp\Magasin\DdpService;
use App\Service\fichier\TraitementDeFichier;
use App\Service\genererPdf\GeneratePdfDdp;
use App\Service\historiqueOperation\HistoriqueOperationDDPService;
use App\Service\historiqueOperation\suivi\SuiviOperationService;
use App\Service\TableauEnStringService;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/ddp")
 */
class DdpController extends Controller
{
    use PdfConversionTrait;

    private DdpFactory $ddpFactory;
    private DdpService $ddpService;
    private DdpLigneService $ddpLigneService;
    private DdpDocService $ddpDocService;
    private DemandePaiementModel $demandePaiementModel;
    private HistoriqueOperationDDPService $historiqueOperation;


    public function __construct(
        DdpFactory $ddpFactory,
        DdpService $ddpService,
        DdpLigneService $ddpLigneService,
        DdpDocService $ddpDocService,
        DemandePaiementModel $demandePaiementModel,
        HistoriqueOperationDDPService $historiqueOperation
    ) {
        parent::__construct();
        $this->ddpFactory = $ddpFactory;
        $this->ddpService = $ddpService;
        $this->ddpLigneService = $ddpLigneService;
        $this->ddpDocService = $ddpDocService;
        $this->demandePaiementModel = $demandePaiementModel;
        $this->historiqueOperation = $historiqueOperation;
    }

    /**
     * @Route("/new/{typeDdp}", name="new_ddp")
     */
    public function new(int $typeDdp, Request $request)
    {
        // initialisation DTO
        $dto = $this->ddpFactory->initialisation($typeDdp);
        //Creation du formulaire
        $form = $this->getFormFactory()->createBuilder(DdpType::class, $dto)->getForm();
        // Traitement du formulaire
        $this->traitementDuFormulaire($form,  $request);
        return $this->render('ddp/magasin/new.html.twig', [
            'form' => $form->createView(),
            'id_type' => $typeDdp
        ]);
    }

    private function traitementDuFormulaire(FormInterface $form, Request $request)
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var DdpDto $dto */
            $dto = $form->getData();

            // valeurs postées, concaténées dans les requêtes IPS
            foreach ((array) $dto->numeroCommande as $numCde) {
                if (trim((string) $numCde) !== '' && !ctype_digit(trim((string) $numCde))) {
                    $form->addError(new FormError('Le numéro de commande ne doit contenir que des chiffres.'));
                    return;
                }
            }

            // à l'avance : le montant total du commande vient d'IPS, jamais de la valeur postée
            if ($dto->typeDdp->getId() === TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_A_L_AVANCE) {
                $numeroProforma = trim((string) $dto->numeroFactureProforma);
                if ($numeroProforma === '' || mb_strlen($numeroProforma) > 50) {
                    $form->get('numeroFactureProforma')->addError(new FormError('Le n° facture proforma est obligatoire (50 caractères maximum).'));
                    return;
                }
                $dto->numeroFacture = [$numeroProforma];

                $montantTotal =$this->demandePaiementModel->getMontantTotalCommande((string) $dto->numeroFournisseur, (string) ($dto->numeroCommande[0] ?? ''));
                if ($montantTotal === null || $montantTotal <= 0) {
                    $form->get('montantTotalCommande')->addError(new FormError('Le montant total de cette commande est introuvable dans IPS pour ce fournisseur.'));
                    return;
                }
                $dto->montantTotalCommande = (string) $montantTotal;
            }

            $erreurCommandeSoldee = $this->erreurCommandeSoldee($dto);
            if ($erreurCommandeSoldee !== null) {
                $form->addError(new FormError($erreurCommandeSoldee));
                return;
            }

            $erreurAvanceEnCours = $this->erreurAvanceEnCours($dto);
            if ($erreurAvanceEnCours !== null) {
                $form->addError(new FormError($erreurAvanceEnCours));
                return;
            }

            // après arrivage : un montant total doit être saisi pour chaque commande
            if ($dto->typeDdp->getId() === TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE) {
                $erreurFacture = $this->erreurFactureDejaDemandee($dto);
                if ($erreurFacture !== null) {
                    $form->addError(new FormError($erreurFacture));
                    return;
                }

                $erreurCommandeNonValidee = $this->erreurCommandeNonValidee($dto);
                if ($erreurCommandeNonValidee !== null) {
                    $form->addError(new FormError($erreurCommandeNonValidee));
                    return;
                }

                // le select n° commande est disabled côté front donc non posté : commandes déduites des factures
                $dto->numeroCommande = $this->ddpFactory->recuperationNumCommande($dto->numeroFournisseur, $dto->numeroFacture);
                // montant réceptionné : affiché sans saisie, lu dans IPS pour les factures sélectionnées
                $dto->montantsCommande = array_map(
                    fn($montant) => number_format((float) $montant, 2, ',', ''),
                    empty($dto->numeroCommande) ? [] : $this->demandePaiementModel->getMontantCommandeReceptionnee($dto->numeroFournisseur, $dto->numeroCommande, $dto->numeroFacture, $dto->codeSociete)
                );
                foreach ($dto->numeroCommande as $numCde) {
                    if ($dto->montantCommande($numCde) === null) {
                        $form->addError(new FormError("Le montant réceptionné de la commande $numCde est introuvable dans IPS pour la/les facture(s) sélectionnée(s)."));
                        return;
                    }
                }

                $erreurDepassement = $this->erreurReceptionDepasseSolde($dto);
                if ($erreurDepassement !== null) {
                    $form->addError(new FormError($erreurDepassement));
                    return;
                }
            }

            $erreurMontant = $this->erreurMontant($dto);
            if ($erreurMontant !== null) {
                $form->get('montantAPayer')->addError(new FormError($erreurMontant));
                return;
            }

            if ($dto->typeDdp->getId() === TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE) {
                $this->soumissionApresArrivage($form, $dto, trim((string) $request->request->get('montantTotalAPayerSaisi', '')));
                return;
            }

            // suivi étape par étape de l'enregistrement (admin > historique > suivi des opérations)
            $suivi = SuiviOperationService::demarrer(SuiviOperationService::TYPE_DDP);
            $dto = $this->numeroEtFichiers($suivi, $form, $dto);

            $this->enregistrementEtPdf($suivi, $dto);

            /** HISTORISATION */
            $this->notificationSoumission($dto->numeroDdp);
        }
    }

    private function numeroEtFichiers(SuiviOperationService $suivi, FormInterface $form, DdpDto $dto): DdpDto
    {
        $dto = $suivi->etape('Numéro et fichiers joints', fn() => $this->ddpFactory->apresSoumission($form, $dto));
        $suivi->setNumeroDocument((string) $dto->numeroDdp);

        return $dto;
    }

    private function enregistrementEtPdf(SuiviOperationService $suivi, DdpDto $dto): void
    {
        // Enregistrement dans BD
        $suivi->etape('Base de données', function () use ($dto) {
            $this->enregistrementSurBd($dto);
        });
        // Enregistrement des fichiers, generation du PDF et fusion des fichiers
        $this->traitementDeFichier($dto);
    }

    /**
     * Après arrivage : une seule demande de paiement (type 2) et une seule page de garde
     * par saisie. Solde avance et montant à payer restent calculés commande par commande
     * (DemandePaiementRepository::getSituationAvanceParCommande).
     */
    private function soumissionApresArrivage(FormInterface $form, DdpDto $dto, string $montantSaisi = ''): void
    {
        if (empty($dto->numeroCommande)) {
            $form->addError(new FormError('Aucune commande trouvée pour la/les facture(s) sélectionnée(s).'));
            return;
        }

        if ($montantSaisi !== '' && !preg_match('/^\d[\d ]*(,\d*)?$/', $montantSaisi)) {
            $form->addError(new FormError('Le montant total à payer doit être un nombre positif.'));
            return;
        }

        // suivi étape par étape de l'enregistrement (admin > historique > suivi des opérations)
        $suivi = SuiviOperationService::demarrer(SuiviOperationService::TYPE_DDP);

        $suivi->etape('Soldes et montants des commandes', function () use ($dto) {
            $repository = $this->getEntityManager()->getRepository(DemandePaiement::class);
            foreach (array_unique($dto->numeroCommande) as $numCde) {
                $numCde = (string) $numCde;
                $situation = $repository->getSituationAvanceParCommande($numCde, $dto->codeSociete);
                $dto->soldesAvance[$numCde] = $situation['soldeAvance'];
                $dto->montantsTotalCommande[$numCde] = $this->demandePaiementModel->getMontantTotalCommande((string) $dto->numeroFournisseur, $numCde);
                if ($situation['typeDdpAGenerer'] === TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_REGULE) {
                    $dto->commandesRegule[] = $numCde;
                }
            }
        });

        $suivi->etape('Factures et livraisons', function () use ($dto) {
            $dto->lignesFactures = $this->demandePaiementModel->getCommandesParFacture(
                TableauEnStringService::TableauEnString(',', $dto->numeroFacture)
            );
            foreach ($this->demandePaiementModel->getLivraisonsReceptionnees($dto->numeroFournisseur, $dto->numeroCommande, $dto->numeroFacture, $dto->codeSociete) as $ligne) {
                $dto->livraisonsParCommande[$ligne['numero_commande']][] = $ligne['numero_livraison'];
            }
        });

        $dto->montantAPayer = $this->montantAPayerApresArrivage($dto, $montantSaisi);

        // après numeroEtFichiers : DdpFactory::apresSoumission() dépend encore du type 2
        $dto = $this->numeroEtFichiers($suivi, $form, $dto);
        $this->appliquerTypeSelonMontants($dto);
        $dto->donneesPageDeGarde = $suivi->etape('Données de la page de garde', fn() => $this->donneesPageDeGarde($dto));

        $this->enregistrementEtPdf($suivi, $dto);

        $this->notificationSoumission($dto->numeroDdp);
    }

    /**
     * Après arrivage : montant à payer à 0 → DDPR (prioritaire), somme des soldes avance à 0 → BAP, sinon DDPL.
     * Le type décide aussi du dossier DocuWare à la transmission (GeneratePdf::copyToDWBapDa).
     */
    private function appliquerTypeSelonMontants(DdpDto $dto): void
    {
        if ($dto->montantAPayer() < 0.005) {
            $idType = TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_REGULE;
            $dto->statut = StatutConstants::DDPR_A_TRANSMETTRE;
        } elseif (array_sum($dto->soldesAvance) < 0.005) {
            $idType = TypeDemandePaiementConstants::ID_BON_A_PAYER;
            $dto->statut = StatutConstants::BAP_A_TRANSMETTRE;
        } else {
            return;
        }

        $dto->typeDdp = $this->getEntityManager()->getRepository(TypeDemande::class)->find($idType);
    }

    /**
     * Montant à payer de la DDP : le montant saisi s'il y en a un, sinon la somme des montants du commande à payer
     */
    private function montantAPayerApresArrivage(DdpDto $dto, string $montantSaisi): string
    {
        if ($montantSaisi !== '') {
            $dto->montantAPayer = $montantSaisi;
            $montant = $dto->montantAPayer();
        } else {
            $montant = array_sum(array_map(fn($numCde) => (float) $dto->montantCommandeAPayer((string) $numCde), $dto->numeroCommande));
        }

        return number_format($montant, 2, ',', '');
    }

    /**
     * Données des tableaux de la page de garde après arrivage (voir GeneratePdfDdp)
     */
    private function donneesPageDeGarde(DdpDto $dto): array
    {
        $daModel = new DaModel();
        $repository = $this->getEntityManager()->getRepository(DemandePaiement::class);

        $commandes = [];
        foreach ($dto->numeroCommande as $numCde) {
            $ddps = [];
            /** @var DemandePaiement $ddp */
            foreach ($repository->getDdpSelonNumCde($numCde, true, true) as $ddp) {
                $ddps[] = [
                    'date'    => $ddp->getDateCreation() ? $ddp->getDateCreation()->format('d/m/Y') : '',
                    'numero'  => $ddp->getNumeroDdp(),
                    'type'    => $ddp->getTypeDemandeId() ? $ddp->getTypeDemandeId()->getLibelle() : '',
                    'montant' => (float) $ddp->getMontantAPayers(),
                    'ratio'   => $ddp->getRatioMontantTotalCommande(),
                    'statut'  => $ddp->getStatut(),
                ];
            }

            $commandes[$numCde] = [
                'livraisons' => $daModel->getHistoriqueLivraison((string) $numCde) ?? [],
                'ddps'       => $ddps,
            ];
        }

        return [
            'commandes' => $commandes,
            'factures'  => $dto->lignesFactures,
        ];
    }

    private function notificationSoumission(string $numerosDdp): void
    {
        $this->historiqueOperation->sendNotificationSoumission(
            'Le document a été généré avec succès',
            $numerosDdp,
            'da_bon_a_payer',
            true,
            null,
            'devis_magasin_search',
            ['appro' => 0]
        );
    }

    /**
     * Vérifie le montant saisi par l'utilisateur, miroir de la vérification
     * déjà faite côté front (DemandePaiementManager.js::verifierMontant) :
     * - à l'avance : montant facture proforma plafonné ;
     * - après arrivage : égal au montant calculé des factures sélectionnées.
     *
     * @return string|null message d'erreur, null si le montant est correct
     */
    private function erreurMontant(DdpDto $dto): ?string
    {
        if (
            $dto->typeDdp->getId() === TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_A_L_AVANCE
            && $dto->montantAPayer() > TypeDemandePaiementConstants::PLAFOND_MONTANT_PROFORMA
        ) {
            return 'Le montant facture proforma ne doit pas dépasser ' . number_format(TypeDemandePaiementConstants::PLAFOND_MONTANT_PROFORMA, 0, ',', ' ') . '.';
        }

        $montantAttendu = $this->montantAttendu($dto);

        if ($montantAttendu === null || abs($dto->montantAPayer() - $montantAttendu) < 0.01) {
            return null;
        }

        return 'Le montant saisi ne correspond pas au montant calculé pour la/les commande(s)/facture(s) sélectionnée(s).';
    }

    /**
     * Paiement à l'avance : bloque la soumission si la commande sélectionnée est
     * déjà payée à 100 % par des demandes de paiement à l'avance validées, ou si
     * cette demande ferait dépasser 100 % à leur cumul.
     * Miroir de la vérification front (DemandePaiementManager.js::verifierMontantAvance).
     *
     * @return string|null message d'erreur, null si la soumission est autorisée
     */
    private function erreurCommandeSoldee(DdpDto $dto): ?string
    {
        if ($dto->typeDdp->getId() !== TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_A_L_AVANCE || empty($dto->numeroCommande)) {
            return null;
        }

        $numCde = (string) $dto->numeroCommande[0];
        // cumul des % (montant à payer / montant total du commande) des demandes à l'avance validées
        $ratioAvanceValide = $this->getEntityManager()
            ->getRepository(DemandePaiement::class)
            ->getCumulRatioDdpValideParCommande($numCde, $dto->codeSociete, TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_A_L_AVANCE);

        if ($ratioAvanceValide >= 100 - 0.01) {
            return sprintf(
                'La commande %s est déjà payée à 100 %% par des demandes de paiement à l\'avance validées : une nouvelle demande de paiement à l\'avance n\'est pas possible.',
                $numCde
            );
        }

        $montantTotal = $dto->montantTotalCommande();
        if ($montantTotal > 0) {
            $cumul = $ratioAvanceValide + $dto->montantAPayer() / $montantTotal * 100;
            if ($cumul > 100 + 0.01) {
                return sprintf(
                    'Le montant facture proforma de la commande %s porte le cumul des demandes de paiement à l\'avance validées à %s %% : il ne doit pas dépasser %s.',
                    $numCde,
                    number_format($cumul, 2, ',', ' '),
                    number_format((100 - $ratioAvanceValide) * $montantTotal / 100, 2, ',', ' ')
                );
            }
        }

        return null;
    }

    /**
     * Paiement à l'avance : bloque la soumission si la commande sélectionnée a déjà une
     * demande à l'avance en cours. Le front ne la propose plus (InfoFournisseurApi::numeroCommandeFournisseur).
     *
     * @return string|null message d'erreur, null si la soumission est autorisée
     */
    private function erreurAvanceEnCours(DdpDto $dto): ?string
    {
        if ($dto->typeDdp->getId() !== TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_A_L_AVANCE || empty($dto->numeroCommande)) {
            return null;
        }

        $numCde = trim((string) $dto->numeroCommande[0]);
        $commandesEnCours = $this->getEntityManager()
            ->getRepository(DemandePaiement::class)
            ->getCommandesAvecDdpAvanceEnCours((string) $dto->numeroFournisseur, $dto->codeSociete);

        if (in_array($numCde, $commandesEnCours, true)) {
            return sprintf('La commande %s a déjà une demande de paiement à l\'avance en cours.', $numCde);
        }

        return null;
    }

    /**
     * Après arrivage : bloque la soumission d'une facture qui a déjà une demande de paiement
     * non refusée. Le front ne la propose plus (InfoFournisseurApi::numeroCommandeFournisseur).
     *
     * @return string|null message d'erreur, null si la soumission est autorisée
     */
    private function erreurFactureDejaDemandee(DdpDto $dto): ?string
    {
        $ddpParFacture = $this->getEntityManager()
            ->getRepository(DemandePaiement::class)
            ->getDdpParFactureNonRefusee((string) $dto->numeroFournisseur, $dto->codeSociete);

        foreach ($dto->numeroFacture as $numFac) {
            $numeroDdp = $ddpParFacture[trim((string) $numFac)] ?? null;
            if ($numeroDdp !== null) {
                return sprintf('La facture %s a déjà une demande de paiement en cours ou validée (%s).', $numFac, $numeroDdp);
            }
        }

        return null;
    }

    /**
     * Après arrivage : bloque la soumission d'une facture dont une commande n'est pas validée
     * dans DocuWare. Le front ne la propose plus (InfoFournisseurApi::numeroCommandeFournisseur).
     *
     * @return string|null message d'erreur, null si la soumission est autorisée
     */
    private function erreurCommandeNonValidee(DdpDto $dto): ?string
    {
        $commandesValidees = array_map(
            fn(CdefnrSoumisAValidation $commande) => trim((string) $commande->getNumCdeFournisseur()),
            $this->getEntityManager()->getRepository(CdefnrSoumisAValidation::class)->findValideesDerniereVersion((string) $dto->numeroFournisseur)
        );
        $facturesNonValidees = $this->demandePaiementModel->getFacturesAvecCommandeHorsListe($dto->numeroFacture, $commandesValidees);

        foreach ($dto->numeroFacture as $numFac) {
            $numCde = $facturesNonValidees[trim((string) $numFac)] ?? null;
            if ($numCde !== null) {
                return sprintf('La facture %s est liée à la commande %s, qui n\'est pas validée dans DocuWare.', $numFac, $numCde);
            }
        }

        return null;
    }

    /**
     * Après arrivage : bloque la soumission si le montant réceptionné d'une commande payée
     * à 100 % par les DDPA dépasse son solde avance.
     * Miroir de la vérification front (DemandePaiementManager.js::verifierMontant).
     *
     * @return string|null message d'erreur, null si la soumission est autorisée
     */
    private function erreurReceptionDepasseSolde(DdpDto $dto): ?string
    {
        $repository = $this->getEntityManager()->getRepository(DemandePaiement::class);

        foreach (array_unique($dto->numeroCommande) as $numCde) {
            $numCde = (string) $numCde;
            $situation = $repository->getSituationAvanceParCommande($numCde, $dto->codeSociete);
            $receptionne = (float) $dto->montantCommande($numCde);

            if ($situation['typeDdpAGenerer'] === TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_REGULE && $receptionne - $situation['soldeAvance'] > 0.01) {
                return sprintf(
                    'Le montant réceptionné de la commande %s (%s) dépasse son solde avance (%s).',
                    $numCde,
                    number_format($receptionne, 2, ',', ' '),
                    number_format($situation['soldeAvance'], 2, ',', ' ')
                );
            }
        }

        return null;
    }

    private function montantAttendu(DdpDto $dto): ?float
    {
        if ($dto->typeDdp->getId() === TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE) {
            if (empty($dto->numeroCommande)) {
                return null;
            }

            // somme des montants du commande réceptionnés (numeroCommande déjà déduit des factures)
            return array_sum(array_map(fn($numCde) => (float) $dto->montantCommande($numCde), $dto->numeroCommande));
        }

        return null;
    }

    private function traitementDeFichier(DdpDto $dto): string
    {
        // NB : pour le type "après arrivage", $dto->numeroCommande est déjà
        // renseigné (à partir des factures) par DdpFactory::apresSoumission().

        /** TRAITEMENT FICHIER  AUTRE DOCUMENT ET BC client externe / BC client magasin*/
        if ($dto->pieceJoint04 !== null) {
            $dto->estAutreDoc = true;
            $dto->nomAutreDoc = $dto->pieceJoint04->getClientOriginalName();
        }

        if ($dto->pieceJoint03 !== null || !empty($dto->pieceJoint03)) {
            $nomFichierBCs = [];
            foreach ($dto->pieceJoint03 as $value) {
                $nomFichierBCs[] = $value->getClientOriginalName();
            }
            $dto->estCdeClientExterneDoc = true;
            $dto->nomCdeClientExterneDoc = $nomFichierBCs;
        }

        /** ENREGISTREMENT DU FICHIER */
        $nomAvecCheminFichier = $dto->nomAvecCheminFichier;
        $nomFichier = $dto->nomFichier;
        // fichiers distants copiés (192.168.0.15) + fichiers téléversés + fichiers de commande DW
        $dto->lesFichiers = $dto->getToutesLesNomFichiers();
        // generation de la page de garde DDP
        SuiviOperationService::suivre('Page de garde', function () use ($dto, $nomAvecCheminFichier) {
            $this->pageDeGarde($dto, $nomAvecCheminFichier);
        });
        // fusion des PDF : page de garde DDP + TOUS les fichiers du dossier DDP
        // (dépôt dans DOCUWARE à la transmission : DdpApiController::transmettreBap)
        SuiviOperationService::suivre('Fusion', function () use ($dto, $nomAvecCheminFichier) {
            $this->fusionDesPdf($this->cheminsFichiersAFusionner($dto), [], $nomAvecCheminFichier);
        });

        return $nomFichier;
    }

    /**
     * Chemins complets des fichiers à fusionner derrière la page de garde, dans
     * l'ordre de $dto->lesFichiers (fichiers distants copiés, fichiers
     * téléversés, fichiers de commande DW), tous situés dans le dossier du DDP.
     * Un fichier absent (ex: copie distante échouée) est ignoré pour ne pas
     * faire échouer toute la fusion.
     */
    private function cheminsFichiersAFusionner(DdpDto $dto): array
    {
        $dossierDdp = rtrim($_ENV['BASE_PATH_FICHIER'], '/\\') . '/ddp/' . $dto->numeroDdp . '/';

        $chemins = [];
        foreach ($dto->lesFichiers as $nomFichier) {
            $chemin = $dossierDdp . $nomFichier;
            if (is_file($chemin)) {
                $chemins[] = $chemin;
            } else {
                error_log("DDP {$dto->numeroDdp} : fichier absent, non fusionné : {$chemin}");
            }
        }

        return array_values(array_unique($chemins));
    }

    private function fusionDesPdf(array $nomEtCheminFichiersEnregistrer, array $fichierChoisiAvecChemins, string $nomAvecCheminFichier): void
    {
        $traitementDeFichier = new TraitementDeFichier();
        $nomEtCheminFichiersEnregistrer = array_merge($nomEtCheminFichiersEnregistrer, $fichierChoisiAvecChemins);
        $fichierConvertir = $this->ConvertirLesPdf($nomEtCheminFichiersEnregistrer);
        $tousLesFichersAvecChemin = $traitementDeFichier->insertFileAtPosition($fichierConvertir, $nomAvecCheminFichier, 0);
        $traitementDeFichier->fusionFichers($tousLesFichersAvecChemin, $nomAvecCheminFichier);
    }

    /**
     * Page de garde DDP
     */
    private function pageDeGarde(DdpDto $dto, string $cheminEtNom): void
    {
        (new GeneratePdfDdp())->genererPdfDto($dto, $cheminEtNom);
    }

    /**
     * Pourcentage du montant à payer par rapport :
     * - à l'avance : au montant total du commande dans IPS ;
     * - après arrivage : au montant HT total des commandes de la demande.
     */
    private function ratioMontantPayer(DdpDto $dto): float
    {
        if ($dto->typeDdp->getId() === TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_A_L_AVANCE) {
            $montantTotalCommande = (float) $dto->montantTotalCommande();
            return $montantTotalCommande > 0 ? round($dto->montantAPayer() / $montantTotalCommande * 100, 2) : 0.0;
        }

        $montantTotalHt = 0.0;
        foreach (array_unique($dto->numeroCommande) as $numCde) {
            $montantTotalHt += $this->demandePaiementModel->getMontantCde((string) $numCde, $dto->codeSociete)['montant_total_cde_ht'];
        }

        return $montantTotalHt > 0 ? round($dto->montantAPayer() / $montantTotalHt * 100, 2) : 0.0;
    }

    private function enregistrementSurBd(DdpDto $dto): void
    {
        // enregistrement dans la table deamnde_paiement
        $ddp = $this->ddpService->createDdp($dto);
        // enregistrement dans la table demande_paiement_ligne
        $dto->ratioMontantPayer = $this->ratioMontantPayer($dto);
        $this->ddpLigneService->createLignesFromDto($dto);
        // enregistrement dans la table doc_demande_paiement
        $this->ddpDocService->createDocDdp($dto);
        // enregistrement dans la table demande_paiement_commande
        $demandePaiementCommandeService = new DemandePaiementCommandeService($this->getEntityManager());
        $demandePaiementCommandeService->createDdpCommande($dto, $ddp);
        // enregistrement dans la table commande_livraison (ce n'est pas utile pour le demande de paiement à l'avance)
        if ($dto->typeDdp->getId() !== TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_A_L_AVANCE) {
            $commandeLivraisonService = new CommandeLivraisonService($this->getEntityManager());
            $commandeLivraisonService->createCommandeLivraison($dto, $ddp);
        }
    }
}
