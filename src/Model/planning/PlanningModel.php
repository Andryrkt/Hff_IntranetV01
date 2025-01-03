<?php
namespace App\Model\planning;


use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Controller\Traits\FormatageTrait;
use App\Entity\planning\PlanningSearch;

class PlanningModel extends Model
{
   use ConversionModel;
   use FormatageTrait;
   use PlanningModelTrait;

   public function recuperationAgenceIrium(){
        $statement = " SELECT  trim(asuc_num) as asuc_num ,
                               trim(asuc_lib) as asuc_lib
                      FROM agr_succ
                      WHERE asuc_codsoc = 'HF'
                      AND  (ASUC_NUM like '01' 
                      or ASUC_NUM like '20' 
                      or ASUC_NUM like '30'
                       or ASUC_NUM like '40'
                       or ASUC_NUM like '50'
                       )
                      order by 1
        ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $dataUtf8 = $this->convertirEnUtf8($data);
        return 
          array_map(function($item) {
              return [$item['asuc_num'].'-'.$item['asuc_lib'] => $item['asuc_num']] ;
          }, $dataUtf8);
        
        
   }
   

   public function recuperationAnneeplannification(){
       $query = " SELECT YEAR(ska_d_start) as Annee
                  FROM ska
                  INNER JOIN skw ON skw.skw_id = ska.skw_id
                  GROUP BY 1
                  ORDER BY YEAR(ska_d_start) DESC           
       ";
      $result = $this->connect->executeQuery($query);
      $data = $this->connect->fetchResults($result);
      $dataUtf8 = $this->convertirEnUtf8($data);
      return array_combine(array_column($dataUtf8,'annee'),array_column($dataUtf8,'annee'));
      
   }
   public function recuperationAgenceDebite(){
      $statement = "SELECT  trim(asuc_lib) as asuc_lib,
                            trim(asuc_num) as asuc_num
                    FROM  agr_succ , sav_itv 
                    WHERE asuc_num = sitv_succdeb 
                    AND asuc_codsoc = 'HF'
                    AND asuc_lib <> 'ANTALAHA'
                    AND asuc_num <> '10'
                    group by 1,2
                    order by 1";
      $result = $this->connect->executeQuery($statement);
      $data = $this->connect->fetchResults($result);
      $dataUtf8 = $this->convertirEnUtf8($data);
     return array_combine(
       array_column($dataUtf8, 'asuc_lib'),
       array_map(function($item) {
           return $item['asuc_num'];
       }, $dataUtf8)
     );              
   }
   public function recuperationSection(){

    $statement = "SELECT  DISTINCT TRIM(sitv_typitv) as sec_num,
                                   TRIM(atab_lib2) as sec_Lib
                  FROM sav_itv
                  INNER JOIN agr_tab ON atab_nom = 'TYI'
                  AND atab_code = sitv_typitv ";
     $result = $this->connect->executeQuery($statement);
     $data = $this->connect->fetchResults($result);
     $dataUtf8 = $this->convertirEnUtf8($data);
     return array_combine(
      array_column($dataUtf8, 'sec_lib'),
      array_map(function($item) {
          return $item['sec_num'];}, $dataUtf8)
    ); 
 
   }


   public function recuperationServiceDebite($agence){

    if ($agence === null) {
        $codeAgence = "";
    } else {
      $codeAgence = " AND asuc_num = '" .$agence."'";
    }
    
        $statement = " SELECT DISTINCT
                        trim(atab_code) as atab_code ,
                        trim(atab_lib) as atab_lib  
                        FROM agr_succ , agr_tab a 
                        WHERE a.atab_nom = 'SER' 
                        and a.atab_code not in (select b.atab_code from agr_tab b where substr(b.atab_nom,10,2) = asuc_num and b.atab_nom like 'SERBLOSUC%') 
                        $codeAgence
        ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $dataUtf8 = $this->convertirEnUtf8($data);
        return array_map(function($item) {
          return [
              "value" => $item['atab_code'], 
              "text"  => $item['atab_lib']
          ];
      }, $dataUtf8);  

   }
  public function recuperationMaterielplanifier(PlanningSearch $criteria, string $lesOrValides, string $back)
  {
    if($criteria->getOrBackOrder() == true){
      $vOrvalDw = "AND seor_numor ||'-'||sitv_interv in (".$back.") ";
    } else {
      if(!empty($lesOrValides)){
        $vOrvalDw = "AND seor_numor ||'-'||sitv_interv in ('".$lesOrValides."') ";
      } 
      else{
        $vOrvalDw = " AND seor_numor ||'-'||sitv_interv in ('')";
      }
    }
    

    $vligneType = $this->typeLigne($criteria);  
  
    $vYearsStatutPlan =  $this->planAnnee($criteria);
    $vConditionNoPlanning = $this->nonplannfierSansDatePla($criteria);
    $vMonthStatutPlan = $this->planMonth($criteria);
    $vDateDMonthPlan = $this->dateDebutMonthPlan($criteria);
    $vDateFMonthPlan = $this->dateFinMonthPlan($criteria);
    $vStatutFacture = $this->facture($criteria);
    $annee =  $this->criterAnnee($criteria);
    $agence = $this->agence($criteria);
    $vStatutInterneExterne = $this->interneExterne($criteria);
    $agenceDebite = $this->agenceDebite($criteria);
    $serviceDebite = $this->serviceDebite($criteria);
    $vconditionNumParc = $this->numParc($criteria);
    $vconditionIdMat = $this->idMat($criteria);
    $vconditionNumOr = $this->numOr($criteria);
    $vconditionNumSerie = $this->numSerie($criteria);
    $vconditionCasier = $this->casier($criteria);
    $vsection = $this->section($criteria);
    $vplan = $criteria->getPlan();

                  $statement = " SELECT
                      
                      trim(seor_succ) as codeSuc, 
                      trim(asuc_lib) as libSuc, 
                      trim(seor_servcrt) as codeServ, 
                      trim(ser.atab_lib) as libServ, 
                      trim(sitv_comment) as commentaire,
                      mmat_nummat as idMat,
                      trim(mmat_marqmat) as markMat,
                      trim(mmat_typmat) as typeMat ,
                      trim(mmat_numserie) as numSerie,
                      trim(mmat_recalph) as numParc,
                      trim(mmat_numparc) as casier,
                      $vYearsStatutPlan as annee,
                      $vMonthStatutPlan as mois,
                      seor_numor ||'-'||sitv_interv as orIntv,

                      (  SELECT SUM( CASE WHEN slor_typlig = 'P' $vligneType  THEN
                                                slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec
                                          ELSE slor_qterea END )
                        FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv  $vligneType ) as QteCdm,
                    	(  SELECT SUM(slor_qterea ) FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv  $vligneType ) as QtLiv,
                      (  SELECT SUM(slor_qteres )FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv   $vligneType ) as QteALL
                      

                    FROM  sav_eor,sav_lor as C , sav_itv as D, agr_succ, agr_tab ser, mat_mat, agr_tab ope, outer agr_tab sec
                    WHERE seor_numor = slor_numor
                    AND seor_serv <> 'DEV'
                    AND sitv_numor = slor_numor 
                    AND sitv_interv = slor_nogrp/100
                    AND (seor_succ = asuc_num) -- OR mmat_succ = asuc_parc)
                    AND (seor_servcrt = ser.atab_code AND ser.atab_nom = 'SER')
                    AND (sitv_typitv = sec.atab_code AND sec.atab_nom = 'TYI')
                    AND (seor_ope = ope.atab_code AND ope.atab_nom = 'OPE')
                    $vStatutFacture
                    AND mmat_marqmat NOT like 'z%' AND mmat_marqmat NOT like 'Z%'
                    AND sitv_servcrt IN ('ATE','FOR','GAR','MAN','CSP','MAS', 'LR6', 'LST')
                    AND (seor_nummat = mmat_nummat)
                    AND slor_constp NOT like '%ZDI%'
                    $vOrvalDw
                    $vligneType

                   
                    $vConditionNoPlanning 
                    $agence
                    $vStatutInterneExterne
                    $agenceDebite
                    $serviceDebite
                    $vDateDMonthPlan
                    $vDateFMonthPlan
                    $vconditionNumParc
                    $vconditionIdMat
                    $vconditionNumOr
                    $vconditionNumSerie
                    $vconditionCasier
                    $vsection 
                    group by 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17
		                order by 10  ";      

        
        $result = $this->connect->executeQuery($statement);
                  //  dump($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return $resultat;
  }
public function backOrderPlanning($lesOrValides){
  
  if(!empty($lesOrValides)){
    $vOrvalDw = "AND slor_numor in ('".$lesOrValides."') ";
  }else{
    $vOrvalDw = " AND  slor_numor in ('')";
  }
  

  $statement = "SELECT distinct 
                   sav.slor_numor || '-' || trunc(sav.slor_nogrp/100) AS intervention
                  FROM sav_lor AS sav
                  INNER JOIN gcot_acknow_cat AS cat
                  ON sav.slor_numcf = cat.numero_po
                  AND (sav.slor_nolign = cat.line_number OR  sav.slor_noligncm = cat.line_number)
                  AND sav.slor_refp = cat.parts_number
                  WHERE cat.libelle_type = 'Back Order'
                  AND cat.id_gcot_acknow_cat  = (
                                              SELECT MAX(sub.id_gcot_acknow_cat )
                                              FROM gcot_acknow_cat AS sub
                                              WHERE sub.parts_number = cat.parts_number
                                                AND sub.numero_po = cat.numero_po
                                                AND sub.line_number = cat.line_number
                                          ) 
                  $vOrvalDw
                  
      ";
  $result = $this->connect->executeQuery($statement);
  // dump($statement);
  $data = $this->connect->fetchResults($result);
  $resultat = $this->convertirEnUtf8($data);
  
  return array_map(function($item) {
    return $item['intervention'];
  }, $resultat);

}

  public function exportExcelPlanning($criteria, $lesOrValides){
   
    if(!empty($lesOrValides)){
      $vOrvalDw = "AND seor_numor ||'-'||sitv_interv in ('".$lesOrValides."') ";
    }else{
      $vOrvalDw = " AND seor_numor ||'-'||sitv_interv in ('')";
    }
    $vplanification = "'".$criteria->getPlan()."'";
    $vligneType = $this->typeLigne($criteria);  
    //$vPiecesSum = $this->sumPieces($criteria);
    $vYearsStatutPlan =  $this->planAnnee($criteria);
    $vConditionNoPlanning = $this->nonplannfierSansDatePla($criteria);
    $vMonthStatutPlan = $this->planMonth($criteria);
    $vDateDMonthPlan = $this->dateDebutMonthPlan($criteria);
    $vDateFMonthPlan = $this->dateFinMonthPlan($criteria);
    $vStatutFacture = $this->facture($criteria);
    $annee =  $this->criterAnnee($criteria);
    $agence = $this->agence($criteria);
    $vStatutInterneExterne = $this->interneExterne($criteria);
    $agenceDebite = $this->agenceDebite($criteria);
    $serviceDebite = $this->serviceDebite($criteria);
    $vconditionNumParc = $this->numParc($criteria);
    $vconditionIdMat = $this->idMat($criteria);
    $vconditionNumOr = $this->numOr($criteria);
    $vconditionNumSerie = $this->numSerie($criteria);
    $vconditionCasier = $this->casier($criteria);
    $vsection = $this->section($criteria);

                  $statement = " SELECT
                      trim(seor_succ) as codeSuc, 
                      trim(asuc_lib) as libSuc, 
                      trim(seor_servcrt) as codeServ, 
                      trim(ser.atab_lib) as libServ, 
                      
                      mmat_nummat as idMat,
                      trim(mmat_marqmat) as markMat,
                      trim(mmat_typmat) as typeMat ,
                      trim(mmat_numserie) as numSerie,
                      trim(mmat_recalph) as numParc,
                      trim(mmat_numparc) as casier,
                      $vMonthStatutPlan as mois,
                      $vYearsStatutPlan as annee,
                      seor_numor ||'-'||sitv_interv as orIntv,
                      slor_pos,
                      $vplanification as plan,
                      trim(sitv_comment) as commentaire
                      
                    FROM  sav_eor,sav_lor as C , sav_itv as D, agr_succ, agr_tab ser, mat_mat, agr_tab ope, outer agr_tab sec
                    WHERE seor_numor = slor_numor
                    AND seor_serv <> 'DEV'
                    AND sitv_numor = slor_numor 
                    AND sitv_interv = slor_nogrp/100
                    AND (seor_succ = asuc_num) -- OR mmat_succ = asuc_parc)
                    AND (seor_servcrt = ser.atab_code AND ser.atab_nom = 'SER')
                    AND (sitv_typitv = sec.atab_code AND sec.atab_nom = 'TYI')
                    AND (seor_ope = ope.atab_code AND ope.atab_nom = 'OPE')
                    $vStatutFacture
                    AND mmat_marqmat NOT like 'z%' AND mmat_marqmat NOT like 'Z%'
                    AND sitv_servcrt IN ('ATE','FOR','GAR','MAN','CSP','MAS', 'LR6')
                    AND (seor_nummat = mmat_nummat)
                    AND slor_constp NOT like '%ZDI%'
                    $vOrvalDw
                    $vligneType

                  
                    $vConditionNoPlanning 
                    $agence
                    $vStatutInterneExterne
                    $agenceDebite
                    $serviceDebite
                    $vDateDMonthPlan
                    $vDateFMonthPlan
                    $vconditionNumParc
                    $vconditionIdMat
                    $vconditionNumOr
                    $vconditionNumSerie
                    $vconditionCasier
                    $vsection 
                    group by 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16
		                order by 1,5  ";      

// dump($statement);
        
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return $resultat;

  }
  
  /**
   * recuperation numOr valide dans DW (demande intervantion)
   */
  public function recuperationNumOrValider($criteria){
  
    if(!empty($criteria->getNumParc())){
      $vconditionNumParc = " AND mmat_recalph = '".$criteria->getNumParc()."'";
    }else{
      $vconditionNumParc = "";
    }
    if(!empty($criteria->getNumSerie())){
      $vconditionNumSerie = " AND mmat_numserie = '".$criteria->getNumSerie()."' ";
    }else{
      $vconditionNumSerie = "";
    }
    if(!empty($criteria->getNumOr())){
      $vconditionNumOr = " AND numero_or ='".$criteria->getNumOr()."'";
    }else{
      $vconditionNumOr = "";
    }
  
    $niveauUrgence = $criteria->getNiveauUrgence();
    if(!empty($niveauUrgence)){
      $idUrgence = $niveauUrgence->getId();
    }
    
    if(!empty($idUrgence)){
        $nivUrg  = "AND id_niveau_urgence = '".$idUrgence."'";
    }else{
      $nivUrg = "";
    }
    // if(!empty($criteria->getServiceDebite())){
    //   $serviceDebite = " AND sitv_servdeb in ('".implode("','",$criteria->getServiceDebite())."')";
    // } else{
    //   $serviceDebite = "";
    // } 
    // if(!empty($criteria->getAgenceDebite())){
    //   $agenceDebite = " AND agence_service_debiteur like  %'".substr($criteria->getAgenceDebite(),-6,2). "' % ";
    // }else{
    //   $agenceDebite = "";
    // }

    $statement = "SELECT 
                  numero_or 
                  FROM demande_intervention
                  WHERE  (date_validation_or is not null  or date_validation_or = '1900-01-01')
                  $vconditionNumOr
                  $nivUrg
                  ";
    //  dump($statement);
    $execQueryNumOr = $this->connexion->query($statement);
    $numOr = array();

    while ($row_num_or = odbc_fetch_array($execQueryNumOr)) {
        $numOr[] = $row_num_or;
    }

    return $numOr;
  }

  public function recupNumeroItv($numOr, $stringItv)
  {
      $statement = " SELECT  
                      COUNT(sitv_interv) as nbItv
                      FROM sav_itv 
                      where sitv_numor='".$numOr."'
                      AND sitv_interv NOT IN ('".$stringItv."')";
      
      $result = $this->connect->executeQuery($statement);

      $data = $this->connect->fetchResults($result);

      return $this->convertirEnUtf8($data);
  }

}