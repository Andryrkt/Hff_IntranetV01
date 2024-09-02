<?php
namespace App\Controller\planning;

use App\Controller\Controller;

use App\Form\PlanningSearchType;
use App\Form\PlanningFormulaireType;
use App\Model\planning\PlanningModel;
use App\Controller\Traits\Transformation;
use App\Entity\PlanningSearch;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PlanningController extends Controller
{        
    use Transformation; 

        private PlanningModel $planningModel;

        public function __construct()
        {
            parent::__construct();
            $this->planningModel = new PlanningModel();

        }

        /**
         * @Route("/planning", name="planning_vue")
         * 
         * @return void
         */
        public function listePlanning( Request $request){
            
            //$planningSearch = new PlanningSearch();

        

            $form = self::$validator->createBuilder(PlanningSearchType::class,null,
            [ 
                'method' =>'GET'
            ])->getForm();

            $form->handleRequest($request);
            $criteria =[];
            if($form->isSubmitted() && $form->isValid())
            {
                //dd($form->getdata());
                $criteria =  $form->getdata();
            }

            $data = $this->planningModel->recuperationMaterielplanifier($criteria);

            self::$twig->display('planning/planning.html.twig', [
                'form' => $form->createView()
            ]);
        }


     /**
     * @Route("/serviceDebiteurPlanning-fetch/{agenceId}")
     */
    public function serviceDebiteur($agenceId)
    {
        $serviceDebiteur = $this->planningModel->recuperationServiceDebite($agenceId);
       
        header("Content-type:application/json");

        echo json_encode($serviceDebiteur);
    }

}