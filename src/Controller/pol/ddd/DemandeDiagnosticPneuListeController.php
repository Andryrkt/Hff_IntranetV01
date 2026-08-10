<?php

namespace App\Controller\pol\ddd;

use App\Controller\Controller;
use App\Entity\ddd\Chantier;
use App\Entity\ddd\DemandeDiagnosticPneuSearch;
use App\Entity\ddd\DemandeDiagnosticPneuSearchType;
use App\Model\ddd\DemandeDiagnosticPneuListeModel;
use App\Service\security\SecurityService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/pol/demande-diagnostic-pneu")
 */
class DemandeDiagnosticPneuListeController extends Controller
{
    private DemandeDiagnosticPneuListeModel $listModel;

    public function __construct()
    {
        parent::__construct();
        $this->listModel = new DemandeDiagnosticPneuListeModel(
            $this->getEntityManager()
        );
    }

    /**
     * @Route("/liste", name="demande_diagnostic_pneu_liste")
     */
    public function index(Request $request)
    {
        $search = new DemandeDiagnosticPneuSearch();
        $chantiers = $this->getEntityManager()
            ->getRepository(Chantier::class)
            ->findBy([], ['nomChantier' => 'ASC']);

        $criteria = $this->getSessionService()->get('ddd_search_criteria', []);
        if (!empty($criteria)) {
            $search->fromArray($criteria);
        }

        $form = $this->getFormFactory()
            ->createBuilder(DemandeDiagnosticPneuSearchType::class, $search, [
                'method' => 'GET',
                'chantiers' => $chantiers,
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getSessionService()->set('ddd_search_criteria', $search->toArray());
        }

        // Récupération des données paginées
        $agenceIdUser = $this->getSecurityService()->getAgenceIdUser();
        $serviceIdUser = $this->getSecurityService()->getServiceIdUser();
        $multisuccursale = $this->getSecurityService()->verifierPermission(SecurityService::PERMISSION_MULTI_SUCCURSALE);

        $paginationData = $this->listModel->getPaginatedList(
            $search,
            $request->query->getInt('page', 1),
            20, // Nombre d'éléments par page
            $agenceIdUser,
            $serviceIdUser,
            $multisuccursale
        );
        // Historique de visite
        $this->logUserVisit('demande_diagnostic_pneu_liste');

        return $this->render('pol/ddd/list.html.twig', [
            'data' => $paginationData['data'],
            'currentPage' => $paginationData['currentPage'],
            'totalPages' => $paginationData['lastPage'],
            'totalItems' => $paginationData['totalItems'],
            'criteria' => $criteria,
            'form' => $form->createView(),
        ]);
    }
}
