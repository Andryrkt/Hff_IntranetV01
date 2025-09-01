<?php


namespace App\Controller\magasin\ors;
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


ini_set('max_execution_time', 10000);
ini_set('memory_limit', '1000M');


use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\dit\DemandeIntervention;
use App\Service\TableauEnStringService;
use App\Controller\Traits\Transformation;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\MagasinListeOrLivrerModel;
use App\Form\magasin\MagasinListeOrALivrerSearchType;
use App\Controller\Traits\magasin\ors\MagasinOrALIvrerTrait;
use App\Controller\Traits\magasin\ors\MagasinTrait as OrsMagasinTrait;
use App\Controller\BaseController;

/**
 * @Route("/magasin/or")
 */
class MagasinListeOrLivrerController extends BaseController
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

    use Transformation;
    use OrsMagasinTrait;
    use MagasinOrALIvrerTrait;
    use AutorisationTrait;

    private $magasinListOrLivrerModel;

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
     * @Route("/liste-or-livrer", name="magasinListe_or_Livrer")
     *
     * @return void
     */
    public function listOrLivrer(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_MAG);
        /** FIN AUtorisation acées */

        $codeAgence = $this->getUser()->getAgenceAutoriserCode();
        $serviceAgence = $this->getUser()->getServiceAutoriserCode();

        /** CREATION D'AUTORISATION */
        $autoriser = $this->autorisationRole($this->getEntityManager());
        //FIN AUTORISATION

        if ($autoriser) {
            $agenceUser = "''";
        } else {
            $agenceUser = TableauEnStringService::TableauEnString(',', $codeAgence);
        }

        $form = $this->getFormFactory()->createBuilder(MagasinListeOrALivrerSearchType::class, ['agenceUser' => $agenceUser, 'autoriser' => $autoriser], [
            'method' => 'GET'
        ])->getForm();

        $form->handleRequest($request);
        $criteria = [
            "agenceUser" => $agenceUser,
            "orCompletNon" => "ORs COMPLET",
            "pieces" => "PIECES MAGASIN"
        ];
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }

        //enregistrer les critère de recherche dans la session
        $this->sessionManagerService->set('magasin_liste_or_livrer_search_criteria', $criteria);

        $data = $this->recupData($criteria);

        $this->logUserVisit('magasinListe_or_Livrer'); // historisation du page visité par l'utilisateur

        $this->getTwig()->render('magasin/ors/listOrLivrer.html.twig', [
            'data' => $data,
            'form' => $form->createView()
        ]);
    }



    /**
     * @Route("/magasin-list-or-livrer-export-excel", name="magasin_list_or_livrer")
     *
     * @return void
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        //recupères les critère dans la session 
        $criteria = $this->sessionManagerService->get('magasin_liste_or_livrer_search_criteria', []);

        $entities = $this->recupData($criteria);


        // Convertir les entités en tableau de données
        $data = [];
        $data[] = ['N° DIT', 'N° Or', "Date planning", "Date Or", "Agence Emetteur", "Service Emetteur", 'Agence débiteur', 'Service débiteur', 'N° Intv', 'N° lig', 'Cst', 'Réf.', 'Désignations', 'Qté demandée', 'Qté à livrer', 'Qté déjà livrée', 'Utilisateur', 'ID Materiel', 'Marque', 'Casier'];
        foreach ($entities as $entity) {

            $data[] = [
                $entity['referencedit'],
                $entity['numeroor'],
                $entity['datePlanning'],
                $entity['datecreation'],
                $entity['agencecrediteur'],
                $entity['servicecrediteur'],
                $entity['agencedebiteur'],
                $entity['servicedebiteur'],
                $entity['numinterv'],
                $entity['numeroligne'],
                $entity['constructeur'],
                $entity['referencepiece'],
                $entity['designationi'],
                $entity['quantitedemander'],
                $entity['qtealivrer'],
                $entity['quantitelivree'],
                $entity['nomPrenom'],
                $entity['idMateriel'],
                $entity['marque'],
                $entity['casier']
            ];
        }

        $this->excelServiceService->createSpreadsheet($data);
    }

    private function recupData($criteria)
    {
        $lesOrSelonCondition = $this->recupNumOrSelonCondition($criteria, $this->getEntityManager());

        $data = $this->magasinListOrLivrerModel->recupereListeMaterielValider($criteria, $lesOrSelonCondition);

        //ajouter le numero dit dans data
        for ($i = 0; $i < count($data); $i++) {
            $numeroOr = $data[$i]['numeroor'];
            $numItv = $data[$i]['numinterv'];
            $datePlannig1 = $this->magasinListOrLivrerModel->recupDatePlanning1($numeroOr);
            $datePlannig2 = $this->magasinListOrLivrerModel->recupDatePlanningOR2($numeroOr, $numItv);
            $data[$i]['nomPrenom'] = $this->magasinListOrLivrerModel->recupUserCreateNumOr($numeroOr)[0]['nomprenom'];

            if (!empty($datePlannig1)) {
                $data[$i]['datePlanning'] = $datePlannig1[0]['dateplanning1'];
            } else if (!empty($datePlannig2)) {
                $data[$i]['datePlanning'] = $datePlannig2[0]['dateplanning2'];
            } else {
                $data[$i]['datePlanning'] = '';
            }

            //$dit = $this->getEntityManager()->getRepository(DemandeIntervention::class)->findNumDit($numeroOr);
            $ditRepository = $this->getEntityManager()->getRepository(DemandeIntervention::class)->findOneBy(['numeroOR' => $numeroOr]);

            if (!empty($ditRepository)) {
                $data[$i]['numDit'] = $ditRepository->getNumeroDemandeIntervention();
                $data[$i]['niveauUrgence'] = $ditRepository->getIdNiveauUrgence()->getDescription();
                $idMateriel = $ditRepository->getIdMateriel();
                $marqueCasier = $this->ditModelService->recupMarqueCasierMateriel($idMateriel);
                $data[$i]['idMateriel'] = $idMateriel;
                $data[$i]['marque'] =  array_key_exists(0, $marqueCasier) ? $marqueCasier[0]['marque'] : '';
                $data[$i]['casier'] = array_key_exists(0, $marqueCasier) ? $marqueCasier[0]['casier'] : '';
            } else {
                break;
            }
        }

        return $data;
    }
}
