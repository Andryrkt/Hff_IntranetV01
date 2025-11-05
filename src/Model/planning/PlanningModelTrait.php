<?php

namespace App\Model\planning;

use App\Service\GlobalVariablesService;

trait PlanningModelTrait
{
  private function criterAnnee($criteria)
  {
    if (!empty($criteria->getAnnee())) {
      $annee = " '" . $criteria->getAnnee() . "' ";
    } else {
      $annee = null;
    }

    return $annee;
  }

  private function facture($criteria)
  {
    switch ($criteria->getFacture()) {
      case "TOUS":
        $vStatutFacture = " AND  sitv_pos  IN ('FC','FE','CP','ST','EC')";
        break;
      case "FACTURE":
        $vStatutFacture = " AND  sitv_pos IN ('FC','FE','CP','ST')";
        break;
      case "ENCOURS":
        $vStatutFacture = " AND sitv_pos NOT IN ('FC','FE','CP','ST')";
        break;
    }

    return $vStatutFacture;
  }

  private function section($criteria)
  {
    if (!empty($criteria->getSection())) {
      $section = " AND sitv_typitv = '" . $criteria->getSection() . "' ";
    } else {
      $section = null;
    }
    return $section;
  }


  private function typeLigne($criteria)
  {
    // Déterminer la valeur de typeLigne selon si criteria est un objet ou un tableau
    if (is_array($criteria)) {
      $typeLigne = $criteria['typeLigne'] ?? null;
    } elseif (is_object($criteria)) {
      $typeLigne = $criteria->getTypeLigne();
    } else {
      throw new \InvalidArgumentException('Criteria must be an array or an object.');
    }

    // Appliquer les conditions selon typeLigne
    switch ($typeLigne) {
      case "TOUTES":
        $vtypeligne = " ";
        break;
      case "PIECES_MAGASIN":
        $constructeurPiecesMagasin = GlobalVariablesService::get('pieces_magasin');
        $vtypeligne = " AND slor_constp in ( $constructeurPiecesMagasin ) AND slor_typlig = 'P' AND (slor_refp not like '%-L' and slor_refp not like '%-CTRL') ";
        break;
      case "ACHAT_LOCAUX":
        $constructeurAchatLocaux = GlobalVariablesService::get('achat_locaux');
        $vtypeligne = " AND slor_constp in ( $constructeurAchatLocaux )";
        break;
      case "LUBRIFIANTS":
        $constructeurLub = GlobalVariablesService::get('lub');
        $vtypeligne = " AND slor_constp in ( $constructeurLub )  AND slor_typlig = 'P'";
        break;
      case "PNEUMATIQUES":
        $constructeurPneumatique = GlobalVariablesService::get('pneumatique');
        $vtypeligne = " AND slor_constp in ( $constructeurPneumatique ) ";
        break;
      default:
        $vtypeligne = " ";
        break;
    }

    return $vtypeligne;
  }


  // private function sumPieces($criteria){

  //   switch ($criteria->getTypeLigne()) {
  //     case "TOUTES": 
  //         $vPieces = " ";
  //         break;
  //     case "PIECES_MAGASIN":
  //         $vPieces = " AND slor_constp in ('AGR','ATC','AUS','CAT','CGM','CMX','DNL','DYN','GRO','HYS','JDR','KIT','MAN','MNT','OLY','OOM','PAR','PDV','PER','PUB','REM','SHM','TBI','THO') AND slor_typlig = 'P'";
  //         break;
  //     case "ACHAT_LOCAUX":
  //         $vPieces = " AND slor_constp in ('ALI','BOI','CAR','CEN','FAT','FBU','HAB','INF','MIN','OUT','ZST')" ;
  //         break;
  //     case "LUBRIFIANTS":
  //         $vPieces = "AND slor_constp in ('LUB', 'JOV')  AND slor_typlig = 'P'";
  //         break;
  //     default:
  //         $vPieces = " ";
  // }
  // return $vPieces;

  // }

  private function planAnnee($criteria)
  {
    $yearsDatePlanifier = " CASE WHEN 
                    YEAR ( (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) ) is Null 
                THEN
                    YEAR(DATE(sitv_datepla)  )
                ELSE
                    YEAR ( (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) )
                END ";


    $yearsDateNonPlanifier = " YEAR ( DATE(sitv_datdeb) ) ";

    switch ($criteria->getPlan()) {
      case "PLANIFIE":
        $vYearsStatutPlan = $yearsDatePlanifier;
        break;
      case "NON_PLANIFIE":
        $vYearsStatutPlan = $yearsDateNonPlanifier;
    }
    return  $vYearsStatutPlan;
  }
  private function nonplannfierSansDatePla($criteria)
  {
    $conditionSansDatePla = " AND CASE WHEN 
                    YEAR ( (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) ) is Null 
                THEN
                    YEAR(DATE(sitv_datepla)  )
                ELSE
                    YEAR ( (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) )
                END is null";
    switch ($criteria->getPlan()) {
      case "PLANIFIE":
        $vNoplanniferStatutPlan = "";
        break;
      case "NON_PLANIFIE":
        $vNoplanniferStatutPlan = $conditionSansDatePla;
    }
    return  $vNoplanniferStatutPlan;
  }
  private function planMonth($criteria)
  {
    $monthDatePlanifier = " CASE WHEN 
                                    MONTH ( (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) ) is Null 
                                THEN
                                    MONTH(DATE(sitv_datepla)  )
                                ELSE
                                    MONTH ( (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) )
                                END  ";
    $monthDateNonPlanifier =  " MONTH ( DATE(sitv_datdeb) ) ";
    switch ($criteria->getPlan()) {
      case "PLANIFIE":
        $vMonthStatutPlan = $monthDatePlanifier;
        break;
      case "NON_PLANIFIE":
        $vMonthStatutPlan = $monthDateNonPlanifier;
    }
    return  $vMonthStatutPlan;
  }
  private function dateDebutMonthPlan($criteria)
  {

    if (!empty($criteria->getDateDebut())) {
      $monthDatePlanifier = " CASE WHEN 
                                     (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id )  is Null 
                                THEN
                                    DATE(sitv_datepla)  
                                ELSE
                                     (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) 
                                END  ";
      $monthDateNonPlanifier =  "  DATE(sitv_datdeb)  ";
      switch ($criteria->getPlan()) {
        case "PLANIFIE":
          $vDateDMonthStatutPlan = " AND " . $monthDatePlanifier . " >= '" . $criteria->getDateDebut()->format("m/d/Y") . "'";
          break;
        case "NON_PLANIFIE":
          $vDateDMonthStatutPlan = " AND " . $monthDateNonPlanifier . " >= '" . $criteria->getDateDebut()->format("m/d/Y") . "'";
      }
    } else {
      $vDateDMonthStatutPlan = null;
    }
    return $vDateDMonthStatutPlan;
  }
  private function dateFinMonthPlan($criteria)
  {

    if (!empty($criteria->getDateFin())) {
      $monthDatePlanifier = " CASE WHEN 
                                    (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id )  is Null 
                                THEN
                                    DATE(sitv_datepla)  
                                ELSE
                                     (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) 
                                END  ";
      $monthDateNonPlanifier =  " DATE(sitv_datdeb)  ";
      switch ($criteria->getPlan()) {
        case "PLANIFIE":
          $vDateFMonthStatutPlan = " AND " . $monthDatePlanifier . " <= '" . $criteria->getDateFin()->format("m/d/Y") . "'";
          break;
        case "NON_PLANIFIE":
          $vDateFMonthStatutPlan = " AND " . $monthDateNonPlanifier . " <= '" . $criteria->getDateFin()->format("m/d/Y") . "'";
      }
    } else {
      $vDateFMonthStatutPlan = null;
    }

    return $vDateFMonthStatutPlan;
  }

  private function interneExterne($criteria)
  {
    switch ($criteria->getInterneExterne()) {
      case "TOUS":
        $vStatutInterneExterne = "";
        break;
      case "INTERNE":
        $vStatutInterneExterne = " AND SITV_NATOP = 'CES'  and SITV_TYPEOR not in ('501','601','602','603','604','605','606','607','608','609','610','611','701','702','703','704','705','706')";
        break;
      case "EXTERNE":
        $vStatutInterneExterne = "AND seor_numcli >1 ";
        break;
    }
    return $vStatutInterneExterne;
  }
  private function agence($criteria)
  {
    if (!empty($criteria->getAgence())) {
      $agence = " AND SEOR_SUCC in ('" . $criteria->getAgence() . "')";
    } else {
      $agence = "";
    }
    return $agence;
  }
  private function agenceDebite($criteria)
  {
    if (!empty($criteria->getAgenceDebite())) {
      $agenceDebite = " AND sitv_succdeb = '" . $criteria->getAgenceDebite() . "' ";
    } else {
      $agenceDebite = ""; // AND sitv_succdeb in ('01','02','90','92','40','60','50','40','30','20')
    }
    return $agenceDebite;
  }
  private function serviceDebite($criteria)
  {
    if (!empty($criteria->getServiceDebite())) {
      $serviceDebite = " AND sitv_servdeb in ('" . implode("','", $criteria->getServiceDebite()) . "')";
    } else {
      $serviceDebite = "";
    }
    return  $serviceDebite;
  }
  private function idMat($criteria)
  {
    if (!empty($criteria->getIdMat())) {
      $vconditionIdMat = " AND mmat_nummat = '" . $criteria->getIdMat() . "'";
    } else {
      $vconditionIdMat = "";
    }
    return $vconditionIdMat;
  }
  private function numOr($criteria)
  {
    if (!empty($criteria->getNumOr())) {
      $vconditionNumOr = " AND slor_numor ='" . $criteria->getNumOr() . "'";
    } else {
      $vconditionNumOr = "";
    }
    return $vconditionNumOr;
  }
  private function numSerie($criteria)
  {
    if (!empty($criteria->getNumSerie())) {
      $vconditionNumSerie = " AND TRIM(mmat_numserie) = '" . $criteria->getNumSerie() . "' ";
    } else {
      $vconditionNumSerie = "";
    }
    return $vconditionNumSerie;
  }

  private function numParc($criteria)
  {
    if (!empty($criteria->getNumParc())) {
      $vconditionNumParc = " AND mmat_recalph = '" . $criteria->getNumParc() . "'";
    } else {
      $vconditionNumParc = "";
    }
    return $vconditionNumParc;
  }
  private function casier($criteria)
  {
    if (!empty($criteria->getCasier())) {
      $vconditionCasier = " AND mmat_numparc like  '%" . $criteria->getCasier() . "%'  ";
    } else {
      $vconditionCasier = "";
    }
    return $vconditionCasier;
  }
}
