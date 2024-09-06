<?php 

namespace App\Model\planning;

trait PlanningModelTrait
{
    private function criterAnnee($criteria)
    {
        if(!empty($criteria->getAnnee())){
            $annee = " '".$criteria->getAnnee()."' ";
          }else{
            $annee = null;
          }

          return $annee;
    }

    private function facture($criteria)
    {
        switch ($criteria->getFacture()){
            case "TOUS":  
              $vStatutFacture = " AND  sitv_pos  IN ('FC','FE','CP','ST','EC')";
              break;
            case "FACTURE":
              $vStatutFacture = " AND  sitv_pos IN ('FC','FE','CP','ST')";
              break;
            case "ENCOURS":  
              $vStatutFacture = " AND sitv_pos NOT IN ('FC','FE','CP','ST')" ;
              break;
             
      }
      return $vStatutFacture; 
    }

    private function planAnnee($criteria){
                    $yearsDatePlanifier = " CASE WHEN 
                    YEAR ( (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) ) is Null 
                THEN
                    YEAR(DATE(sitv_datepla)  )
                ELSE
                    YEAR ( (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) )
                END ";
              

            $yearsDateNonPlanifier = " YEAR ( DATE(sitv_datdeb) ) ";  
            
            switch ($criteria->getPlan()){
                    case "PLANIFIE":
                    $vYearsStatutPlan = $yearsDatePlanifier;
                    break;
                    case "NON_PLANIFIE":
                    $vYearsStatutPlan = $yearsDateNonPlanifier;
                    }
             return  $vYearsStatutPlan;     

    }
    private function planMonth($criteria){
        $monthDatePlanifier = " CASE WHEN 
                                    MONTH ( (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) ) is Null 
                                THEN
                                    MONTH(DATE(sitv_datepla)  )
                                ELSE
                                    MONTH ( (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) )
                                END  "; 
        $monthDateNonPlanifier =  " MONTH ( DATE(sitv_datdeb) ) "; 
        switch ($criteria->getPlan()){
            case "PLANIFIE":
            $vMonthStatutPlan = $monthDatePlanifier;
            break;
            case "NON_PLANIFIE":
            $vMonthStatutPlan = $monthDateNonPlanifier;
            }
     return  $vMonthStatutPlan;
    }
    private function dateDebutMonthPlan($criteria){

      if(!empty($criteria->getDateDebut())){
        $monthDatePlanifier = " CASE WHEN 
                                    MONTH ( (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) ) is Null 
                                THEN
                                    MONTH(DATE(sitv_datepla)  )
                                ELSE
                                    MONTH ( (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) )
                                END  "; 
        $monthDateNonPlanifier =  " MONTH ( DATE(sitv_datdeb) ) "; 
        switch ($criteria->getPlan()){
          case "PLANIFIE":
          $vDateDMonthStatutPlan = " AND " .$monthDatePlanifier." >= '".$criteria->getDateDebut()->format("m")."'";
          break;
          case "NON_PLANIFIE":
          $vDateDMonthStatutPlan = " AND " .$monthDateNonPlanifier ." >= '".$criteria->getDateDebut()->format("m")."'";
          }
      }else{
        $vDateDMonthStatutPlan = null;
      }
      return $vDateDMonthStatutPlan;
    }
    private function dateFinMonthPlan($criteria){

      if(!empty($criteria->getDateFin())){
        $monthDatePlanifier = " CASE WHEN 
                                    MONTH ( (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) ) is Null 
                                THEN
                                    MONTH(DATE(sitv_datepla)  )
                                ELSE
                                    MONTH ( (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) )
                                END  "; 
        $monthDateNonPlanifier =  " MONTH ( DATE(sitv_datdeb) ) "; 
        switch ($criteria->getPlan()){
          case "PLANIFIE":
          $vDateFMonthStatutPlan = " AND " .$monthDatePlanifier." <= '".$criteria->getDateFin()->format("m")."'";
          break;
          case "NON_PLANIFIE":
          $vDateFMonthStatutPlan = " AND ".$monthDateNonPlanifier ." <= '".$criteria->getDateFin()->format("m")."'";
          }
      }else{
        $vDateFMonthStatutPlan = null;
      }
     
      return $vDateFMonthStatutPlan;
    }
    
    private function interneExterne($criteria){
        switch ($criteria->getInterneExterne()){
            case "TOUS":
                  $vStatutInterneExterne = "";
                  break;
            case "INTERNE":
                   $vStatutInterneExterne = " AND SITV_NATOP = 'CES'  and SITV_TYPEOR not in ('501','601','602','603','604','605','606','607','608','609','610','611','701','702','703','704','705','706')";
                   break;
            case "EXTERNE":
                   $vStatutInterneExterne = "AND SITV_NATOP <> 'CES' ";
                   break;
          }
          return $vStatutInterneExterne;
    }
    private function agence($criteria)
    {
        if(!empty($criteria->getAgence())) {
          $agence = " AND SEOR_SUCC in ('".implode("','",$criteria->getAgence())."')";
        } else {
          $agence = "";
        }
        return $agence;
    }
    private function agenceDebite($criteria){
        if(!empty($criteria->getAgenceDebite())){
            $agenceDebite = "AND sitv_succdeb = '".$criteria->getAgenceDebite(). "'";
          }else{
            $agenceDebite = "";
          }
          return $agenceDebite;
    }
    private function serviceDebite($criteria){
        if(!empty($criteria->getServiceDebite())){
            $serviceDebite = " AND sitv_servdeb in ('".implode("','",$criteria->getServiceDebite())."')";
          } else{
            $serviceDebite = "";
          } 
          return  $serviceDebite;
    }    
    private function idMat($criteria){
        if(!empty($criteria->getIdMat())){
            $vconditionIdMat = " AND mmat_nummat = " + "'".$criteria->getIdMat()."'";
          }else{
            $vconditionIdMat = "";
          }
          return $vconditionIdMat;
    }
    private function numOr($criteria){
        if(!empty($criteria->getNumOr())){
            $vconditionNumOr = " AND slor_numor = "+ "'".$criteria->getNumOr()."'";
          }else{
            $vconditionNumOr = "";
          }
          return $vconditionNumOr;
    }
    private function numSerie($criteria){
        if(!empty($criteria->getNumSerie())){
            $vconditionNumSerie = " AND mmat_numserie = "+ "'".$criteria->getNumSerie()."'";
          }else{
            $vconditionNumSerie = "";
          }
    return $vconditionNumSerie;
    }
       
    private function numParc($criteria){
        if(!empty($criteria->getNumParc())){
            $vconditionNumParc = " AND mmat_recalph = "+ "'".$criteria->getNumParc()."'";
          }else{
            $vconditionNumParc = "";
          }
          return $vconditionNumParc;
    }
        

        
       
  
    
}