<?php


namespace App\Controller\magasin\ors;

// ini_set('max_execution_time', 10000);

use App\Controller\Controller;
use App\Entity\dit\DemandeIntervention;
use App\Service\TableauEnStringService;
use App\Controller\Traits\Transformation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\MagasinListeOrATraiterModel;
use App\Form\magasin\MagasinListeOrATraiterSearchType;
use App\Controller\Traits\magasin\ors\MagasinOrATraiterTrait;
use App\Controller\Traits\magasin\ors\MagasinTrait as OrsMagasinTrait;

/**
 * @Route("/magasin/or")
 */
class MagasinListeOrTraiterController extends Controller
{
    use Transformation;
    use OrsMagasinTrait;
    use MagasinOrATraiterTrait;

    /**
     * @Route("/liste-magasin", name="magasinListe_index")
     *
     * @return void
     */
    public function index(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $magasinModel = new MagasinListeOrATraiterModel;
        $codeAgence = $this->getUser()->getAgenceAutoriserCode();

        /** CREATION D'AUTORISATION */
        $autoriser = $this->autorisationRole(self::$em);
        //FIN AUTORISATION

        if ($autoriser) {
            $agenceUser = "''";
        } else {
            $agenceUser = TableauEnStringService::TableauEnString(',', $codeAgence);
        }

        $form = self::$validator->createBuilder(MagasinListeOrATraiterSearchType::class, ['agenceUser' => $agenceUser, 'autoriser' => $autoriser], [
            'method' => 'GET'
        ])->getForm();

        $form->handleRequest($request);
        $criteria = $this->innitialisationCriteria($agenceUser);
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }

        //enregistrer les critère de recherche dans la session
        $this->sessionService->set('magasin_liste_or_traiter_search_criteria', $criteria);

        $data = $this->recupData($criteria, $magasinModel);

        $this->logUserVisit('magasinListe_index'); // historisation du page visité par l'utilisateur

        self::$twig->display('magasin/ors/listOrATraiter.html.twig', [
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
        $criteria = $this->sessionService->get('magasin_liste_or_traiter_search_criteria', []);

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

        $this->excelService->createSpreadsheet($data);
    }

    private function innitialisationCriteria($agenceUser)
    {
        return [
            "agenceUser" => $agenceUser
        ];
    }
    private function recupData($criteria, $magasinModel)
    {
        $lesOrSelonCondition = $this->recupNumOrTraiterSelonCondition($criteria, $magasinModel, self::$em);

        $data = $magasinModel->recupereListeMaterielValider($criteria, $lesOrSelonCondition);

        //enregistrer les critère de recherche dans la session
        $this->sessionService->set('magasin_liste_or_traiter_search_criteria', $criteria);

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
