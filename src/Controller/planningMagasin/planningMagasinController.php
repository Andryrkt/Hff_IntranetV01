<?php

namespace App\Controller\planningMagasin;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\magasin\bc\BcMagasin;
use App\Entity\admin\utilisateur\Role;
use App\Entity\admin\utilisateur\User;
use App\Service\TableauEnStringService;
use App\Controller\Traits\PlanningTraits;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\planningMagasin\PlanningMagasinModel;
use App\Entity\planningMagasin\PlanningMagasinSearch;
use App\Form\planningMagasin\PlanningMagasinSearchType;

/**
 * @Route("/magasin")
 */
class planningMagasinController extends Controller
{

    use AutorisationTrait;
    use PlanningTraits;


    private PlanningMagasinModel $planningMagasinModel;
    private PlanningMagasinSearch $planningMagasinSearch;
    private $BcMagasinRepository;

    public function __construct()
    {
        parent::__construct();
        $this->planningMagasinModel = new PlanningMagasinModel();
        $this->planningMagasinSearch = new PlanningMagasinSearch();
        $this->BcMagasinRepository = $this->getEntityManager()->getRepository(BcMagasin::class);
    }
    /**
     * @Route("/Planning", name = "interface_planningMag")
     */
    public function headPlanning(Request $request)
    {
        /** Autorisation accées */
        $this->autorisationAcces(Application::ID_REP);
        // autorisation pour affichage
        $autoriser = $this->estAutoriser();
        $codeAgence = $autoriser ? "-0" : $this->getUser()->getCodeAgenceUser();
        /** FIN AUtorisation acées */
        //initialisation
        $this->planningMagasinSearch
            ->setAnnee(date('Y'))
            ->setFacture('ENCOURS')
            ->setPlan('PLANIFIE')
            ->setInterneExterne('TOUS')
            ->setTypeLigne('TOUETS')
            ->setMonths(3)
            ->setAgence($codeAgence)
        ;

        $form = $this->getFormFactory()->createBuilder(
            PlanningMagasinSearchType::class,
            $this->planningMagasinSearch,
            [
                'method' => 'GET'
            ]
        )->getForm();

        $form->handleRequest($request);
        //initialisation criteria
        $criteria = $this->planningMagasinSearch;
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria =  $form->getdata();
        }
        /** @var array $touLesBCSoumis ce qui est valider DW*/
        $tousLesBCSoumis = $this->allBCs();
        //recupère le condition clicsur la légende
        $condition = $request->query->get('condition', "1");
        // dd($condition);
        $back = $this->planningMagasinModel->backOrderplanningMagasin($criteria, $tousLesBCSoumis);

        if (is_array($back)) {
            $backString = TableauEnStringService::orEnString($back);
        } else {
            $backString = '';
        }


        $data = $this->planningMagasinModel->recuperationCommadeplanifier($criteria, $backString, $condition, $tousLesBCSoumis, $codeAgence);
        $tabObjetPlanning = $this->creationTableauObjetPlanningMagasin($data, $back);
        $fusionResult = $this->ajoutMoiDetailMagasin($tabObjetPlanning);
        $forDisplay = $this->prepareDataForDisplay($fusionResult, $criteria->getMonths() == null ? 3 : $criteria->getMonths());
        return $this->render('planningMagasin/planning.html.twig', [
            'form'           => $form->createView(),
            'criteria'       => $criteria->toArray(),
            'uniqueMonths'   => $forDisplay['uniqueMonths'],
            'preparedData'   => $forDisplay['preparedData'],
        ]);
    }

    private function allBCs(): array
    {
        /** @var array */
        $numBc = $this->BcMagasinRepository->findnumBCAll();
        return $numBc;
    }

    private function estAutoriser()
    {
        return $this->hasRoles(Role::ROLE_ADMINISTRATEUR, Role::ROLE_MULTI_SUCURSALES);
    }
}
