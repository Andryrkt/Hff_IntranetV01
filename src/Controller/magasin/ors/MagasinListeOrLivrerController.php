<?php


namespace App\Controller\magasin\ors;

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
use App\Model\dit\DitModel;

/**
 * @Route("/magasin/or")
 */
class MagasinListeOrLivrerController extends Controller
{
    use Transformation;
    use OrsMagasinTrait;
    use MagasinOrALIvrerTrait;

    private $ditModel;

    use AutorisationTrait;

    private $magasinListOrLivrerModel;

    public function __construct()
    {
        parent::__construct();
        $this->ditModel = new DitModel();
        $this->magasinListOrLivrerModel = new MagasinListeOrLivrerModel();
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
        $this->getSessionService()->set('magasin_liste_or_livrer_search_criteria', $criteria);

        $data = $this->recupData($criteria);

        $this->logUserVisit('magasinListe_or_Livrer'); // historisation du page visité par l'utilisateur

        return $this->render('magasin/ors/listOrLivrer.html.twig', [
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
        $criteria = $this->getSessionService()->get('magasin_liste_or_livrer_search_criteria', []);

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

        $this->getExcelService()->createSpreadsheet($data);
    }

    private function recupData($criteria)
    {
        $lesOrSelonCondition = $this->recupNumOrSelonCondition($criteria);

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

            $ditRepository = $this->getEntityManager()->getRepository(DemandeIntervention::class)->findOneBy(['numeroOR' => $numeroOr]);

            if (!empty($ditRepository)) {
                $data[$i]['numDit'] = $ditRepository->getNumeroDemandeIntervention();
                $data[$i]['niveauUrgence'] = $ditRepository->getIdNiveauUrgence()->getDescription();
                $idMateriel = $ditRepository->getIdMateriel();
                $marqueCasier = $this->ditModel->recupMarqueCasierMateriel($idMateriel);
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
