<?php
namespace App\Controller\planning;

use App\Controller\Controller;

use App\Model\planning\PlanningModel;

use App\Entity\planning\PlanningSearch;
use App\Controller\Traits\Transformation;
use App\Entity\planning\PlanningMateriel;
use App\Form\planning\PlanningSearchType;
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
                        ->setQteCdm($item['qtecdm'])
                        ->setQteLiv($item['qtliv'])
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
   
    /**
     * @Route("/detail-modal/{numOr}", name="liste_detailModal")
     *
     * @return void
     */
    public function detailModal($numOr)
    {
       
        //RECUPERATION DE LISTE DETAIL 
        if ($numOr === '') {
            $details = [];
        } else {
            $details = $this->planningModel->recuperationDetailPieceInformix($numOr);
            
            $detailes = [];

            for ($i=0; $i < count($details); $i++) { 
             
                if(!empty($details[$i]['numerocmd']) && $details[$i]['numerocmd'] <> "0"){
                    $detailes[]= $this->planningModel->recuperationEtaMag($details[$i]['numor'], $details[$i]['ref']);
                    $recupPariel = $this->planningModel->recuperationPartiel($details[$i]['numerocmd'],$details[$i]['ref']);
                }

                if(!empty($detailes[$i])){

                    $details[$i]['Eta_ivato'] = $detailes[$i]['0']['Eta_ivato'];
                    $details[$i]['Eta_magasin'] =  $detailes[$i]['0']['Eta_magasin'];                    
                } else {
                    $details[$i]['Eta_ivato'] = "";
                    $details[$i]['Eta_magasin'] = "";               
                } 
                if(!empty($recupPariel[$i])){
                    $details[$i]['qteSlode'] = $recupPariel[$i]['solde'];
                    $details[$i]['qte'] = $recupPariel[$i]['qte'];
                }else{
                    $details[$i]['qteSlode'] = "";
                    $details[$i]['qte'] = "";
                }
            }
        }

        // dd($details);
        header("Content-type:application/json");

        echo json_encode($details);
    }
}