<?php


namespace App\Controller\magasin\ors;

ini_set('max_execution_time', 10000);
ini_set('memory_limit', '1000M');


use App\Controller\Controller;
use App\Controller\Traits\magasin\ors\MagasinOrALIvrerTrait;
use App\Controller\Traits\magasin\ors\MagasinTrait as OrsMagasinTrait;
use App\Entity\dit\DemandeIntervention;
use App\Controller\Traits\Transformation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\MagasinListeOrLivrerModel;
use App\Form\magasin\MagasinListeOrALivrerSearchType;

class MagasinListeOrLivrerController extends Controller
{
    use Transformation;
    use OrsMagasinTrait;
    use MagasinOrALIvrerTrait;

    private $magasinListOrLivrerModel;

    public function __construct()
    {
        parent::__construct();
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

        $agenceServiceUser = $this->agenceServiceIpsObjet();

        /** CREATION D'AUTORISATION */
        $autoriser = $this->autorisationRole(self::$em);
        //FIN AUTORISATION

        if ($autoriser) {
            $agenceUser = null;
        } else {
            $agenceUser = $agenceServiceUser['agenceIps']->getCodeAgence() . '-' . $agenceServiceUser['agenceIps']->getLibelleAgence();
        }

        $form = self::$validator->createBuilder(MagasinListeOrALivrerSearchType::class, ['agenceUser' => $agenceUser, 'autoriser' => $autoriser], [
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
        $this->sessionService->set('magasin_liste_or_livrer_search_criteria', $criteria);

        $data = $this->recupData($criteria);

        $this->logUserVisit('magasinListe_or_Livrer'); // historisation du page visité par l'utilisateur

        self::$twig->display('magasin/ors/listOrLivrer.html.twig', [
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
        $criteria = $this->sessionService->get('magasin_liste_or_livrer_search_criteria', []);

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

        $this->excelService->createSpreadsheet($data);
    }

    private function recupData($criteria)
    {
        $lesOrSelonCondition = $this->recupNumOrSelonCondition($criteria, self::$em);

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

            //$dit = self::$em->getRepository(DemandeIntervention::class)->findNumDit($numeroOr);
            $ditRepository = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroOR' => $numeroOr]);

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
