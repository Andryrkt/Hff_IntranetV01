<?php

namespace App\Controller\magasin\cis;
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
use App\Entity\admin\Application;
use App\Entity\dit\DemandeIntervention;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Model\magasin\cis\CisATraiterModel;
use App\Controller\Traits\AutorisationTrait;
use App\Form\magasin\cis\ATraiterSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\magasin\cis\AtraiterTrait;
use App\Controller\BaseController;

/**
 * @Route("/magasin/cis")
 */
class CisATraiterController extends BaseController
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

    use AtraiterTrait;
    use AutorisationTrait;

    /**
     * @Route("/cis-liste-a-traiter", name="cis_liste_a_traiter")
     */
    public function listCisATraiter(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_MAG);
        /** FIN AUtorisation acées */

        $cisATraiterModel = new CisATraiterModel();

        /** CREATION D'AUTORISATION */
        $autoriser = $this->autorisationRole($this->getEntityManager());
        //FIN AUTORISATION

        $agenceUser = $this->agenceUser($autoriser);

        $form = $this->getFormFactory()->createBuilder(ATraiterSearchType::class, ['agenceUser' => $agenceUser, 'autoriser' => $autoriser], [
            'method' => 'GET'
        ])->getForm();

        $form->handleRequest($request);
        $criteria = [
            "agenceUser" => $agenceUser,
            'orValide' => true
        ];
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }

        $data = $this->recupData($cisATraiterModel, $criteria);

        //enregistrer les critère de recherche dans la session
        $this->sessionManagerService->set('cis_a_traiter_search_criteria', $criteria);

        $this->logUserVisit('cis_liste_a_traiter'); // historisation du page visité par l'utilisateur

        return $this->render('magasin/cis/listATraiter.html.twig', [
            'data' => $data,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/export-excel-a-traiter-cis", name="export_excel_a_traiter_cis")
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $cisATraiterModel = new CisATraiterModel();

        //recupères les critère dans la session 
        $criteria = $this->sessionManagerService->get('cis_a_traiter_search_criteria', []);

        $entities = $this->recupData($cisATraiterModel, $criteria);

        // Convertir les entités en tableau de données
        $data = [];
        $data[] = ['N° DIT', 'N° CIS', 'Date CIS', 'Ag/Serv Travaux', 'N° Or', 'Date Or', "Ag/Serv Débiteur / client", 'N° Intv', 'N° lig', 'Cst', 'Réf.', 'Désignations', 'Qté dem', 'ID Materiel', 'Marque', 'Casier'];
        foreach ($entities as $entity) {
            $data[] = [
                $entity['numdit'],
                $entity['numcis'],
                $entity['datecis'],
                $entity['agenceservicetravaux'],
                $entity['numor'],
                $entity['dateor'],
                $entity['agenceservicedebiteur'],
                $entity['nitv'],
                $entity['numligne'],
                $entity['cst'],
                $entity['ref'],
                $entity['designations'],
                $entity['qte_dem'],
                $entity['idMateriel'],
                $entity['marque'],
                $entity['casier']
            ];
        }

        $this->excelServiceService->createSpreadsheet($data);
    }


    private function recupData($cisATraiterModel, $criteria)
    {
        $ditOrsSoumisRepository = $this->getEntityManager()->getRepository(DitOrsSoumisAValidation::class);
        $numORItvValides = $this->orEnString($ditOrsSoumisRepository->findNumOrItvValide());

        $data = $cisATraiterModel->listOrATraiter($criteria, $numORItvValides);

        for ($i = 0; $i < count($data); $i++) {

            $numeroOr = $data[$i]['numor'];
            $ditRepository = $this->getEntityManager()->getRepository(DemandeIntervention::class)->findOneBy(['numeroOR' => $numeroOr]);
            if ($ditRepository != null) {
                $idMateriel = $ditRepository->getIdMateriel();
                $marqueCasier = $this->ditModelService->recupMarqueCasierMateriel($idMateriel);
                $data[$i]['idMateriel'] = $idMateriel;
                $data[$i]['marque'] = $marqueCasier[0]['marque'];
                $data[$i]['casier'] = $marqueCasier[0]['casier'];
            } else {
                $data[$i]['idMateriel'] = 0;
                $data[$i]['marque'] = '';
                $data[$i]['casier'] = '';
            }
        }

        return $data;
    }
}
