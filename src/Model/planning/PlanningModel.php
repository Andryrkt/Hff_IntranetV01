<?php
namespace App\Model\planning;


use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Controller\Traits\FormatageTrait;


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
                      or ASUC_NUM like '10' 
                      or ASUC_NUM like '20' 
                      or ASUC_NUM like '30'
                       or ASUC_NUM like '40')
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
  public function recuperationMaterielplanifier($criteria)
  {

   $vYearsStatutPlan =  $this->planAnnee($criteria);
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
                      $vYearsStatutPlan as annee,
                      $vMonthStatutPlan as mois,
                      seor_numor ||'-'||sitv_interv as orIntv,

                      (  SELECT SUM(slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv ) as QteCdm,
                    	(  SELECT SUM(slor_qterea ) FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv ) as QtLiv
                      

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
                    AND sitv_servcrt IN ('ATE','FOR','GAR','MAN','CSP','MAS')
                    AND (seor_nummat = mmat_nummat)
                    AND  slor_typlig = 'P'
                    AND slor_constp NOT like '%ZDI%'
                    AND $vYearsStatutPlan = $annee
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
                     group by 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15
		                order by 1,5  ";      
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
       
        $resultat = $this->convertirEnUtf8($data);
        return $resultat;
  }
    


}