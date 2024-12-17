<?php

namespace App\Api\planning;

use App\Controller\Controller;
use App\Model\planning\PlanningModel;
use App\Entity\dit\DemandeIntervention;
use Symfony\Component\Routing\Annotation\Route;

class PlanningApi extends Controller
{
    private PlanningModel $planningModel;

    public function __construct()
    {
        parent::__construct();
        $this->planningModel = new PlanningModel();
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
        $criteria = $this->sessionService->get('planning_search_criteria', []);
    // dd($criteria);
        //RECUPERATION DE LISTE DETAIL 
        if ($numOr === '') {
            $details = [];
        } else {
            $details = $this->planningModel->recuperationDetailPieceInformix($numOr, $criteria);
            
            $ditRepositoryConditionner = self::$em->getRepository(DemandeIntervention::class)->findOneBy(['numeroOR' => explode('-', $numOr)[0]]);
            $numDit = $ditRepositoryConditionner->getNumeroDemandeIntervention();
            $migration = $ditRepositoryConditionner->getMigration();
            
            $detailes = [];
             dd($details);
            $recupPariel = [];
            $recupGot = [];
            for ($i=0; $i < count($details); $i++) {
                if(empty($details[$i]['numerocmd']) || $details[$i]['numerocmd'] == "0" ){
                    $recupGot = [];
                } else {
                    $detailes[]= $this->planningModel->recuperationEtaMag($details[$i]['numor'], $details[$i]['ref']);
                    $recupPariel[] = $this->planningModel->recuperationPartiel($details[$i]['numerocmd'],$details[$i]['ref']);
                    $recupGot['ord']= $this->planningModel->recuperationinfodGcot($details[$i]['numerocmd']);
                }
                
                if(!empty($detailes[0])){
                        $details[$i]['Eta_ivato'] = $detailes[0][0]['Eta_ivato'];
                        $details[$i]['Eta_magasin'] =  $detailes[0][0]['Eta_magasin']; 
                        $detailes = [];                 
                } 
                else {
                    $details[$i]['Eta_ivato'] = "";
                    $details[$i]['Eta_magasin'] = "";  
                    $detailes = [];              
                } 
                
                if(!empty($recupPariel[$i])){
                    $details[$i]['qteSlode'] = $recupPariel[$i]['0']['solde'];
                    $details[$i]['qte'] = $recupPariel[$i]['0']['qte'];
                }else{
                    $details[$i]['qteSlode'] = "";
                    $details[$i]['qte'] = "";
                }

                if(!empty($recupGot)){
                    $details[$i]['Ord']= $recupGot['ord'] === false ? '' : $recupGot['ord']['Ord'];
                }else{
                    $details[$i]['Ord'] = "";
                }
            
                $details[$i]['numDit'] = $numDit;
                $details[$i]['migration'] = $migration;
            }

        }


        header("Content-type:application/json");

        echo json_encode($details);
    }

    /**
     * @Route("/api/technicien-intervenant/{numOr}/{numItv}", name="")
     */
    public function TechnicienIntervenant($numOr, $numItv)
    {
        $matriculeNom = $this->planningModel->recupTechnicientIntervenant($numOr, $numItv);

        if(empty($matriculeNom))
        {
            $matriculeNom = $this->planningModel->recupTechnicien2($numOr, $numItv);
        }

        header("Content-type:application/json");

        echo json_encode($matriculeNom);
    }
}