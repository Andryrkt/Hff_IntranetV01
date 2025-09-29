<?php

namespace App\Controller\da\ListeDa;

use App\Entity\da\DaSearch;
use App\Form\da\DaSearchType;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Controller\Traits\da\DaListeTrait;
use App\Controller\Traits\da\StatutBcTrait;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/demande-appro")
 */
class listeDaController extends Controller
{
    use DaListeTrait;
    use AutorisationTrait;

    public function __construct()
    {
        parent::__construct();

        $this->initDaListeTrait();
        $this->initStatutBcTrait();
    }

    /**
     * @Route("/da-list", name="list_da")
     */
    public function index(Request $request)
    {
        $start = microtime(true);

        $fonctions = [];
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        $fonctions[] = ['fonctionName' => 'verifierSessionUtilisateur()', 'executionTime' => number_format(microtime(true) - $start, 4)];

        $start1 = microtime(true);
        /** Autorisation accès */
        $this->autorisationAcces($this->getUser(), Application::ID_DAP);
        /** FIN AUtorisation accès */
        $fonctions[] = ['fonctionName' => '$this->autorisationAcces($this->getUser(), Application::ID_DAP)', 'executionTime' => number_format(microtime(true) - $start1, 4)];

        $start2 = microtime(true);
        /** Initialisation DaSearch */
        $daSearch = new DaSearch;
        $this->initialisationRechercheDa($daSearch);
        $fonctions[] = ['fonctionName' => '$this->initialisationRechercheDa($daSearch)', 'executionTime' => number_format(microtime(true) - $start2, 4)];

        $start3 = microtime(true);
        //formulaire de recherche
        $form = $this->getFormFactory()->createBuilder(DaSearchType::class, $daSearch, ['method' => 'GET'])->getForm();
        $fonctions[] = ['fonctionName' => '$this->getFormFactory()->createBuilder(DaSearchType::class, $daSearch, ...', 'executionTime' => number_format(microtime(true) - $start3, 4)];

        $start4 = microtime(true);
        $form->handleRequest($request);
        $fonctions[] = ['fonctionName' => '$form->handleRequest($request)', 'executionTime' => number_format(microtime(true) - $start4, 4)];

        $start5 = microtime(true);
        if ($form->isSubmitted() && $form->isValid()) {
            $daSearch = $form->getData();
        }
        $fonctions[] = ['fonctionName' => 'if ($form->isSubmitted() && $form->isValid())', 'executionTime' => number_format(microtime(true) - $start5, 4)];

        $criteria = [];

        $start6 = microtime(true);
        //transformer l'objet daSearch en tableau
        $criteria = $daSearch->toArray();
        $fonctions[] = ['fonctionName' => '$criteria = $daSearch->toArray()', 'executionTime' => number_format(microtime(true) - $start6, 4)];

        $start7 = microtime(true);
        //recupères les données du criteria dans une session nommé criteria_search_list_da
        $this->getSessionService()->set('criteria_search_list_da', $criteria);
        $fonctions[] = ['fonctionName' => '$this->getSessionService()->set(\'criteria_search_list_da\', $criteria)', 'executionTime' => number_format(microtime(true) - $start7, 4)];

        $sortJoursClass = false;

        $start8 = microtime(true);
        if ($criteria && $criteria['sortNbJours']) {
            $sortJoursClass = $criteria['sortNbJours'] === 'asc' ? 'fas fa-arrow-up-1-9' : 'fas fa-arrow-down-9-1';
        }
        $fonctions[] = ['fonctionName' => 'if ($criteria && $criteria[\'sortNbJours\'])', 'executionTime' => number_format(microtime(true) - $start8, 4)];

        $start9 = microtime(true);
        //recupère le numero de page
        $page = $request->query->getInt('page', 1);
        $fonctions[] = ['fonctionName' => '$page = $request->query->getInt(\'page\', 1)', 'executionTime' => number_format(microtime(true) - $start9, 4)];

        //nombre de ligne par page
        $limit = 20;

        $start10 = microtime(true);
        // Donnée à envoyer à la vue
        $paginationData = $this->getPaginationData($criteria, $page, $limit, $fonctions);
        $fonctions[] = ['fonctionName' => '$this->getPaginationData($criteria, $page, $limit)', 'executionTime' => number_format(microtime(true) - $start10, 4)];

        $start11 = microtime(true);
        $dataPrepared = $this->prepareDataForDisplay($paginationData['data']);
        $fonctions[] = ['fonctionName' => '$this->prepareDataForDisplay($paginationData[\'data\'])', 'executionTime' => number_format(microtime(true) - $start11, 4)];

        return $this->render('da/list-da.html.twig', [
            'data'           => $dataPrepared,
            'fonctions'      => $fonctions,
            'form'           => $form->createView(),
            'criteria'       => $criteria,
            'sortJoursClass' => $sortJoursClass,
            'currentPage'    => $paginationData['currentPage'],
            'totalPages'     => $paginationData['lastPage'],
            'resultat'       => $paginationData['totalItems'],
        ]);
    }
}
