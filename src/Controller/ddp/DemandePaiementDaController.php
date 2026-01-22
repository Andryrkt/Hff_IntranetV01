<?php

namespace App\Controller\ddp;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Dto\ddp\DemandePaiementDto;
use App\Form\ddp\DemandePaiementDaType;
use App\Service\TableauEnStringService;
use Symfony\Component\Form\FormInterface;
use App\Factory\ddp\DemandePaiementFactory;
use App\Service\fichier\UploderFileService;
use App\Controller\Traits\AutorisationTrait;
use App\Service\ddp\DdpGeneratorNameService;
use App\Controller\Traits\PdfConversionTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Constants\ddp\TypeDemandePaiementConstants;
use App\Model\ddp\DemandePaiementModel;
use App\Service\ddp\DemandePaiementLigneService;
use App\Service\ddp\DemandePaiementService;
use App\Service\ddp\DocDemandePaiementService;

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

    public function __construct()
    {
        parent::__construct();
        $this->demandePaiementModel = new DemandePaiementModel();
        $this->demandePaiementLigneService = new DemandePaiementLigneService($this->getEntityManager());
        $this->demandePaiementService = new DemandePaiementService($this->getEntityManager());
        $this->docDemandePaiementService = new DocDemandePaiementService($this->getEntityManager());
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
            dd($dto);
        }
    }

    private function enregistrementSurBd(DemandePaiementDto $dto)
    {
        // enregistrement dans la table deamnde_paiement
        $this->demandePaiementService->createDdp($dto);
        // enregistrement dans la table demande_paiement_ligne
        $lignesCreees = $this->demandePaiementLigneService->createLignesFromDto($dto);
        // enregistrement dans la table doc_demande_paiement
        $this->docDemandePaiementService->createDocDdp($dto);
        // enregistrement dans la table historique_statut_ddp
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
        [$nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier, $nomFichier] = $this->enregistrementFichier($form, $dto);
        if ($dto->typeDemande->getId() === TypeDemandePaiementConstants::ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE) {
            $dto->numeroCommande = $numeroCommandes;
        }
        $nomDufichierCde = $this->recupCdeDw($dto, $dto->numeroDdp, $dto->numeroVersion); //recupération de fichier cde dans DW
    }

    private function enregistrementFichier(FormInterface $form, DemandePaiementDto $dto): array
    {
        $nameGenerator = new DdpGeneratorNameService();
        $cheminBaseUpload = $_ENV['BASE_PATH_FICHIER'] . '/ddp/';
        $uploader = new UploderFileService($cheminBaseUpload, $nameGenerator);
        $path = $this->cheminBaseUpload . $dto->numeroDdp . '_New_1';
        if (!is_dir($path)) mkdir($path, 0777, true);

        $nomEtCheminFichiersEnregistrer = $uploader->getNomsEtCheminFichiers($form, [
            'repertoire' => $path,
            'conserver_nom_original' => true,
        ]);

        $nomFichier = $nameGenerator->generateNamePrincipal($dto->numeroDdp);
        $nomAvecCheminFichier = $path . '/' . $nomFichier;

        return [$nomEtCheminFichiersEnregistrer, $nomAvecCheminFichier, $nomFichier];
    }

    private function recupCdeDw(DemandePaiementDto $dto, $numDdp, $numVersion): array
    {
        $pathAndCdes = [];
        foreach ($dto->numeroCommande as  $numcde) {
            $pathAndCdes[] = $this->demandePaiementModel->getPathDwCommande($numcde);
        }

        $nomDufichierCde = [];
        foreach ($pathAndCdes as  $pathAndCde) {
            if ($pathAndCde[0]['path'] != null) {
                $cheminDufichierInitial = $_ENV['BASE_PATH_FICHIER'] . "/" . $pathAndCde[0]['path'];

                if (!file_exists($cheminDufichierInitial)) {
                    // Le fichier n'existe pas, on passe au suivant
                    continue;
                }

                $nomFichierInitial = basename($pathAndCde[0]['path']);

                $cheminDufichierDestinataire = $this->cheminDeBase . '/' . $numDdp . '_New_' . $numVersion . '/' . $nomFichierInitial;

                $destinationDir = dirname($cheminDufichierDestinataire);
                if (!is_dir($destinationDir)) {
                    mkdir($destinationDir, 0777, true);
                }

                if (copy($cheminDufichierInitial, $cheminDufichierDestinataire)) {
                    $nomDufichierCde[] =  $nomFichierInitial;
                }
            }
        }

        return $nomDufichierCde;
    }
}
