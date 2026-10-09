<?php

namespace App\Controller\ddp;

use App\Constants\ddp\TypeDemandePaiementConstants;
use App\Controller\Controller;
use App\Controller\Traits\PdfConversionTrait;
use App\Dto\ddp\DdpDto;
use App\Factory\ddp\DdpFactory;
use App\Form\ddp\DdpType;
use App\Model\ddp\DemandePaiementModel;
use App\Service\ddp\CommandeLivraisonService;
use App\Service\ddp\DemandePaiementCommandeService;
use App\Service\ddp\Magasin\DdpDocService;
use App\Service\ddp\Magasin\DdpLigneService;
use App\Service\ddp\Magasin\DdpService;
use App\Service\fichier\TraitementDeFichier;
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

            $erreurMontant = $this->erreurMontant($dto);
            if ($erreurMontant !== null) {
                $form->get('montantAPayer')->addError(new FormError($erreurMontant));
                return;
            }

            $dto = $this->ddpFactory->apresSoumission($form, $dto);

            // Enregistrement dans BD
            $this->enregistrementSurBd($dto);
            // Enregistrement des fichiers, generation du PDF, fusion des fichiers et envoi dans DOCUWARE
            $this->traitementDeFichier($dto);

            /** HISTORISATION */
            $this->historiqueOperation->sendNotificationSoumission(
                'Le document a été généré avec succès',
                $dto->numeroDdp,
                'da_bon_a_payer',
                true,
                null,
                'devis_magasin_search',
                ['appro' => 0]
            );
        }
    }

    /**
     * Vérifie le montant saisi par l'utilisateur, miroir de la vérification
     * déjà faite côté front (DemandePaiementManager.js::verifierMontant) :
     * - à l'avance : supérieur à 0 et au plus le montant HT de la commande sélectionnée ;
     * - après arrivage : égal au montant calculé des factures sélectionnées.
     *
     * @return string|null message d'erreur, null si le montant est correct
     */
    private function erreurMontant(DdpDto $dto): ?string
    {
        if ($dto->typeDdp->getId() === TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_A_L_AVANCE) {
            return $this->erreurMontantAvance($dto);
        }

        $montantAttendu = $this->montantAttendu($dto);

        if ($montantAttendu === null || abs($dto->montantAPayer() - $montantAttendu) < 0.01) {
            return null;
        }

        return 'Le montant saisi ne correspond pas au montant calculé pour la/les commande(s)/facture(s) sélectionnée(s).';
    }

    private function erreurMontantAvance(DdpDto $dto): ?string
    {
        $montantSaisi = $dto->montantAPayer();

        if ($montantSaisi <= 0) {
            return 'Le montant à payer doit être supérieur à 0.';
        }

        if (empty($dto->numeroCommande)) {
            return null;
        }

        $montantCommandeHt = $this->demandePaiementModel->getMontantCde((string) $dto->numeroCommande[0], $dto->codeSociete)['montant_total_cde_ht'];

        if ($montantSaisi - $montantCommandeHt > 0.01) {
            return sprintf(
                'Le montant à payer ne peut pas dépasser le montant HT de la commande (%s).',
                number_format($montantCommandeHt, 2, ',', ' ')
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

    private function pageDeGarde(DdpDto $dto, string $cheminEtNom): GeneratePdfDdp
    {
        $generatePdfDdp = new GeneratePdfDdp();
        $generatePdfDdp->genererPdfDto($dto, $cheminEtNom);

        return $generatePdfDdp;
    }

    private function enregistrementSurBd(DdpDto $dto): void
    {
        // enregistrement dans la table deamnde_paiement
        $ddp = $this->ddpService->createDdp($dto);
        // enregistrement dans la table demande_paiement_ligne
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
