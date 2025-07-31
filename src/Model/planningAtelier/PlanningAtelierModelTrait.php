<?php
namespace App\Model\planningAtelier;



trait PlanningAtelierModelTrait {
    private function agenceEm($criteria){
        if (!empty($criteria->getAgenceEm())) {
           $agenceEm = "AND sitv_succ = '".$criteria->getAgenceEm(). "'  ";
        }else{
            $agenceEm = "";
        }
        return $agenceEm;
    }
    private function agenceDeb($criteria){
         if (!empty($criteria->getAgenceDebite())) {
           $agenceDeb = "AND sitv_succdeb = '".$criteria->getAgenceDebite(). "'  ";
        }else{
            $agenceDeb = "";
        }
        return $agenceDeb;
    }
    private function serviceDebite($criteria){
        if(!empty($criteria->getServiceDebite())){
            $serviceDebite = " AND sitv_servdeb in ('".implode("','",$criteria->getServiceDebite())."')";
          } else{
            $serviceDebite = "";
          } 
          return  $serviceDebite;
    } 
    private function numOR($criteria){
         if (!empty($criteria->getNumOr())) {
           $numOR = "AND sitv_numor = '".$criteria->getNumOr(). "'  ";
        }else{
            $numOR = "";
        }
        return $numOR;
    }
    private function ressource($criteria){
         if (!empty($criteria->getResource())) {
           $ressource = "AND skr_name = '".$criteria->getResource(). "'  ";
        }else{
            $ressource = "";
        }
        return $ressource;
    }
    private function section($criteria){
         if (!empty($criteria->getSection())) {
           $section = "AND skg.skg_id = '".$criteria->getSection(). "'  ";
        }else{
            $section = "";
        }
        return $section;
    }
    private function dateDebut_Fin($criteria){
        if (!empty($criteria->getDateDebut()) && !empty($criteria->getDateFin())) {
           $dateDeb = "AND ska_d_start  between DATETIME(".$criteria->getDateDebut()->format("Y-m-d")." ) YEAR TO DAY AND DATETIME( ".$criteria->getDateFin()->format("Y-m-d"). ") YEAR TO DAY";
        }else{
            $dateDeb = "";
        }
        return $dateDeb;
    }
    
}