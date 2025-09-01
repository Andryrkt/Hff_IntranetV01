<?php

namespace App\Controller\da\Creation;
use App\Service\FusionPdf;
use App\Model\ProfilModel;
use App\Model\badm\BadmModel;
use App\Model\admin\personnel\PersonnelModel;
use App\Model\dom\DomModel;
use App\Model\da\DaModel;
use App\Model\dom\DomDetailModel;
use App\Model\dom\DomDuplicationModel;
use App\Model\dom\DomListModel;
use App\Model\dit\DitModel;
use App\Service\SessionManagerService;
use App\Service\ExcelService;


use App\Controller\Controller;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Form\da\DemandeApproDirectFormType;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use App\Service\application\ApplicationService;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\da\creation\DaNewDirectTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Controller\BaseController;

/**
 * @Route("/demande-appro")
 */
class DaNewDirectController extends BaseController
{
    private FusionPdf $fusionPdfService;
    private ProfilModel $profilModelService;
    private BadmModel $badmModelService;
    private PersonnelModel $personnelModelService;
    private DomModel $domModelService;
    private DaModel $daModelService;
    private DomDetailModel $domDetailModelService;
    private DomDuplicationModel $domDuplicationModelService;
    private DomListModel $domListModelService;
    private DitModel $ditModelService;
    private SessionManagerService $sessionManagerService;
    private ExcelService $excelServiceService;

    use DaNewDirectTrait;
    use AutorisationTrait;

    public function __construct(
        FusionPdf $fusionPdfService,
        ProfilModel $profilModelService,
        BadmModel $badmModelService,
        PersonnelModel $personnelModelService,
        DomModel $domModelService,
        DaModel $daModelService,
        DomDetailModel $domDetailModelService,
        DomDuplicationModel $domDuplicationModelService,
        DomListModel $domListModelService,
        DitModel $ditModelService,
        SessionManagerService $sessionManagerService,
        ExcelService $excelServiceService
    ) {
        parent::__construct();
        $this->fusionPdfService = $fusionPdfService;
        $this->profilModelService = $profilModelService;
        $this->badmModelService = $badmModelService;
        $this->personnelModelService = $personnelModelService;
        $this->domModelService = $domModelService;
        $this->daModelService = $daModelService;
        $this->domDetailModelService = $domDetailModelService;
        $this->domDuplicationModelService = $domDuplicationModelService;
        $this->domListModelService = $domListModelService;
        $this->ditModelService = $ditModelService;
        $this->sessionManagerService = $sessionManagerService;
        $this->excelServiceService = $excelServiceService;
    }

    /**
     * @Route("/new-da-direct", name="da_new_direct")
     */
    public function newDADirect(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $this->checkPageAccess($this->estAdmin()); // todo: à changer plus tard

        $demandeAppro = $this->initialisationDemandeApproDirect();

        $form = $this->getFormFactory()->createBuilder(DemandeApproDirectFormType::class, $demandeAppro)->getForm();
        $this->traitementFormDirect($form, $request, $demandeAppro);

        return $this->render('da/new-da-direct.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function traitementFormDirect($form, Request $request, DemandeAppro $demandeAppro): void
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** 
             * @var DemandeAppro $demandeAppro
             */
            $demandeAppro = $form->getData();

            $numDa = $demandeAppro->getNumeroDemandeAppro();

            $formDAL = $form->get('DAL');
            /** 
             * Ajout de ligne de demande appro dans la table Demande_Appro_L 
             * @var DemandeApproL $DAL la demande appro l à enregistrer dans la BDD
             **/
            foreach ($demandeAppro->getDAL() as $ligne => $DAL) {
                if (null === $DAL->getNumeroFournisseur()) {
                    $this->sessionManagerService->set('notification', ['type' => 'danger', 'message' => 'Erreur : Le nom du fournisseur doit correspondre à l’un des choix proposés.']);
                    $this->redirectToRoute("list_da");
                }

                $DAL->setNumeroDemandeAppro($numDa)
                    ->setNumeroLigne($ligne + 1)
                    ->setStatutDal(DemandeAppro::STATUT_A_VALIDE_DW)
                    ->setJoursDispo($this->getJoursRestants($DAL));
                $this->traitementFichiers($DAL, $formDAL[$ligne + 1]->get('fileNames')->getData()); // traitement des fichiers uploadés pour chaque ligne DAL
                $this->getEntityManager()->persist($DAL);
            }

            /** Ajout de demande appro dans la base de donnée (table: Demande_Appro) */
            $this->getEntityManager()->persist($demandeAppro);

            /** Modifie la colonne dernière_id dans la table applications */
            $applicationService = new ApplicationService($this->getEntityManager());
            $applicationService->mettreAJourDerniereIdApplication('DAP', $numDa);

            /** ajout de l'observation dans la table da_observation si ceci n'est pas null */
            if ($demandeAppro->getObservation() !== null) {
                $this->insertionObservation($demandeAppro->getObservation(), $demandeAppro);
            }

            $this->getEntityManager()->flush();

            // ajout des données dans la table DaAfficher
            $this->ajouterDaDansTableAffichage($demandeAppro);

            // ajout des données dans la table DaSoumisAValidation
            $this->ajouterDansDaSoumisAValidation($demandeAppro);

            /** création de pdf et envoi dans docuware */
            $this->creationPdfSansDitAvaliderDW($demandeAppro);

            $this->sessionManagerService->set('notification', ['type' => 'success', 'message' => 'Votre demande a été enregistrée']);
            $this->redirectToRoute("list_da");
        }
    }

    /** 
     * TRAITEMENT DES FICHIER UPLOAD pour chaque ligne de la demande appro (DAL)
     */
    private function traitementFichiers(DemandeApproL $dal, $files): void
    {
        $fileNames = [];
        if ($files !== null) {
            $i = 1; // Compteur pour le nom du fichier
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $fileName = $this->daFileUploader->uploadPJForDal($file, $dal, $i); // Appel de la méthode pour uploader le fichier
                } else {
                    throw new \InvalidArgumentException('Le fichier doit être une instance de UploadedFile.');
                }
                $i++; // Incrémenter le compteur pour le prochain fichier
                $fileNames[] = $fileName; // Ajouter le nom du fichier dans le tableau
            }
        }
        $dal->setFileNames($fileNames); // Enregistrer les noms de fichiers dans l'entité
    }
}
