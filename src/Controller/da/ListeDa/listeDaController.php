<?php

namespace App\Controller\da\ListeDa;

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
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** Autorisation accès */
        $this->autorisationAcces($this->getUser(), Application::ID_DAP);
        /** FIN AUtorisation accès */

        //formulaire de recherche
        $form = $this->getFormFactory()->createBuilder(DaSearchType::class, null, ['method' => 'GET'])->getForm();

        $form->handleRequest($request);

        $criteria = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }
        $this->getSessionService()->set('criteria_for_excel_list_da', $criteria);

        $sortJoursClass = false;

        if ($criteria && $criteria['sortNbJours']) {
            $sortJoursClass = $criteria['sortNbJours'] === 'asc' ? 'fas fa-arrow-up-1-9' : 'fas fa-arrow-down-9-1';
        }

        //recupère le numero de page
        $page = $request->query->getInt('page', 1);
        //nombre de ligne par page
        $limit = 20;

        // Donnée à envoyer à la vue
        $paginationData = $this->getPaginationData($criteria, $page, $limit);
        $dataPrepared = $this->prepareDataForDisplay($paginationData['data']);

        return $this->render('da/list-da.html.twig', [
            'data'           => $dataPrepared,
            'form'           => $form->createView(),
            'criteria'       => $criteria,
            'sortJoursClass' => $sortJoursClass,
            'currentPage'    => $paginationData['currentPage'],
            'totalPages'     => $paginationData['lastPage'],
            'resultat'       => $paginationData['totalItems'],
        ]);
    }
}
