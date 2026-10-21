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
use App\Service\genererPdf\bap\GenererPdfBapMagasin;
use App\Service\genererPdf\GeneratePdfDdp;
use App\Service\historiqueOperation\HistoriqueOperationDDPService;
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

            $erreurCommandeSoldee = $this->erreurCommandeSoldee($dto);
            if ($erreurCommandeSoldee !== null) {
                $form->addError(new FormError($erreurCommandeSoldee));
                return;
            }

            // après arrivage : un montant total doit être saisi pour chaque commande
            if ($dto->typeDdp->getId() === TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE) {
                foreach ($dto->numeroCommande as $numCde) {
                    if ($dto->montantCommande($numCde) === null) {
                        $form->addError(new FormError("Le montant du commande à payer $numCde est obligatoire."));
                        return;
                    }
                }
            }

            $erreurMontant = $this->erreurMontant($dto);
            if ($erreurMontant !== null) {
                $form->get('montantAPayer')->addError(new FormError($erreurMontant));
                return;
            }

            if ($dto->typeDdp->getId() === TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE) {
                $this->soumissionApresArrivage($form, $dto);
                return;
            }

            $dto = $this->ddpFactory->apresSoumission($form, $dto);

            // Enregistrement dans BD
            $this->enregistrementSurBd($dto);
            // Enregistrement des fichiers, generation du PDF, fusion des fichiers et envoi dans DOCUWARE
            $this->traitementDeFichier($dto);

            /** HISTORISATION */
            $this->notificationSoumission($dto->numeroDdp);
        }
    }

    /**
     * Après arrivage : les commandes sont regroupées selon leur situation
     * (DemandePaiementRepository::getSituationAvanceParCommande) et une demande
     * de paiement est générée par groupe :
     * - BAP (aucune DDPA validée) : PDF BAP MAGASIN ;
     * - DDPR (DDPA à 100 %) / DDPL (DDPA entre 0 et 100 %) : PDF DDP.
     */
    private function soumissionApresArrivage(FormInterface $form, DdpDto $dto): void
    {
        $groupes = $this->groupesParSituation($dto);
        if (is_string($groupes)) {
            $form->addError(new FormError($groupes));
            return;
        }

        $numerosDdp = [];
        $source = null;
        foreach ($groupes as $typeId => $groupe) {
            $dtoGroupe = clone $dto;
            $dtoGroupe->numeroCommande = $groupe['commandes'];
            $dtoGroupe->numeroFacture = $groupe['factures'];
            $dtoGroupe->montantsCommande = array_intersect_key($dto->montantsCommande, array_flip($groupe['commandes']));
            $dtoGroupe->soldesAvance = $groupe['soldes'];
            // montant à payer de la DDP = montant des factures du groupe
            $dtoGroupe->montantAPayer = number_format($groupe['montantFactures'], 2, ',', '');

            // typeDdp encore "après arrivage" ici : apresSoumission() en dépend (commandes, fichiers distants)
            $dtoGroupe = $this->ddpFactory->apresSoumission($form, $dtoGroupe, $source);
            $dtoGroupe->typeDdp = $this->getEntityManager()->getRepository(TypeDemande::class)->find($typeId);
            $dtoGroupe->statut = [
                TypeDemandePaiementConstants::ID_BON_A_PAYER              => StatutConstants::BAP_A_TRANSMETTRE,
                TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_REGULE  => StatutConstants::DDPR_A_TRANSMETTRE,
            ][$typeId] ?? StatutConstants::DDPL_A_TRANSMETTRE;
            if ($typeId === TypeDemandePaiementConstants::ID_BON_A_PAYER) {
                $dtoGroupe->donneesBapMagasin = $this->donneesBapMagasin($dtoGroupe, $groupe['lignesFactures']);
            }

            $this->enregistrementSurBd($dtoGroupe);
            $this->traitementDeFichier($dtoGroupe);

            $source = $source ?? $dtoGroupe;
            $numerosDdp[] = $dtoGroupe->numeroDdp;
        }

        $this->notificationSoumission(implode(', ', $numerosDdp));
    }

    /**
     * Regroupe les commandes de la demande par type de DDP à générer.
     *
     * @return array<int, array{commandes: string[], factures: string[], soldes: array<string, float>, montantFactures: float, lignesFactures: array}>|string
     *         groupes indexés par id de type de demande, ou message d'erreur
     */
    private function groupesParSituation(DdpDto $dto)
    {
        $lignesFactures = $this->demandePaiementModel->getCommandesParFacture(
            TableauEnStringService::TableauEnString(',', $dto->numeroFacture)
        );
        $repository = $this->getEntityManager()->getRepository(DemandePaiement::class);

        $groupes = [];
        $typeParCommande = [];
        foreach (array_unique($dto->numeroCommande) as $numCde) {
            $numCde = (string) $numCde;
            $situation = $repository->getSituationAvanceParCommande($numCde, $dto->codeSociete);
            $typeId = $situation['typeDdpAGenerer'];
            $typeParCommande[$numCde] = $typeId;

            $groupes[$typeId]['commandes'][] = $numCde;
            $groupes[$typeId]['soldes'][$numCde] = $situation['soldeAvance'];
        }

        foreach ($groupes as $typeId => &$groupe) {
            $groupe['factures'] = [];
            $groupe['lignesFactures'] = [];
            $groupe['montantFactures'] = 0.0;
        }
        unset($groupe);

        // chaque facture rejoint le groupe de ses commandes
        $groupeParFacture = [];
        foreach ($lignesFactures as $ligne) {
            $numFac = trim((string) $ligne['Numero_Facture']);
            $numCde = trim((string) $ligne['Numero_PO']);
            if (!isset($typeParCommande[$numCde])) {
                continue;
            }
            $typeId = $typeParCommande[$numCde];
            if (isset($groupeParFacture[$numFac]) && $groupeParFacture[$numFac] !== $typeId) {
                return "La facture $numFac concerne des commandes de situations différentes (BAP / DDPR / DDPL) : veuillez la traiter séparément.";
            }
            $groupes[$typeId]['lignesFactures'][] = $ligne;
            if (!isset($groupeParFacture[$numFac])) {
                $groupeParFacture[$numFac] = $typeId;
                $groupes[$typeId]['factures'][] = $numFac;
                $groupes[$typeId]['montantFactures'] += (float) $ligne['Montant_Facture'];
            }
        }

        return $groupes;
    }

    /**
     * Données de la page de garde BAP MAGASIN (voir GenererPdfBapMagasin)
     */
    private function donneesBapMagasin(DdpDto $dto, array $lignesFactures): array
    {
        $daModel = new DaModel();
        $repository = $this->getEntityManager()->getRepository(DemandePaiement::class);
        $cdeFrnRepository = $this->getEntityManager()->getRepository(CdefnrSoumisAValidation::class);

        $commandes = [];
        foreach ($dto->numeroCommande as $numCde) {
            /** @var CdefnrSoumisAValidation|null $validation */
            $validation = $cdeFrnRepository->findOneBy(['numCdeFournisseur' => $numCde], ['numVersion' => 'DESC']);

            $ddps = [];
            /** @var DemandePaiement $ddp */
            foreach ($repository->getDdpSelonNumCde($numCde) as $ddp) {
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
                'infoBc'     => $daModel->getInfoBC((string) $numCde, $dto->codeSociete),
                'validation' => $validation ? [
                    'statut'         => $validation->getStatut(),
                    'dateSoumission' => $validation->getDateHeureSoumission(),
                    'version'        => $validation->getNumVersion(),
                ] : null,
                'livraisons' => $daModel->getHistoriqueLivraison((string) $numCde) ?? [],
                'ddps'       => $ddps,
            ];
        }

        return [
            'commandes' => $commandes,
            'factures'  => $lignesFactures,
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
     * - à l'avance : pas de contrôle (montant libre) ;
     * - après arrivage : égal au montant calculé des factures sélectionnées.
     *
     * @return string|null message d'erreur, null si le montant est correct
     */
    private function erreurMontant(DdpDto $dto): ?string
    {
        $montantAttendu = $this->montantAttendu($dto);

        if ($montantAttendu === null || abs($dto->montantAPayer() - $montantAttendu) < 0.01) {
            return null;
        }

        return 'Le montant saisi ne correspond pas au montant calculé pour la/les commande(s)/facture(s) sélectionnée(s).';
    }

    /**
     * Paiement à l'avance : bloque la soumission si la commande sélectionnée est
     * déjà payée à 100 % par des demandes de paiement à l'avance validées.
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

        return null;
    }

    private function montantAttendu(DdpDto $dto): ?float
    {
        if ($dto->typeDdp->getId() === TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE) {
            if (empty($dto->numeroFacture)) {
                return null;
            }

            $numFacString = TableauEnStringService::TableauEnString(',', $dto->numeroFacture);
            $montants = $this->demandePaiementModel->getMontantFacGcot($dto->numeroFournisseur, '', $numFacString);

            return isset($montants[0]) ? (float) $montants[0] : 0.0;
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
        $generatePdfDdp = $this->pageDeGarde($dto, $nomAvecCheminFichier);
        // fusion des PDF : page de garde DDP + TOUS les fichiers du dossier DDP
        $this->fusionDesPdf($this->cheminsFichiersAFusionner($dto), [], $nomAvecCheminFichier);
        // envoi du PDF final dans DOCUWARE
        $generatePdfDdp->copyToDwDdp($nomFichier, $dto->numeroDdp);

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
     * Page de garde : BAP MAGASIN pour un bon à payer, DDP sinon
     *
     * @return GeneratePdfDdp|GenererPdfBapMagasin
     */
    private function pageDeGarde(DdpDto $dto, string $cheminEtNom)
    {
        $generatePdfDdp = $dto->typeDdp->getId() === TypeDemandePaiementConstants::ID_BON_A_PAYER
            ? new GenererPdfBapMagasin()
            : new GeneratePdfDdp();
        $generatePdfDdp->genererPdfDto($dto, $cheminEtNom);

        return $generatePdfDdp;
    }

    /**
     * Pourcentage du montant à payer par rapport :
     * - à l'avance : au montant total du commande saisi ;
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
