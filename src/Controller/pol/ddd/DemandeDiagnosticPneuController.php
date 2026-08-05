<?php


namespace App\Controller\pol\ddd;

use App\Controller\Controller;
use App\Entity\ddd\DemandeDiagnosticPneu;
use App\Factory\pol\DemandeDiagnosticPneuFactory;
use App\Form\pol\ddd\demandeDiagnosticPneuType;
use App\Model\ddd\DemandeDiagnosticPneuModel;
use App\Service\historiqueOperation\HistoriqueOperationDDDService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/pol")
 */
class DemandeDiagnosticPneuController extends Controller
{
    private $historiqueOperation;
    private DemandeDiagnosticPneuModel $demandeDiagnosticPneuModel;
    private $demandeDiagnosticPneuRepository;
    private $demandeDiagnosticPneuFactory;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationDDDService($this->getEntityManager());
        $this->demandeDiagnosticPneuModel = new DemandeDiagnosticPneuModel();

        $this->demandeDiagnosticPneuFactory = new DemandeDiagnosticPneuFactory($this->getEntityManager(), $this->demandeDiagnosticPneuModel, $this->historiqueOperation);
        $this->demandeDiagnosticPneuRepository = $this->getEntityManager()->getRepository(DemandeDiagnosticPneu::class);
    }

    /**
     * @Route("/nouveau-demande-diagnostic-pneu", name="nouveau_demande_diagnostic_pneu")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function new(Request $request)
    {

        $demandeDiagnosticPneu = new DemandeDiagnosticPneu;
        $codeSociete = $this->getSecurityService()->getCodeSocieteUser();
        $agenceService = $this->agenceServiceIpsObjet();


        $form = $this->getFormFactory()->createBuilder(demandeDiagnosticPneuType::class, $demandeDiagnosticPneu)->getForm();

        $this->traitementFormulaire($form, $request);

        $this->logUserVisit('demande_diag_pneu_new');

        return $this->render('pol/ddd/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public  function traitementFormulaire($form, Request $request) {}
}
