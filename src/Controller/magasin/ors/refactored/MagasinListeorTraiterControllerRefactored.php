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


// ini_set('max_execution_time', 10000);

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\dit\DemandeIntervention;
use App\Service\TableauEnStringService;
use App\Controller\Traits\Transformation;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\MagasinListeOrATraiterModel;
use App\Form\magasin\MagasinListeOrATraiterSearchType;
use App\Controller\Traits\magasin\ors\MagasinOrATraiterTrait;
use App\Controller\Traits\magasin\ors\MagasinTrait as OrsMagasinTrait;
use App\Controller\BaseController;

/**
 * @Route("/magasin/or")
 */
class MagasinListeOrTraiterController extends BaseController
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

    use Transformation;
    use OrsMagasinTrait;
    use MagasinOrATraiterTrait;
    use AutorisationTrait;

    /**
     * @Route("/liste-magasin", name="magasinListe_index")
     *
     * @return void
     */
    public function index(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_MAG);
        /** FIN AUtorisation acées */

        $magasinModel = new MagasinListeOrATraiterModel;
        $codeAgence = $this->getUser()->getAgenceAutoriserCode();

        /** CREATION D'AUTORISATION */
        $autoriser = $this->autorisationRole($this->getEntityManager());
        //FIN AUTORISATION

        if ($autoriser) {
            $agenceUser = "''";
        } else {
            $agenceUser = TableauEnStringService::TableauEnString(',', $codeAgence);
        }

        $form = $this->getFormFactory()->createBuilder(MagasinListeOrATraiterSearchType::class, ['agenceUser' => $agenceUser, 'autoriser' => $autoriser], [
            'method' => 'GET'
        ])->getForm();

        $form->handleRequest($request);
        $criteria = $this->innitialisationCriteria($agenceUser);
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }

        //enregistrer les critère de recherche dans la session
        $this->sessionManagerService->set('magasin_liste_or_traiter_search_criteria', $criteria);

        $data = $this->recupData($criteria, $magasinModel);

        $this->logUserVisit('magasinListe_index'); // historisation du page visité par l'utilisateur

        $this->getTwig()->render('magasin/ors/listOrATraiter.html.twig', [
            'data' => $data,
            'form' => $form->createView()
        ]);
    }




    /**
     * @Route("/magasin-list-or-traiter-export-excel", name="magasin_list_or_traiter")
     *
     * @return void
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $magasinModel = new MagasinListeOrATraiterModel;
        //recupères les critère dans la session 
        $criteria = $this->sessionManagerService->get('magasin_liste_or_traiter_search_criteria', []);

        $entities = $this->recupData($criteria, $magasinModel);

        // Convertir les entités en tableau de données
        $data = [];
        $data[] = ['N° DIT', 'N° Or', 'Date planning', "Date Or", "Agence Emetteur", "Service Emetteur", 'Agence Débiteur', 'Service Débiteur', 'N° Intv', 'N° lig', 'Cst', 'Réf.', 'Désignations', 'Qté demandée', 'Utilisateur', 'ID Materiel', 'Marque', 'Casier'];
        foreach ($entities as $entity) {
            $data[] = [
                $entity['referencedit'],
                $entity['numeroor'],
                $entity['datePlanning'],
                $entity['datecreation'],
                $entity['agencecrediteur'],
                $entity['servicecrediteur'],
                $entity['agence'],
                $entity['service'],
                $entity['numinterv'],
                $entity['numeroligne'],
                $entity['constructeur'],
                $entity['referencepiece'],
                $entity['designationi'],
                $entity['quantitedemander'],
                $entity['nomPrenom'],
                $entity['idMateriel'],
                $entity['marque'],
                $entity['casier']
            ];
        }

        $this->excelServiceService->createSpreadsheet($data);
    }

    private function innitialisationCriteria($agenceUser)
    {
        return [
            "agenceUser" => $agenceUser
        ];
    }
    private function recupData($criteria, $magasinModel)
    {
        $lesOrSelonCondition = $this->recupNumOrTraiterSelonCondition($criteria, $magasinModel, $this->getEntityManager());

        $data = $magasinModel->recupereListeMaterielValider($criteria, $lesOrSelonCondition);

        //enregistrer les critère de recherche dans la session
        $this->sessionManagerService->set('magasin_liste_or_traiter_search_criteria', $criteria);

        //ajouter le numero dit dans data
        for ($i = 0; $i < count($data); $i++) {
            $numeroOr = $data[$i]['numeroor'];
            $data[$i]['nomPrenom'] = $magasinModel->recupUserCreateNumOr($numeroOr)[0]['nomprenom'];
            $datePlannig1 = $magasinModel->recupDatePlanning1($numeroOr);
            $datePlannig2 = $magasinModel->recupDatePlanning2($numeroOr);
            if (!empty($datePlannig1)) {
                $data[$i]['datePlanning'] = $datePlannig1[0]['dateplanning1'];
            } else if (!empty($datePlannig2)) {
                $data[$i]['datePlanning'] = $datePlannig2[0]['dateplanning2'];
            } else {
                $data[$i]['datePlanning'] = '';
            }


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
