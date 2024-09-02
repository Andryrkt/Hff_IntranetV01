<?php
namespace App\Controller\planning;

use App\Controller\Controller;
use App\Form\PlanningType;
use App\Model\planning\PlanningModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
class PlanningController extends Controller
{       
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

            


            $form = self::$validator->createBuilder(PlanningType::class,null,[ 'method' =>'GET'
            ])->getForm();

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid())
            {
                dd($form->getData());
            }
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