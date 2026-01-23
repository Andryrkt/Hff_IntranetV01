<?php

namespace App\Controller\ddp;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Dto\ddp\DemandePaiementDto;
use App\Form\ddp\DemandePaiementDaType;
use App\Model\ddp\DemandePaiementModel;
use App\Service\TableauEnStringService;
use Symfony\Component\Form\FormInterface;
use App\Factory\ddp\DemandePaiementFactory;
use App\Service\ddp\DemandePaiementService;
use App\Service\fichier\UploderFileService;
use App\Controller\Traits\AutorisationTrait;
use App\Service\ddp\DdpGeneratorNameService;
use App\Service\fichier\TraitementDeFichier;
use App\Controller\Traits\PdfConversionTrait;
use Symfony\Component\HttpFoundation\Request;
use App\Service\ddp\DocDemandePaiementService;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ddp\DemandePaiementLigneService;
use App\Service\genererPdf\ddp\GeneratePdfDdpDa;
use App\Constants\ddp\TypeDemandePaiementConstants;
use App\Service\historiqueOperation\HistoriqueOperationDDPService;

/**
 * @Route("/compta/demande-de-paiement")
 */
class DemandePaiementDaController extends Controller
{
    use AutorisationTrait;
    use PdfConversionTrait;

    private DemandePaiementModel $demandePaiementModel;
    private DemandePaiementLigneService $demandePaiementLigneService;
    private DemandePaiementService $demandePaiementService;
    private DocDemandePaiementService $docDemandePaiementService;
    private HistoriqueOperationDDPService $historiqueOperation;

    public function __construct()
    {
        parent::__construct();
        $this->demandePaiementModel = new DemandePaiementModel();
        $this->demandePaiementLigneService = new DemandePaiementLigneService($this->getEntityManager());
        $this->demandePaiementService = new DemandePaiementService($this->getEntityManager());
        $this->docDemandePaiementService = new DocDemandePaiementService($this->getEntityManager());
        $this->historiqueOperation = new HistoriqueOperationDDPService($this->getEntityManager());
    }

    /**
     * @Route("/newDa/{typeDdp}/{numCdeDa}/{typeDa}", name="demande_paiement_da", defaults={"numCdeDa"=null, "typeDa"=null})
     */
    public function index(int $typeDdp, int $numCdeDa, int $typeDa, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_DDP);
        /** FIN AUtorisation acées */

        // creation du formulaire
        $dto = (new DemandePaiementFactory($this->getEntityManager()))->load($typeDdp, $numCdeDa, $typeDa, $this->getUser());
        $form = $this->getFormFactory()->createBuilder(DemandePaiementDaType::class, $dto, [
            'method' => 'POST',
            'em' => $this->getEntityManager()
        ])->getForm();

        //traitement du formulaire
        $this->traitementDuFormulaire($request, $form);

        return $this->render('ddp/demande_paiement_da_new.html.twig', [
            'dto' => $dto,
            'form' => $form->createView()
        ]);
    }


    private function traitementDuFormulaire(Request $request, FormInterface $form)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dto = $form->getdata();

            $this->traitementDeFichier($dto, $form);
            $this->enregistrementSurBd($dto);

            /** HISTORISATION */
            $this->historiqueOperation->sendNotificationSoumission('Le document a été généré avec succès', $dto->numeroDdp, 'ddp_liste', true);
        }
    }

    private function pageDeGarde(DemandePaiementDto $dto, string $cheminEtNom): GeneratePdfDdpDa
    {
        $generatePdfDdp = new GeneratePdfDdpDa();
        $generatePdfDdp->generer($dto, $cheminEtNom);

        return $generatePdfDdp;
    }

    private function copierFichierDistant(DemandePaiementDto $dto): void
    {
        if ($dto->typeDemande->getId() === TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE) {
            $this->docDemandePaiementService->copierFichierDistant($dto);
        }
    }

    private function enregistrementSurBd(DemandePaiementDto $dto): void
    {
        // enregistrement dans la table deamnde_paiement
        $this->demandePaiementService->createDdp($dto);
        // enregistrement dans la table demande_paiement_ligne
        $this->demandePaiementLigneService->createLignesFromDto($dto);
        // enregistrement dans la table doc_demande_paiement
        $this->docDemandePaiementService->createDocDdp($dto);
        // enregistrement dans la table historique_statut_ddp
        $this->demandePaiementService->createHistoriqueStatut($dto);
    }

    private function traitementDeFichier(DemandePaiementDto $dto, FormInterface $form)
    {
        $numCdes = $this->demandePaiementModel->getCommandeReceptionnee($dto->numeroFournisseur);
        $numCdesString = TableauEnStringService::TableauEnString(',', $numCdes);
        $numFacString = TableauEnStringService::TableauEnString(',', $dto->numeroFacture);
        $numeroCommandes = $this->demandePaiementModel->getNumCommande($dto->numeroFournisseur, $numCdesString, $numFacString);

        /** TRAITEMENT FICHIER  AUTRE DOCUMENT ET BC client externe / BC client magasin*/
        if ($dto->pieceJoint04 !== null) {
            $dto->estAutresDoc = true;
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
        $this->copierFichierDistant($dto);
        [$nomEtCheminFichiersEnregistrer, $nomFichiersTelecharger,  $nomAvecCheminFichier, $nomFichier] = $this->enregistrementFichier($form, $dto);
        if ($dto->typeDemande->getId() === TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE) {
            $dto->numeroCommande = $numeroCommandes;
        }
        $dto->lesFichiers = $this->docDemandePaiementService->fusionDesFichiersDansUnTableau($dto, $nomFichiersTelecharger);
        $generatePdf = $this->pageDeGarde($dto, $nomAvecCheminFichier);
        $this->fusionDesPdf($nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier);
        $generatePdf->copyToDw($nomAvecCheminFichier, $nomFichier);
    }

    private function enregistrementFichier(FormInterface $form, DemandePaiementDto $dto): array
    {
        $nameGenerator = new DdpGeneratorNameService();
        $cheminBaseUpload = $_ENV['BASE_PATH_FICHIER'] . '/ddp/';
        $uploader = new UploderFileService($cheminBaseUpload, $nameGenerator);
        $path = $cheminBaseUpload . $dto->numeroDdp . '_New_1/';
        if (!is_dir($path)) mkdir($path, 0777, true);

        [$nomEtCheminFichiersEnregistrer, $nomFichierTelecharger] = $uploader->getFichiers($form, [
            'repertoire' => $path,
            'conserver_nom_original' => true,
        ]);

        $nomFichier = $nameGenerator->generateNamePrincipal($dto->numeroDdp);
        $nomAvecCheminFichier = $path . '/' . $nomFichier;

        return [$nomEtCheminFichiersEnregistrer, $nomFichierTelecharger,  $nomAvecCheminFichier, $nomFichier];
    }

    private function fusionDesPdf(array $nomEtCheminFichiersEnregistrer, string $nomAvecCheminFichier): void
    {
        $traitementDeFichier = new TraitementDeFichier();

        $fichierConvertir = $this->ConvertirLesPdf($nomEtCheminFichiersEnregistrer);
        $tousLesFichersAvecChemin = $traitementDeFichier->insertFileAtPosition($fichierConvertir, $nomAvecCheminFichier, 0);
        $traitementDeFichier->fusionFichers($tousLesFichersAvecChemin, $nomAvecCheminFichier);
    }
}
