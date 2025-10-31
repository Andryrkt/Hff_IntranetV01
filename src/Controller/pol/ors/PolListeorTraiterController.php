<?php


namespace App\Controller\pol\ors;

// ini_set('max_execution_time', 10000);

use App\Model\dit\DitModel;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\dit\DemandeIntervention;
use App\Service\TableauEnStringService;
use App\Controller\Traits\Transformation;
use Symfony\Component\Form\FormInterface;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\magasin\MagasinListeOrATraiterModel;
use App\Form\magasin\MagasinListeOrATraiterSearchType;
use App\Controller\Traits\magasin\ors\MagasinOrATraiterTrait;
use App\Controller\Traits\magasin\ors\MagasinTrait as OrsMagasinTrait;

/**
 * @Route("/pol/or-pol")
 */
class PolListeOrTraiterController extends Controller
{
    use Transformation;
    use OrsMagasinTrait;
    use MagasinOrATraiterTrait;
    use AutorisationTrait;

    private $ditModel;

    public function __construct()
    {
        parent::__construct();
        $this->ditModel = new DitModel();
    }

    /**
     * @Route("/listes-or-a-traiter", name="pol_or_liste_a_traiter")
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
            'method' => 'GET',
            'est_pneumatique' => true
        ])->getForm();

        //traitement du formulaire et recupération des data
        $data = $this->traitementFormualire($form, $request, $agenceUser);

        $this->logUserVisit('magasinListe_index'); // historisation du page visité par l'utilisateur

        return $this->render('pol/ors/listOrATraiter.html.twig', [
            'data' => $data,
            'form' => $form->createView(),
            'est_pneumatique' => true
        ]);
    }

    private function traitementFormualire(FormInterface $form, Request $request, string $agenceUser): array
    {
        $form->handleRequest($request);

        $criteria = [
            "agenceUser" => $agenceUser
        ];
        if ($form->isSubmitted() && $form->isValid()) {

            // recupération des données du formulaire
            $criteria = $form->getData();
        }
        //enregistrer les critère de recherche dans la session
        $this->getSessionService()->set('pol_liste_or_traiter_search_criteria', $criteria);

        //recupération des données
        return $this->recupData($criteria, new MagasinListeOrATraiterModel());
    }



    /**
     * @Route("/pol-list-or-traiter-export-excel", name="pol_list_or_traiter_export_excel")
     *
     * @return void
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $magasinModel = new MagasinListeOrATraiterModel();
        //recupères les critère dans la session 
        $criteria = $this->getSessionService()->get('pol_liste_or_traiter_search_criteria', []);

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

        $this->getExcelService()->createSpreadsheet($data);
    }


    private function recupData($criteria, $magasinListeOrATraiterModel)
    {
        $lesOrSelonCondition = $this->recupNumOrTraiterSelonCondition($criteria, $magasinListeOrATraiterModel, $this->getEntityManager());

        $data = $magasinListeOrATraiterModel->getListeOrTraiterPol($criteria, $lesOrSelonCondition);

        //enregistrer les critère de recherche dans la session
        $this->getSessionService()->set('pol_liste_or_traiter_search_criteria', $criteria);

        //ajouter le numero dit dans data
        for ($i = 0; $i < count($data); $i++) {
            $ditRepository = $this->getEntityManager()->getRepository(DemandeIntervention::class)->findOneBy(['numeroDemandeIntervention' => $data[$i]['referencedit']]);
            if (!empty($ditRepository)) {
                $data[$i]['niveauUrgence'] = $ditRepository->getIdNiveauUrgence()->getDescription();
            } else {
                break;
            }
        }

        return $data;
    }
}
