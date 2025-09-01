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
use App\Model\magasin\cis\CisALivrerModel;
use App\Entity\dit\DitOrsSoumisAValidation;
use App\Form\magasin\cis\ALivrerSearchtype;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\magasin\cis\ALivrerTrait;
use App\Controller\BaseController;

/**
 * @Route("/magasin/cis")
 */
class CisALivrerController extends BaseController
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

    use ALivrerTrait;
    use AutorisationTrait;

    /**
     * @Route("/cis-liste-a-livrer", name="cis_liste_a_livrer")
     */
    public function listCisALivrer(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_MAG);
        /** FIN AUtorisation acées */

        $cisATraiterModel = new CisALivrerModel();

        /** CREATION D'AUTORISATION */
        $autoriser = $this->autorisationRole($this->getEntityManager());
        //FIN AUTORISATION

        $agenceUser = $this->agenceUser($autoriser);

        $form = $this->getFormFactory()->createBuilder(ALivrerSearchtype::class, ['agenceUser' => $agenceUser, 'autoriser' => $autoriser], [
            'method' => 'GET'
        ])->getForm();



        $form->handleRequest($request);
        $criteria = [
            "agenceUser" => $agenceUser,
            "orValide" => true,
        ];
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }

        $data = $this->recupData($cisATraiterModel, $criteria);

        //enregistrer les critère de recherche dans la session
        $this->sessionManagerService->set('cis_a_Livrer_search_criteria', $criteria);

        $this->logUserVisit('cis_liste_a_livrer'); // historisation du page visité par l'utilisateur

        return $this->render('magasin/cis/listALivrer.html.twig', [
            'data' => $data,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/export-excel-cis-a-livrer", name="export_excel_cis_a_livrer")
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $cisATraiterModel = new CisALivrerModel();

        //recupères les critère dans la session 
        $criteria = $this->sessionManagerService->get('cis_a_Livrer_search_criteria', []);

        $entities = $this->recupData($cisATraiterModel, $criteria);

        // Convertir les entités en tableau de données
        $data = [];
        $data[] = ['N° DIT', 'N° CIS', 'Date CIS', 'Ag/Serv Travaux', 'N° OR', 'Date OR', "Ag/Serv Débiteur / client", 'N° Intv', 'N° lig', 'Cst', 'Réf.', 'Désignations', 'Qté cde', 'Qté à liv', 'Qté liv', 'ID Materiel', 'Marque', 'Casier'];
        foreach ($entities as $entity) {
            $data[] = [
                $entity['num_dit'],
                $entity['num_cis'],
                $entity['date_cis'],
                $entity['agence_service_travaux'],
                $entity['num_or'],
                $entity['date_or'],
                $entity['agence_service_debiteur_ou_client'],
                $entity['nitv'],
                $entity['numligne'],
                $entity['cst'],
                $entity['ref'],
                $entity['designations'],
                $entity['quantitercommander'],
                $entity['quantiteralivrer'],
                $entity['quantiterlivrer'],
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
        $data = $cisATraiterModel->listOrALivrer($criteria, $numORItvValides);

        for ($i = 0; $i < count($data); $i++) {

            $numeroOr = $data[$i]['num_or'];
            $ditRepository = $this->getEntityManager()->getRepository(DemandeIntervention::class)->findOneBy(['numeroOR' => $numeroOr]);
            $idMateriel = $ditRepository->getIdMateriel();
            $marqueCasier = $this->ditModelService->recupMarqueCasierMateriel($idMateriel);
            $data[$i]['idMateriel'] = $idMateriel;
            $data[$i]['marque'] = $marqueCasier[0]['marque'];
            $data[$i]['casier'] = $marqueCasier[0]['casier'];
        }
        return $data;
    }
}
