<?php

namespace App\Controller\planningMagasin;

use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\magasin\bc\BcMagasin;
use App\Service\TableauEnStringService;
use App\Controller\Traits\PlanningTraits;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\magasin\bc\BcMagasinRepository;
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
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        /** Autorisation accées */
        $this->autorisationAcces($this->getUser(), Application::ID_REP);
        /** FIN AUtorisation acées */
        //initialisation
        $this->planningMagasinSearch
            ->setAnnee(date('Y'))
            ->setFacture('ENCOURS')
            ->setPlan('PLANIFIE')
            ->setInterneExterne('TOUS')
            ->setTypeLigne('TOUETS')
            ->setMonths(3)
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
            // dd($form->getdata());
            $criteria =  $form->getdata();
        }
        /** @var array $touLesBCSoumis ce qui est valider DW*/
        $tousLesBCSoumis = $this->allBCs();
        //recupère le condition clicsur la légende
        $condition = $request->query->get('condition', "1");
        // dd($condition);
        $back = $this->planningMagasinModel->backOrderplanningMagasin($criteria,$tousLesBCSoumis);

        if (is_array($back)) {
            $backString = TableauEnStringService::orEnString($back);
        } else {
            $backString = '';
        }


        $data = $this->planningMagasinModel->recuperationCommadeplanifier($criteria, $backString, $condition,$tousLesBCSoumis);
        // dump($data);

        $tabObjetPlanning = $this->creationTableauObjetPlanningMagasin($data, $back);
        // dd($tabObjetPlanning);
        // die();
        // Fusionner les objets en fonction de l'idMat
        $fusionResult = $this->ajoutMoiDetailMagasin($tabObjetPlanning);
        // dd($fusionResult);
        $forDisplay = $this->prepareDataForDisplay($fusionResult, $criteria->getMonths() == null ? 3 : $criteria->getMonths());
        return $this->render('planningMagasin/planning.html.twig', [
            'form' => $form->createView(),
            'criteria'       => $criteria->toArray(),
            'uniqueMonths' => $forDisplay['uniqueMonths'],
            'preparedData' => $forDisplay['preparedData'],
        ]);
    }
    private function allBCs(): array
    {
        /** @var array */
        $numBc = $this->BcMagasinRepository->findnumBCAll();
        return $numBc;
    }
}
