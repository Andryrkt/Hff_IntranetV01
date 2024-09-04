<?php
namespace App\Controller\planning;

use App\Controller\Controller;

use App\Entity\PlanningMateriel;
use App\Form\PlanningSearchType;
use App\Form\PlanningFormulaireType;
use App\Model\planning\PlanningModel;
use App\Entity\planning\PlanningSearch;
use App\Controller\Traits\Transformation;
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
            
            $planningSearch = new PlanningSearch();
            //initialisation
            $planningSearch
                ->setAnnee(date('Y'))
                ->setFacture('ENCOURS')
                ->setPlan('PLANIFIE')
                ->setInterneExterne('TOUS')
            ;
        

            $form = self::$validator->createBuilder(PlanningSearchType::class,$planningSearch,
            [ 
                'method' =>'GET'
            ])->getForm();

            $form->handleRequest($request);
           $criteria = $planningSearch;
            if($form->isSubmitted() && $form->isValid())
            {
                //dd($form->getdata());
                $criteria =  $form->getdata();
                
            }
           

            $data = $this->planningModel->recuperationMaterielplanifier($criteria);
            
           
             
            $table = [];
            //Recuperation de idmat et les truc
            foreach ($data as $item ) {
                $planningMateriel = new PlanningMateriel();
                  //initialisation
                 $planningMateriel
                        ->setCodeSuc($item['codesuc'])
                        ->setLibSuc($item['libsuc'])
                        ->setCodeServ($item['codeserv'])
                        ->setLibServ($item['libserv'])
                        ->setIdMat($item['idmat'])
                        ->setMarqueMat($item['markmat'])
                        ->setTypeMat($item['typemat'])
                        ->setNumSerie($item['numserie'])
                        ->setNumParc($item['numparc'])
                        ->setCasier($item['casier'])
                        ->setAnnee($item['annee'])
                        ->setMois($item['mois'])
                        ->setOrIntv($item['orintv'])
                    ;
                    $table[] = $planningMateriel;
            }

         
           
            self::$twig->display('planning/planning.html.twig', [
                'form' => $form->createView(),
                'data' => $table

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