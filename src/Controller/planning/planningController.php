<?php
namespace App\Controller\planning;

use App\Controller\Controller;
use App\Form\PlanningFormulaireType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
class PlanningController extends Controller
{       
         
        public function __construct()
        {
            parent::__construct();
        }

        /**
         * @Route("/planning", name="planning_vue")
         * 
         * @return void
         */
        public function listePlanning( Request $request){

            $form = self::$validator->createBuilder(PlanningFormulaireType::class,null,[ 'method' =>'GET'
            ])->getForm();
            $form->handleRequest($request);
            self::$twig->display('planning/planning.html.twig', [
                'form' => $form->createView()
            ]);
        }

}