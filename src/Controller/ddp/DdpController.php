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

            $dto = $this->ddpFactory->apresSoumission($form, $dto);

            // Enregistrement dans BD
            $this->enregistrementSurBd($dto);
            // Enregistrement des fichiers, generation du PDF et fusion des fichiers
            $this->traitementDeFichier($dto);
            // TODO: envoie dans DOCUWARE
            // TODO: MOdification des données dans des base de données

            /** HISTORISATION */
            $this->historiqueOperation->sendNotificationSoumission('Le document a été généré avec succès', $dto->numeroDdp, 'ddp_liste', true);
        }
    }

    private function traitementDeFichier(DdpDto $dto): string
    {
        $numCdes = [];
        $numeroCommandes = '';

        if (!empty($dto->numeroFournisseur) && $dto->numeroFournisseur !== '-') {
            $numCdes = $this->demandePaiementModel->getCommandeReceptionnee($dto->numeroFournisseur);
            $numCdesString = TableauEnStringService::TableauEnString(',', $numCdes);
            $numFacString =  TableauEnStringService::TableauEnString(',', $dto->numeroFacture);
            $numeroCommandes = $this->demandePaiementModel->getNumCommande($dto->numeroFournisseur, $numCdesString, $numFacString);
        }

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
        $nomEtCheminFichiersEnregistrer = $dto->nomEtCheminFichiersEnregistrer;
        $nomAvecCheminFichier = $dto->nomAvecCheminFichier;
        $nomFichier = $dto->nomFichier;
        if ($dto->typeDdp->getId() === TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE) {
            $numeroCommandes = [];
            $dto->numeroCommande = $numeroCommandes;
        }
        $dto->lesFichiers = $dto->getToutesLesNomFichiers();
        // generation de la page de garde DDP
        $this->pageDeGarde($dto, $nomAvecCheminFichier);
        // fusion des PDF (page de garde DDP+ autres documents)
        $this->fusionDesPdf($nomEtCheminFichiersEnregistrer, [], $nomAvecCheminFichier);

        return $nomFichier;
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
