<?php

namespace App\Model\planning;


use Exception;
use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Entity\planning\PlanningSearch;
use App\Service\GlobalVariablesService;
use App\Controller\Traits\FormatageTrait;

class PlanningModel extends Model
{
  use ConversionModel;
  use FormatageTrait;
  use PlanningModelTrait;

  public function recuperationAgenceIrium()
  {
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
      array_map(function ($item) {
        return [$item['asuc_num'] . '-' . $item['asuc_lib'] => $item['asuc_num']];
      }, $dataUtf8);
  }


  public function recuperationAnneeplannification()
  {
    $query = " SELECT YEAR(ska_d_start) as Annee
                  FROM ska
                  INNER JOIN skw ON skw.skw_id = ska.skw_id
                  GROUP BY 1
                  ORDER BY YEAR(ska_d_start) DESC           
       ";
    $result = $this->connect->executeQuery($query);
    $data = $this->connect->fetchResults($result);
    $dataUtf8 = $this->convertirEnUtf8($data);
    return array_combine(array_column($dataUtf8, 'annee'), array_column($dataUtf8, 'annee'));
  }
  public function recuperationAgenceDebite()
  {
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
      array_map(function ($item) {
        return $item['asuc_num'];
      }, $dataUtf8)
    );
  }
  public function recuperationSection()
  {

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
      array_map(function ($item) {
        return $item['sec_num'];
      }, $dataUtf8)
    );
  }


  public function recuperationServiceDebite($agence)
  {

    if ($agence === null) {
      $codeAgence = "";
    } else {
      $codeAgence = " AND asuc_num = '" . $agence . "'";
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
    return array_map(function ($item) {
      return [
        "value" => $item['atab_code'],
        "text"  => $item['atab_lib']
      ];
    }, $dataUtf8);
  }
  public function recuperationMaterielplanifier($criteria, string $lesOrValides, string $back, $touslesOrItvSoumis)
  {
    if ($criteria->getOrBackOrder() == true) {
      $vOrvalDw = "AND seor_numor ||'-'||sitv_interv in (" . $back . ") ";
    } else {
      if (!empty($lesOrValides)) {
        if ($criteria->getOrNonValiderDw() == true) {
          $vOrvalDw = "AND seor_numor ||'-'||sitv_interv not in (" . $touslesOrItvSoumis . ") ";
        } else {
          $vOrvalDw = "AND seor_numor ||'-'||sitv_interv in ('" . $lesOrValides . "') ";
        }
      } else {
        $vOrvalDw = " --AND seor_numor ||'-'||sitv_interv in ('')";
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
                    AND seor_soc = 'HF'
                    AND sitv_numor = slor_numor 
                    AND sitv_interv = slor_nogrp/100
                    AND (seor_succ = asuc_num) -- OR mmat_succ = asuc_parc)
                    AND (seor_servcrt = ser.atab_code AND ser.atab_nom = 'SER')
                    AND (sitv_typitv = sec.atab_code AND sec.atab_nom = 'TYI')
                    AND (seor_ope = ope.atab_code AND ope.atab_nom = 'OPE')
                    $vStatutFacture
                 
                    AND (seor_nummat = mmat_nummat)
                   -- AND slor_constp NOT like '%ZDI%'
                    
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


    // dump($statement);
    $result = $this->connect->executeQuery($statement);
    $data = $this->connect->fetchResults($result);
    $resultat = $this->convertirEnUtf8($data);
    return $resultat;
  }
  public function backOrderPlanning($lesOrValides, PlanningSearch $criteria, $tousLesOrSoumis)
  {

    if (!empty($lesOrValides)) {
      if ($criteria->getOrNonValiderDw() == true) {
        $vOrvalDw = "AND slor_numor not in (" . $tousLesOrSoumis . ") ";
      } else {
        $vOrvalDw = "AND slor_numor in ('" . $lesOrValides . "') ";
      }
    } else {
      $vOrvalDw = " AND  slor_numor in ('')";
    }


    $statement = "SELECT distinct 
                   CAST(sav.slor_numor || '-' || trunc(sav.slor_nogrp/100) as varchar(50))AS intervention
                  FROM sav_lor AS sav
                  INNER JOIN gcot_acknow_cat AS cat
                  ON CAST(sav.slor_numcf  as varchar(50))= CAST(cat.numero_po as varchar(50))
                  AND (sav.slor_nolign = cat.line_number OR  sav.slor_noligncm = cat.line_number)
                  AND sav.slor_refp = cat.parts_number
                  WHERE (  CAST(cat.libelle_type as varchar(10))= 'Error'  or CAST(cat.libelle_type as varchar(10))= 'Back Order'  ) 
                  AND cat.id_gcot_acknow_cat = (
                                              SELECT MAX(sub.id_gcot_acknow_cat )
                                              FROM gcot_acknow_cat AS sub
                                              WHERE sub.parts_number = cat.parts_number
                                                AND sub.numero_po = cat.numero_po
                                                AND sub.line_number = cat.line_number
                                          )


                  $vOrvalDw
                  
      ";

    // dump($statement);
    $result = $this->connect->executeQuery($statement);
    $data = $this->connect->fetchResults($result);
    $resultat = $this->convertirEnUtf8($data);

    return array_map(function ($item) {
      return $item['intervention'];
    }, $resultat);
  }

  public function backOrderPlanningnoItv($lesOrValides, PlanningSearch $criteria, $tousLesOrSoumis)
  {

    if (!empty($lesOrValides)) {
      if ($criteria->getOrNonValiderDw() == true) {
        $vOrvalDw = "AND slor_numor not in (" . $tousLesOrSoumis . ") ";
      } else {
        $vOrvalDw = "AND slor_numor in ('" . $lesOrValides . "') ";
      }
    } else {
      $vOrvalDw = " AND  slor_numor in ('')";
    }


    $statement = "SELECT distinct 
                   sav.slor_numor  AS intervention
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

    return array_map(function ($item) {
      return $item['intervention'];
    }, $resultat);
  }

  public function exportExcelPlanning($criteria, $lesOrValides)
  {

    if (!empty($lesOrValides)) {
      $vOrvalDw = "AND seor_numor ||'-'||sitv_interv in ('" . $lesOrValides . "') ";
    } else {
      $vOrvalDw = " AND seor_numor ||'-'||sitv_interv in ('')";
    }
    $vplanification = "'" . $criteria->getPlan() . "'";
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
  public function recuperationDetailPieceInformix($numOrIntv, $criteria)
  {
    $vplan = "'" . $criteria['plan'] . "'";

    if (!empty($criteria['typeligne'])) {
      switch ($criteria['typeligne']) {
        case "TOUTES":
          $vtypeligne = " ";
          break;
        case "PIECES_MAGASIN":
          $vtypeligne = " AND  slor_constp  <> 'LUB'  AND slor_constp not like 'Z%'    AND slor_typlig = 'P'";
          break;
        case "ACHAT_LOCAUX":
          $vtypeligne = " AND slor_constp  = 'ZST'";
          break;
        case "LUBRIFIANTS":
          $vtypeligne = " AND slor_constp = 'LUB'   AND slor_typlig = 'P' ";
          break;
        default:
          $vtypeligne  = "";
          break;
      }
    } else {
      $vtypeligne = "";
    }

    $statement = " SELECT $vplan as plan,
                            slor_numor as numOr,
                            slor_numcf as numCis,
                            sitv_interv as Intv,
                            trim(sitv_comment) as commentaire,
                            --slor_datel as datePlanning,
                            --sitv_datepla as datePlanning,
                            CASE WHEN 


                                   ( SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = slor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) is Null 

 
                                THEN


                                    DATE(sitv_datepla) 


                                ELSE


                                    (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = slor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) 

 
                                END   as datePlanning,
                            trim(slor_constp) as cst,
                            trim(slor_refp) as ref,
                            trim(slor_desi) as desi,
                            slor_qterel AS QteReliquat,
                            CASE 
                              WHEN slor_typlig = 'P' 
                                THEN
                                  (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) 
		                          ELSE 
                                slor_qterea 
	                          	END AS QteRes_Or,
                            slor_qterea AS Qteliv,
                            slor_qteres AS QteAll,
                            
                      CASE  
                        WHEN slor_natcm = 'C' THEN 'COMMANDE'
                        WHEN slor_natcm = 'L' THEN 'RECEPTION'
                      END AS Statut_ctrmq,
                      CASE 
                        WHEN slor_natcm = 'C' THEN 
                          slor_numcf
                        WHEN slor_natcm = 'L' THEN 
                          (SELECT MAX(fllf_numcde) FROM frn_llf WHERE fllf_numliv = slor_numcf
                          AND fllf_ligne = slor_noligncm
                          AND fllf_refp = slor_refp)
                      END  AS numeroCmd,

                      CASE WHEN slor_qteres = (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) AND slor_qterel >0 THEN
                        trim('A LIVRER')
                      WHEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) = slor_qteres AND slor_qterel = 0 AND slor_qterea = 0 THEN
                        trim('DISPO STOCK')
                      WHEN slor_qterea =  (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) THEN
                         trim('LIVRE')
                      WHEN slor_natcm = 'C' THEN
                                ( SELECT libelle_type 
                                  FROM  gcot_acknow_cat 
                                  WHERE CAST( Numero_PO as varchar(10)) = CAST(slor_numcf  as varchar(10)) 
                                  AND Parts_Number = slor_refp  
                                  AND Parts_CST = slor_constp 
                                  AND Line_Number = slor_noligncm 
		   		                        AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat)
                                                             FROM gcot_acknow_cat 
                                                             WHERE CAST( Numero_PO as varchar(10)) = CAST(slor_numcf  as varchar(10))  
                                                             AND Parts_Number = slor_refp  
                                                             AND Parts_CST = slor_constp 
                                                             AND Line_Number = slor_noligncm )
					                    	 )
                      WHEN slor_typcf = 'CIS' THEN
		                            ( SELECT libelle_type 
                                  FROM  gcot_acknow_cat 
                                  WHERE  CAST( Numero_PO as varchar(10)) = CAST(nlig_numcf  as varchar(10))
                                  AND Parts_Number = slor_refp  
                                  AND Parts_CST = slor_constp 
                                  AND (Line_Number = slor_nolign OR Line_Number = nlig_noligncm )
	                                AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat)
                                                             FROM gcot_acknow_cat 
                                                             WHERE  CAST( Numero_PO as varchar(10)) = CAST(nlig_numcf  as varchar(10))
                                                             AND Parts_Number = slor_refp  
                                                             AND Parts_CST = slor_constp 
                                                             AND (Line_Number = slor_nolign OR Line_Number = nlig_noligncm ) )
				                         )
	                    END as Statut,

                    CASE WHEN slor_qteres = (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) AND slor_qterel >0 THEN
                    TO_CHAR((
		                                 SELECT spic_datepic
                                     FROM (
                                        SELECT spic_datepic,
                                         ROW_NUMBER() OVER (ORDER BY spic_datepic ASC) AS rn
                                         FROM sav_pic
                                         WHERE spic_numor = slor_numor
                                        AND spic_refp = slor_refp
                                        AND spic_nolign = slor_nolign
                                           ) AS ranked_dates
                                       WHERE rn = 1
                             ), '%Y-%m-%d')

	                  WHEN slor_qterea = (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) THEN
                  	TO_CHAR((
		                        (SELECT sliv_date 
		                        FROM sav_liv 
                            WHERE sliv_numor = slor_numor 
		                        AND sliv_nolign = slor_nolign)), '%Y-%m-%d')
	                  WHEN slor_natcm = 'C' THEN
 		                    TO_CHAR((	
                                  ( SELECT date_creation
                                    FROM  gcot_acknow_cat 
                                    WHERE CAST( Numero_PO as varchar(10)) = CAST(slor_numcf  as varchar(10)) 
                                    AND Parts_Number = slor_refp  
                                    AND Parts_CST = slor_constp 
                                    AND (Line_Number = slor_noligncm OR Line_Number = slor_nolign)
                                    AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat) 
                                                               FROM gcot_acknow_cat 
                                                               WHERE CAST( Numero_PO as varchar(10)) = CAST(slor_numcf  as varchar(10))  
                                                               AND Parts_Number = slor_refp  
                                                               AND Parts_CST = slor_constp 
                                                               AND (Line_Number = slor_noligncm OR Line_Number = slor_nolign) )
	                        	       )
                                 ), 
                                 '%Y-%m-%d')
                    WHEN slor_typcf = 'CIS' THEN
		                       TO_CHAR((
                                  ( SELECT date_creation
                                    FROM  gcot_acknow_cat 
                                    WHERE  CAST( Numero_PO as varchar(10)) = CAST(nlig_numcf  as varchar(10))
                                    AND Parts_Number = slor_refp  
                                    AND Parts_CST = slor_constp 
                                    AND (Line_Number = slor_nolign OR Line_Number = nlig_noligncm )
                                    AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat) 
                                                               FROM gcot_acknow_cat 
                                                               WHERE  CAST( Numero_PO as varchar(10)) = CAST(nlig_numcf  as varchar(10))
                                                               AND Parts_Number = slor_refp  
                                                               AND Parts_CST = slor_constp 
                                                               AND (Line_Number = slor_nolign OR Line_Number = nlig_noligncm ))
                                    )
                                 ), '%Y-%m-%d')
	                  END AS dateStatut,

                      CASE  WHEN slor_qterea <> (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) THEN
	                     ( SELECT message FROM  gcot_acknow_cat 
                          WHERE CAST( Numero_PO as varchar(10)) = CAST(slor_numcf  as varchar(10)) 
                          AND Parts_Number = slor_refp  
                          AND Parts_CST = slor_constp 
                          AND (Line_Number = slor_noligncm OR Line_Number = slor_nolign)
		   		                AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat) 
                                                      FROM gcot_acknow_cat 
                                                      WHERE CAST( Numero_PO as varchar(10)) = CAST(slor_numcf  as varchar(10))  
                                                      AND Parts_Number = slor_refp  
                                                      AND Parts_CST = slor_constp 
                                                      AND (Line_Number = slor_noligncm OR Line_Number = slor_nolign))
					            	)
                        WHEN slor_typcf = 'CIS' THEN
                                  ( SELECT message FROM  gcot_acknow_cat 
                                            WHERE  CAST( Numero_PO as varchar(10)) = CAST(nlig_numcf  as varchar(10))
                                            AND Parts_Number = slor_refp  
                                            AND Parts_CST = slor_constp 
                                            AND (Line_Number = slor_nolign OR Line_Number = nlig_noligncm )
                                            AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat) 
                                                                         FROM gcot_acknow_cat 
                                                                         WHERE  CAST( Numero_PO as varchar(10)) = CAST(nlig_numcf  as varchar(10))
                                                                         AND Parts_Number = slor_refp  
                                                                         AND Parts_CST = slor_constp 
                                                                         AND (Line_Number = slor_nolign OR Line_Number = nlig_noligncm ) )
                                  )
	                    END as Message ,
                    CASE  
                      WHEN nlig_natcm = 'C' THEN 'COMMANDE'
                      WHEN nlig_natcm = 'L' THEN 'RECEPTION'
                    END AS Statut_ctrmq_cis,
                    
                    CASE
                    WHEN nlig_natcm = 'C' THEN 
                     nlig_numcf   
                    WHEN nlig_natcm = 'L'THEN
                     (SELECT MAX(fllf_numcde) FROM frn_llf WHERE fllf_numliv = nlig_numcf
                          AND fllf_ligne = nlig_noligncm
                          AND fllf_refp = nlig_refp)
                    END as numerocdecis   
                                      

                FROM sav_lor
	              JOIN sav_itv ON slor_numor = sitv_numor AND sitv_interv = slor_nogrp / 100
              LEFT JOIN neg_lig ON slor_numcf = nlig_numcde AND slor_refp = nlig_refp
                WHERE slor_numor || '-' || sitv_interv = '" . $numOrIntv . "'
                AND (slor_refp not like '%-L' and slor_refp not like '%-CTRL')
                
                --AND slor_typlig = 'P'
                $vtypeligne
               -- AND slor_constp NOT LIKE '%ZDI%'
                GROUP BY 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20
               
      ";
    // dump($statement);
    $result = $this->connect->executeQuery($statement);
    $data = $this->connect->fetchResults($result);
    $resultat = $this->convertirEnUtf8($data);
    return $resultat;
  }
  /**
   * eta mag
   */
  public function recuperationEtaMag($numcde, $refp, $cst)
  {
    if ($cst == 'CAT') {
      $cst = 'K230';
    } else {
      $cst = $cst;
    }
    $squery = " SELECT Eta_ivato,
                    Eta_magasin,
                    Est_ship_date
                    FROM Ces_magasin
                    WHERE Cust_ref = '" . $numcde . "'
                    AND Part_no = '" . $refp . "'
                    AND custCode = '" . $cst . "'
        ";
    $sql = $this->connexion04->query($squery);
    $data = array();
    while ($tabType = odbc_fetch_array($sql)) {
      $data[] = $tabType;
    }
    return $data;
  }
  /**
   * Etat partiel piece
   */
  public function recuperationPartiel($numcde, $refp)
  {
    $statement = " SELECT fcdl_solde as solde,
                          fcdl_qte as qte
                  FROM FRN_CDL 
                  WHERE  fcdl_numcde = '$numcde' 
                  AND  fcdl_refp = '$refp'
    ";
    $result = $this->connect->executeQuery($statement);
    $data = $this->connect->fetchResults($result);
    $resultat = $this->convertirEnUtf8($data);
    return $resultat;
  }
  /**
   * qte CIS
   */

  public function recupeQteCISlig($numOr, $itv, $refp)
  {
    $statement = "SELECT 
                  trunc(nvl(nlig_qtecde,0)) as qteorlig,
                  trunc(nvl(nlig_qtealiv,0) )as qtealllig,
                  trunc(nvl((nlig_qtecde - nlig_qtealiv - nlig_qteliv) ,0))as qtereliquatlig,
                  trunc(nvl(nlig_qteliv,0)) as qtelivlig
                  
                  from sav_lor 

                  inner join neg_lig on 
                      nlig_soc = slor_soc 
                      
                  and nlig_succd = slor_succ
                      
                  and nlig_numcde = slor_numcf
                      
                  and nlig_constp = slor_constp
                      
                  and nlig_refp = slor_refp

                  where nlig_natop = 'CIS'

                  and slor_numor  ='" . $numOr . "'
                  and trunc(slor_nogrp/100) = '" . $itv . "'
                  and slor_refp ='" . $refp . "'
        ";
    // dump($statement);
    $result = $this->connect->executeQuery($statement);
    $data = $this->connect->fetchResults($result);
    $resultat = $this->convertirEnUtf8($data);
    return $resultat;
  }
  /**
   * gcot ORD
   */
  public function recuperationinfodGcot($numcde)
  {
    $statement = "SELECT Code_Statut  as Ord
					FROM  GCOT_Statut_Dossier 
					WHERE  Numero_Dossier = '$numcde'
					AND Code_Statut = 'ORD' ";
    $sql = $this->connexion04Gcot->query($statement);
    $data = odbc_fetch_array($sql);
    return $data;
  }

  /**
   * recuperation numOr valide dans DW (demande intervantion)
   */
  public function recuperationNumOrValider($criteria)
  {
    if (!empty($criteria->getNumOr())) {
      $vconditionNumOr = " AND numero_or ='" . $criteria->getNumOr() . "'";
    } else {
      $vconditionNumOr = "";
    }
    if (!empty($criteria->getTypeDocument())) {
      $vconditionTypeDoc = " AND type_document ='" . $criteria->getTypeDocument()->getId() . "'";
    } else {
      $vconditionTypeDoc = "";
    }
    if (!empty($criteria->getReparationRealise())) {
      $vconditionReparationPar = " AND reparation_realise ='" . $criteria->getReparationRealise() . "'";
    } else {
      $vconditionReparationPar = "";
    }

    $niveauUrgence = $criteria->getNiveauUrgence();
    if (!empty($niveauUrgence)) {
      $idUrgence = $niveauUrgence->getId();
    }

    if (!empty($idUrgence)) {
      $nivUrg  = " AND id_niveau_urgence = '" . $idUrgence . "'";
    } else {
      $nivUrg = "";
    }


    $sql = "SELECT distinct CONCAT(numeroOR,'-',numeroItv) as numero_complet , numeroOR as numero_or
                        from ors_soumis_a_validation o
                        inner join demande_intervention d on d.numero_or = o.numeroOR
                        where numeroversion = (select max(numeroversion) from ors_soumis_a_validation oo 
                        where oo.numeroOR = o.numeroOR) 
                        and o.statut like 'Valid%'
                  $vconditionTypeDoc
                  $vconditionReparationPar
                  $vconditionNumOr
                  $nivUrg
            ORDER  BY  numeroOR ASC
                  ";
    // dd($sql);
    $execQueryNumOr = $this->connexion->query($sql);

    if (!$execQueryNumOr) {
      $error = odbc_errormsg($this->connexion);
      throw new Exception("Erreur ODBC: " . $error);
    }


    $numOr = [];
    $numORTouCourt = [];
    while ($row_num_or = odbc_fetch_array($execQueryNumOr)) {
      $numOr[] = $row_num_or['numero_complet'];
      $numORTouCourt[] = $row_num_or['numero_or'];
    }


    return [$numOr, $numORTouCourt];
  }


  public function recupNumeroItv($numOr, $stringItv)
  {
    $statement = " SELECT  
                      COUNT(sitv_interv) as nbItv
                      FROM sav_itv 
                      where sitv_numor='" . $numOr . "'
                      AND sitv_interv NOT IN ('" . $stringItv . "')";

    $result = $this->connect->executeQuery($statement);

    $data = $this->connect->fetchResults($result);

    return $this->convertirEnUtf8($data);
  }

  public function recupTechnicientIntervenant($numOr, $numItv)
  {
    $statement = " SELECT distinct 
        --skr_id as numero_tech,
        ssal_numsal AS matricule, 
        ssal_nom AS matriculeNomPrenom
        --ofh_id as numero_or, 
        --ofs_id as numero_intervention
        from skw
        inner join ska on ska.skw_id = skw.skw_id
        inner join sav_sal on sav_sal.ssal_numsal = ska.skr_id
        and ofs_id = '" . $numItv . "'
        where skw.ofh_id ='" . $numOr . "'
      ";

    $result = $this->connect->executeQuery($statement);

    $data = $this->connect->fetchResults($result);

    return $this->convertirEnUtf8($data);
  }

  public function recupTechnicien2($numOr, $numItv)
  {
    $statement = " SELECT
        ssal_numsal AS matricule, 
        ssal_nom AS matriculeNomPrenom 
        --sitv_numor 
        from sav_itv
        inner join sav_sal on sav_sal.ssal_numsal = sitv_techn
        where sitv_numor = '" . $numOr . "'
        and sitv_interv = '" . $numItv . "' 
        and ssal_numsal <> 9999
      ";

    $result = $this->connect->executeQuery($statement);

    $data = $this->connect->fetchResults($result);

    return $this->convertirEnUtf8($data);
  }

  public function recupOrcis($numOritv)
  {
    // $statement = "SELECT  DISTINCT 
    //       nlig_natop from sav_lor 
    //       inner join neg_lig on 
    //       nlig_soc = slor_soc 
    //       and nlig_succd = slor_succ
    //       and nlig_numcde = slor_numcf
    //       and nlig_constp = slor_constp
    //       and nlig_refp = slor_refp
    //       where nlig_natop = 'CIS'
    //       and slor_succ  <> '01'
    //       and  slor_numor  || '-' || trunc(slor_nogrp/100) = '".$numOritv."'
    //  ";
    $statement = "SELECT  decode(seor_succ,'01','','60','','80','','CIS') as succ
            from sav_lor, sav_eor
            where slor_succ = seor_succ
            and slor_numor = seor_numor
            and  slor_numor  || '-' || trunc(slor_nogrp/100) = '" . $numOritv . "'
                     ";
    $result = $this->connect->executeQuery($statement);
    $data = $this->connect->fetchResults($result);
    $resultat = $this->convertirEnUtf8($data);
    return $resultat;
  }

  public function dateLivraisonCIS($numCIS, $refp, $cst)
  {
    $statement = "SELECT  max(nliv_datexp) as datelivlig
                  from neg_liv, neg_llf 
                  where nliv_soc = nllf_soc
                  and nliv_numcde = '" . $numCIS . "'
                  and nliv_numliv = nllf_numliv
                  and nllf_constp = '" . $cst . "'
                  and nllf_refp = '" . $refp . "'
                 ";
    $result = $this->connect->executeQuery($statement);
    $data = $this->connect->fetchResults($result);
    $resultat = $this->convertirEnUtf8($data);
    return $resultat;
  }

  public function dateAllocationCIS($numCIS, $refp, $cst)
  {
    $statement = " SELECT  max(npic_date) as datealllig
                  from neg_pic, neg_pil
                  where npic_soc = npil_soc
                  and npic_numcde = npil_numcde
                  and  npic_numcde = '" . $numCIS . "'
                  and npil_constp = '" . $cst . "'
                  and npil_refp = '" . $refp . "'
                ";
    $result = $this->connect->executeQuery($statement);
    $data = $this->connect->fetchResults($result);
    $resultat = $this->convertirEnUtf8($data);
    return $resultat;
  }

  /**
   * liste planning
   */
  public function recupMatListeTous($criteria, string $lesOrValides, string $back, $touslesOrSoumis)
  {
    if ($criteria->getOrBackOrder() == true) {
      // $vOrvalDw = "AND seor_numor in (" . $back . ") ";
      $vOrvalDw = "AND seor_numor ||'-'||sitv_interv in (" . $back . ") ";
    } else {
      if (!empty($lesOrValides)) {
        if ($criteria->getOrNonValiderDw() == true) {
          // $vOrvalDw = "AND seor_numor  in (" . $touslesOrSoumis . ") ";
          $vOrvalDw = "AND seor_numor ||'-'||sitv_interv in ('" . $touslesOrSoumis . "') ";
        } else {
          // $vOrvalDw = "AND seor_numor in ('" . $lesOrValides . "') ";
          $vOrvalDw = "AND seor_numor ||'-'||sitv_interv in ('" . $lesOrValides . "') ";
        }
      } else {
        // $vOrvalDw = " AND seor_numor in ('')";
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
                 ( SELECT SUM( CASE WHEN slor_typlig = 'P' $vligneType  THEN
                  ROUND(slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
                   ELSE ROUND(slor_qterea) END )
                  FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv  $vligneType ) as QteCdm,
                (  SELECT SUM(ROUND(slor_qterea) ) FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv  $vligneType ) as QtLiv,
                (  SELECT SUM(ROUND(slor_qteres ))FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv   $vligneType ) as QteALL,
                sitv_interv as Itv,
                seor_numor as numOR,
CASE 
    WHEN (
        SELECT SUM(
            CASE  
                WHEN A.slor_typlig = 'P' 
                THEN A.slor_qterel + A.slor_qterea + A.slor_qteres + A.slor_qtewait - A.slor_qrec 
                ELSE A.slor_qterea  
            END
        )
        FROM sav_lor AS A
        INNER JOIN sav_itv AS B ON A.slor_numor = B.sitv_numor 
                               AND B.sitv_interv = A.slor_nogrp / 100 
        WHERE A.slor_numor = C.slor_numor AND B.sitv_interv = D.sitv_interv
    ) = (
        SELECT SUM(A.slor_qterea)
        FROM sav_lor AS A
        INNER JOIN sav_itv AS B ON A.slor_numor = B.sitv_numor 
                               AND B.sitv_interv = A.slor_nogrp / 100 
        WHERE A.slor_numor = C.slor_numor AND B.sitv_interv = D.sitv_interv
    ) 
    THEN TRIM('TOUT LIVRE')

    WHEN (
        SELECT SUM(A.slor_qterea)
        FROM sav_lor AS A
        INNER JOIN sav_itv AS B ON A.slor_numor = B.sitv_numor 
                               AND B.sitv_interv = A.slor_nogrp / 100 
        WHERE A.slor_numor = C.slor_numor AND B.sitv_interv = D.sitv_interv
    ) > 0 
    AND (
        SELECT SUM(A.slor_qterea)
        FROM sav_lor AS A
        INNER JOIN sav_itv AS B ON A.slor_numor = B.sitv_numor 
                               AND B.sitv_interv = A.slor_nogrp / 100 
        WHERE A.slor_numor = C.slor_numor AND B.sitv_interv = D.sitv_interv
    ) != (
        SELECT SUM(
            CASE  
                WHEN A.slor_typlig = 'P' 
                THEN A.slor_qterel + A.slor_qterea + A.slor_qteres + A.slor_qtewait - A.slor_qrec 
                ELSE A.slor_qterea 
            END
        )
        FROM sav_lor AS A
        INNER JOIN sav_itv AS B ON A.slor_numor = B.sitv_numor 
                               AND B.sitv_interv = A.slor_nogrp / 100 
        WHERE A.slor_numor = C.slor_numor AND B.sitv_interv = D.sitv_interv
    ) 
    THEN TRIM('PARTIELLEMENT LIVRE')

    WHEN (
        SELECT SUM(
            CASE  
                WHEN A.slor_typlig = 'P' 
                THEN A.slor_qterel + A.slor_qterea + A.slor_qteres + A.slor_qtewait - A.slor_qrec 
                ELSE A.slor_qterea 
            END
        )
        FROM sav_lor AS A
        INNER JOIN sav_itv AS B ON A.slor_numor = B.sitv_numor 
                               AND B.sitv_interv = A.slor_nogrp / 100 
        WHERE A.slor_numor = C.slor_numor
    ) != (
        SELECT SUM(A.slor_qteres)
        FROM sav_lor AS A
        INNER JOIN sav_itv AS B ON A.slor_numor = B.sitv_numor 
                               AND B.sitv_interv = A.slor_nogrp / 100 
        WHERE A.slor_numor = C.slor_numor AND B.sitv_interv = D.sitv_interv
    ) 
    THEN TRIM('PARTIELLEMENT DISPO')

    WHEN (
        SELECT SUM(
            CASE  
                WHEN A.slor_typlig = 'P' 
                THEN A.slor_qterel + A.slor_qterea + A.slor_qteres + A.slor_qtewait - A.slor_qrec 
                ELSE A.slor_qterea 
            END
        )
        FROM sav_lor AS A
        INNER JOIN sav_itv AS B ON A.slor_numor = B.sitv_numor 
                               AND B.sitv_interv = A.slor_nogrp / 100 
        WHERE A.slor_numor = C.slor_numor AND B.sitv_interv = D.sitv_interv
    ) = (
        SELECT SUM(A.slor_qteres)
        FROM sav_lor AS A
        INNER JOIN sav_itv AS B ON A.slor_numor = B.sitv_numor 
                               AND B.sitv_interv = A.slor_nogrp / 100 
        WHERE A.slor_numor = C.slor_numor AND B.sitv_interv = D.sitv_interv
    ) 
    THEN TRIM('COMPLET NON LIVRE')

    ELSE ''
END AS Status_B
,
                      --ligne
                     
                        sitv_datepla as datePlanning,
                        trim(slor_constp) as cst,
                        trim(slor_refp) as ref,
                        trim(slor_desi) as desi,
                        ROUND(slor_qterel) AS QteReliquat,
                        CASE 
                          WHEN slor_typlig = 'P' THEN
                            ROUND( (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) )
		                      ELSE 
                           ROUND( slor_qterea )
                        END AS QteRes_Or,
                        ROUND(slor_qterea) AS Qteliv,
                        ROUND(slor_qteres) AS QteAll,
                        CASE  
                          WHEN slor_natcm = 'C' THEN 'COMMANDE'
                          WHEN slor_natcm = 'L' THEN 'RECEPTION'
                        END AS Statut_ctrmq,
                        CASE 
                          WHEN slor_natcm = 'C' THEN 
                            slor_numcf
                          WHEN slor_natcm = 'L' THEN 
                            (SELECT MAX(fllf_numcde) FROM frn_llf WHERE fllf_numliv = slor_numcf
                              AND fllf_ligne = slor_noligncm
                              AND fllf_refp = slor_refp)
                        END  AS numeroCmd,
                        CASE 
                            WHEN slor_qteres = (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) AND slor_qterel >0 THEN
                             trim('A LIVRER')
                            WHEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) = slor_qteres AND slor_qterel = 0 AND slor_qterea = 0 THEN
                             trim('DISPO STOCK')
                            WHEN slor_qterea =  (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) THEN
                             trim('LIVRE')
                            WHEN slor_natcm = 'C' THEN 
                                ( SELECT libelle_type 
                                  FROM  gcot_acknow_cat 
                                  WHERE CAST( Numero_PO as varchar(10)) = CAST(slor_numcf  as varchar(10))
                                  AND Parts_Number = slor_refp  
                                  AND Parts_CST = slor_constp 
                                  AND Line_Number = slor_noligncm 
		   		                        AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat)
                                                             FROM gcot_acknow_cat 
                                                             WHERE CAST( Numero_PO as varchar(10)) = CAST(slor_numcf  as varchar(10)) 
                                                             AND Parts_Number = slor_refp  
                                                             AND Parts_CST = slor_constp 
                                                             AND Line_Number = slor_noligncm )
					                    	 )
                            WHEN slor_typcf = 'CIS' THEN 
		                            ( SELECT libelle_type 
                                  FROM  gcot_acknow_cat 
                                  WHERE  CAST( Numero_PO as varchar(10)) = CAST(nlig_numcf  as varchar(10))
                                  AND Parts_Number = slor_refp  
                                  AND Parts_CST = slor_constp 
                                  AND (Line_Number = slor_nolign OR Line_Number = nlig_noligncm )
	                                AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat)
                                                             FROM gcot_acknow_cat 
                                                             WHERE  CAST( Numero_PO as varchar(10)) = CAST(nlig_numcf  as varchar(10))
                                                             AND Parts_Number = slor_refp  
                                                             AND Parts_CST = slor_constp 
                                                             AND (Line_Number = slor_nolign OR Line_Number = nlig_noligncm ) )
				                         )
	                      END as Statut,

                        CASE 
                          WHEN slor_qteres = (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) AND slor_qterel >0 THEN
                              TO_CHAR((
		                                 SELECT spic_datepic
                                     FROM (
                                        SELECT spic_datepic,
                                         ROW_NUMBER() OVER (ORDER BY spic_datepic ASC) AS rn
                                         FROM sav_pic
                                         WHERE spic_numor = slor_numor
                                        AND spic_refp = slor_refp
                                        AND spic_nolign = slor_nolign
                                           ) AS ranked_dates
                                       WHERE rn = 1
                             ), '%Y-%m-%d')
	                        WHEN slor_qterea = (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) THEN
                  	          TO_CHAR((
                                  (SELECT sliv_date 
                                  FROM sav_liv 
                                  WHERE sliv_numor = slor_numor 
                                  AND sliv_nolign = slor_nolign)), '%Y-%m-%d')
	                        WHEN slor_natcm = 'C' THEN 
                                TO_CHAR((	
                                          ( SELECT date_creation
                                            FROM  gcot_acknow_cat 
                                            WHERE CAST( Numero_PO as varchar(10)) = CAST(slor_numcf  as varchar(10))
                                            AND Parts_Number = slor_refp  
                                            AND Parts_CST = slor_constp 
                                            AND (Line_Number = slor_noligncm OR Line_Number = slor_nolign)
                                            AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat) 
                                                                      FROM gcot_acknow_cat 
                                                                      WHERE CAST( Numero_PO as varchar(10)) = CAST(slor_numcf  as varchar(10)) 
                                                                      AND Parts_Number = slor_refp  
                                                                      AND Parts_CST = slor_constp 
                                                                      AND (Line_Number = slor_noligncm OR Line_Number = slor_nolign) )
                                          )
                                        ), 
                                        '%Y-%m-%d')
                          WHEN slor_typcf = 'CIS' THEN 
                            TO_CHAR((
                                    ( SELECT date_creation
                                      FROM  gcot_acknow_cat 
                                      WHERE  CAST( Numero_PO as varchar(10)) = CAST(nlig_numcf  as varchar(10))
                                      AND Parts_Number = slor_refp  
                                      AND Parts_CST = slor_constp 
                                      AND (Line_Number = slor_nolign OR Line_Number = nlig_noligncm )
                                      AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat) 
                                                                FROM gcot_acknow_cat 
                                                                WHERE  CAST( Numero_PO as varchar(10)) = CAST(nlig_numcf  as varchar(10))
                                                                AND Parts_Number = slor_refp  
                                                                AND Parts_CST = slor_constp 
                                                                AND (Line_Number = slor_nolign OR Line_Number = nlig_noligncm ))
                                      )
                                  ), '%Y-%m-%d')
	                      END AS dateStatut,
                        CASE  
                            WHEN slor_qterea <> (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) THEN 
                                  ( SELECT message FROM  gcot_acknow_cat 
                                  WHERE CAST( Numero_PO as varchar(10)) = CAST(slor_numcf  as varchar(10))
                                  AND Parts_Number = slor_refp  
                                  AND Parts_CST = slor_constp 
                                  AND (Line_Number = slor_noligncm OR Line_Number = slor_nolign)
                                  AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat) 
                                                              FROM gcot_acknow_cat 
                                                              WHERE CAST( Numero_PO as varchar(10)) = CAST(slor_numcf  as varchar(10)) 
                                                              AND Parts_Number = slor_refp  
                                                              AND Parts_CST = slor_constp 
                                                              AND (Line_Number = slor_noligncm OR Line_Number = slor_nolign))
                                ) 
                            WHEN slor_typcf = 'CIS' THEN 
                                  ( SELECT message FROM  gcot_acknow_cat 
                                            WHERE  CAST( Numero_PO as varchar(10)) = CAST(nlig_numcf  as varchar(10))
                                            AND Parts_Number = slor_refp  
                                            AND Parts_CST = slor_constp 
                                            AND (Line_Number = slor_nolign OR Line_Number = nlig_noligncm )
                                            AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat) 
                                                                         FROM gcot_acknow_cat 
                                                                         WHERE  CAST( Numero_PO as varchar(10)) = CAST(nlig_numcf  as varchar(10))
                                                                         AND Parts_Number = slor_refp  
                                                                         AND Parts_CST = slor_constp 
                                                                         AND (Line_Number = slor_nolign OR Line_Number = nlig_noligncm ) )
                                  )
	                       END as Message ,
                        slor_numcf as numCis,
                        CASE  
                          WHEN nlig_natcm = 'C' THEN 'COMMANDE'
                          WHEN nlig_natcm = 'L' THEN 'RECEPTION'
                        END AS Statut_ctrmq_cis,
                        CASE
                          WHEN nlig_natcm = 'C' THEN 
                          nlig_numcf   
                          WHEN nlig_natcm = 'L'THEN
                          (SELECT MAX(fllf_numcde) FROM frn_llf WHERE fllf_numliv = nlig_numcf
                            AND fllf_ligne = nlig_noligncm
                            AND fllf_refp = nlig_refp)
                        END as numerocdecis

      FROM  sav_eor,sav_lor as C , sav_itv as D, agr_succ, agr_tab ser, mat_mat, agr_tab ope, outer agr_tab sec, outer neg_lig
      WHERE seor_numor = slor_numor
      AND seor_soc = 'HF'
      AND seor_serv <> 'DEV'
      AND seor_soc = 'HF'
      AND sitv_numor = slor_numor 
      AND sitv_interv = slor_nogrp/100 
      AND (seor_succ = asuc_num)
      AND (seor_servcrt = ser.atab_code AND ser.atab_nom = 'SER')
      AND (sitv_typitv = sec.atab_code AND sec.atab_nom = 'TYI')
      AND (seor_ope = ope.atab_code AND ope.atab_nom = 'OPE')     
      $vStatutFacture     
      AND mmat_marqmat NOT like 'z%' AND mmat_marqmat NOT like 'Z%'
      AND sitv_servcrt IN ('ATE','FOR','GAR','MAN','CSP','MAS', 'LR6', 'LST')
      AND (seor_nummat = mmat_nummat)
      --AND slor_constp NOT like '%ZDI%'
      --ligne
      AND slor_numcf = nlig_numcde AND slor_refp = nlig_refp
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
      group by 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36
      order by 10,14 
  ";
    //  dd($statement);
    $result = $this->connect->executeQuery($statement);
    $data = $this->connect->fetchResults($result);
    $resultat = $this->convertirEnUtf8($data);
    return $resultat;
  }
  public function recupMatListeTousCount($criteria, string $lesOrValides, string $back, $touslesOrSoumis)
  {
    if ($criteria->getOrBackOrder() == true) {
      // $vOrvalDw = "AND seor_numor in (" . $back . ") ";
      $vOrvalDw = "AND seor_numor ||'-'||sitv_interv in (" . $back . ") ";
    } else {
      if (!empty($lesOrValides)) {
        if ($criteria->getOrNonValiderDw() == true) {
          // $vOrvalDw = "AND seor_numor  in (" . $touslesOrSoumis . ") ";
          $vOrvalDw = "AND seor_numor ||'-'||sitv_interv in ('" . $touslesOrSoumis . "') ";
        } else {
          // $vOrvalDw = "AND seor_numor in ('" . $lesOrValides . "') ";
          $vOrvalDw = "AND seor_numor ||'-'||sitv_interv in ('" . $lesOrValides . "') ";
        }
      } else {
        // $vOrvalDw = " AND seor_numor in ('')";
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
                COUNT( distinct seor_numor ||'-'||sitv_interv )  as nb_numOR,
                COUNT( sitv_interv ) as nb_itv,
                COUNT ( slor_constp) as nb_ligne

      FROM  sav_eor,sav_lor as C , sav_itv as D, agr_succ, agr_tab ser, mat_mat, agr_tab ope, outer agr_tab sec, outer neg_lig
      WHERE seor_numor = slor_numor
      AND seor_soc = 'HF'
      AND seor_serv <> 'DEV'
      AND seor_soc = 'HF'
      AND sitv_numor = slor_numor 
      AND sitv_interv = slor_nogrp/100 
      AND (seor_succ = asuc_num)
      AND (seor_servcrt = ser.atab_code AND ser.atab_nom = 'SER')
      AND (sitv_typitv = sec.atab_code AND sec.atab_nom = 'TYI')
      AND (seor_ope = ope.atab_code AND ope.atab_nom = 'OPE')     
      $vStatutFacture     
      AND mmat_marqmat NOT like 'z%' AND mmat_marqmat NOT like 'Z%'
      AND sitv_servcrt IN ('ATE','FOR','GAR','MAN','CSP','MAS', 'LR6', 'LST')
      AND (seor_nummat = mmat_nummat)
      --AND slor_constp NOT like '%ZDI%'
      --ligne
      AND slor_numcf = nlig_numcde AND slor_refp = nlig_refp
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
     
  ";
    //  dd($statement);
    $result = $this->connect->executeQuery($statement);
    $data = $this->connect->fetchResults($result);
    $resultat = $this->convertirEnUtf8($data);
    return $resultat;
  }

  public function recuperationMaterielplanifierListe($criteria, string $lesOrValides, string $back, $page, $limit, $export = false)
  {
    if ($criteria->getOrBackOrder() == true) {
      $vOrvalDw = "AND seor_numor in (" . $back . ") ";
      // $vOrvalDw = "AND seor_numor ||'-'||sitv_interv in (".$back.") ";
    } else {
      if (!empty($lesOrValides)) {
        $vOrvalDw = "AND seor_numor in ('" . $lesOrValides . "') ";
        // $vOrvalDw = "AND seor_numor ||'-'||sitv_interv in ('".$lesOrValides."') ";
      } else {
        $vOrvalDw = " AND seor_numor in ('')";
        // $vOrvalDw = " AND seor_numor ||'-'||sitv_interv in ('')";
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

    $offset = ($page - 1) * $limit;

    $conditionPagination = $export === false ? "SKIP $offset FIRST $limit" : "";

    $statement = " SELECT $conditionPagination
                      
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
                      (  SELECT SUM(slor_qteres )FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv   $vligneType ) as QteALL,
                      sitv_interv as Itv,
                      seor_numor as numOR,

   CASE  
                      WHEN  (  SELECT SUM( CASE WHEN slor_typlig = 'P'    THEN
                                        slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec
                                          ELSE slor_qterea END )
                        FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv    ) 
		=  
		(  SELECT SUM(slor_qterea ) FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv    ) THEN
                      TRIM('TOUT LIVRE') 
		
	       WHEN  (  SELECT SUM(slor_qterea ) FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv    ) > 0 
AND   (  SELECT SUM(slor_qterea ) FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv    ) !=   (  SELECT SUM( CASE WHEN slor_typlig = 'P'    THEN


                                                slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec


                                          ELSE slor_qterea END )


                        FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv    )
AND   (  SELECT SUM( CASE WHEN slor_typlig = 'P'    THEN


                                                slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec


                                          ELSE slor_qterea END )


                        FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv    ) > ( (  SELECT SUM(slor_qterea ) FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv    ) +   (  SELECT SUM(slor_qteres )FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv     ) 
) THEN
		TRIM('PARTIELLEMENT LIVRE')
WHEN   (  SELECT SUM( CASE WHEN slor_typlig = 'P'    THEN


                                                slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec


                                          ELSE slor_qterea END )


                        FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv    ) !=   (  SELECT SUM(slor_qteres )FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv     ) 
AND  (  SELECT SUM(slor_qterea ) FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv    ) = 0 
AND  (  SELECT SUM(slor_qteres )FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv     ) >0  THEN
TRIM('PARTIELLEMENT DISPO')

WHEN  (  (  SELECT SUM( CASE WHEN slor_typlig = 'P'    THEN


                                                slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec


                                          ELSE slor_qterea END )


                        FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv    ) =  (  SELECT SUM(slor_qteres )FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv     ) 
AND  (  SELECT SUM(slor_qterea ) FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv    ) <   (  SELECT SUM( CASE WHEN slor_typlig = 'P'    THEN


                                                slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec


                                          ELSE slor_qterea END )


                        FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv    )  )
OR(  (  SELECT SUM(slor_qteres )FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv     ) > 0 

AND   (  SELECT SUM( CASE WHEN slor_typlig = 'P'    THEN


                                                slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec


                                          ELSE slor_qterea END )


                        FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv    ) = (  (  SELECT SUM(slor_qteres )FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv     ) +  (  SELECT SUM(slor_qterea ) FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv    )   )) THEN
TRIM('COMPLET NON LIVRE')
                      END  AS Status_B

                    FROM  sav_eor,sav_lor as C , sav_itv as D, agr_succ, agr_tab ser, mat_mat, agr_tab ope, outer agr_tab sec
                    WHERE seor_numor = slor_numor
                    AND (slor_refp not like '%-L' and slor_refp not like '%-CTRL')
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
                    group by 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20
		                order by 10,14  ";


    // dd($statement);
    $result = $this->connect->executeQuery($statement);
    $data = $this->connect->fetchResults($result);
    $resultat = $this->convertirEnUtf8($data);
    return $resultat;
  }

  /**
   * nombre total de matériel planifié
   */
  public function recuperationNombreMaterielplanifier($criteria, string $lesOrValides, string $back)
  {
    if ($criteria->getOrBackOrder() == true) {
      $vOrvalDw = "AND seor_numor in (" . $back . ") ";
      // $vOrvalDw = "AND seor_numor ||'-'||sitv_interv in (".$back.") ";
    } else {
      if (!empty($lesOrValides)) {
        $vOrvalDw = "AND seor_numor in ('" . $lesOrValides . "') ";
        // $vOrvalDw = "AND seor_numor ||'-'||sitv_interv in ('".$lesOrValides."') ";
      } else {
        $vOrvalDw = " AND seor_numor in ('')";
        // $vOrvalDw = " AND seor_numor ||'-'||sitv_interv in ('')";
      }
    }


    $vligneType = $this->typeLigne($criteria);

    $vConditionNoPlanning = $this->nonplannfierSansDatePla($criteria);
    $vDateDMonthPlan = $this->dateDebutMonthPlan($criteria);
    $vDateFMonthPlan = $this->dateFinMonthPlan($criteria);
    $vStatutFacture = $this->facture($criteria);
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

    $statement = " SELECT COUNT(*) as total FROM  ( SELECT 


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


                       CASE WHEN 


                    YEAR ( (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) ) is Null 

 
                THEN


                    YEAR(DATE(sitv_datepla)  )


                ELSE


                    YEAR ( (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) )


                END  as annee,


                       CASE WHEN 


                                    MONTH ( (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) ) is Null 

 
                                THEN


                                    MONTH(DATE(sitv_datepla)  )


                                ELSE


                                    MONTH ( (SELECT DATE(Min(ska_d_start) ) FROM ska, skw WHERE ofh_id = seor_numor AND ofs_id=sitv_interv AND skw.skw_id = ska.skw_id ) )

 
                                END   as mois,


                      seor_numor ||'-'||sitv_interv as orIntv,



                      (  SELECT SUM( CASE WHEN slor_typlig = 'P'    THEN


                                                slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec


                                          ELSE slor_qterea END )


                        FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv    ) as QteCdm,

 
                    (  SELECT SUM(slor_qterea ) FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv    ) as QtLiv,

 
                      (  SELECT SUM(slor_qteres )FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv     ) as QteALL,
                      

 
                      sitv_interv as Itv,


                      seor_numor as numOR
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
                    group by codeSuc ,libSuc,codeServ,libServ,commentaire,idMat,markMat,typeMat,numSerie,numParc,casier,annee,mois,orIntv,QteCdm,QtLiv,QteALL,Itv,numOR)";


    $result = $this->connect->executeQuery($statement);
    $data = $this->connect->fetchResults($result);
    // dump($statement);
    // Retourne le nombre total d'éléments
    return (int) $data[0]['total'];
  }

  public function recuperationDetailPieceInformixListe($numOr, $criteria, $itv)
  {
    $vplan = "'" . $criteria['plan'] . "'";

    if (!empty($criteria['typeligne'])) {
      switch ($criteria['typeligne']) {
        case "TOUTES":
          $vtypeligne = " ";
          break;
        case "PIECES_MAGASIN":
          $vtypeligne = " AND slor_constp in (" . GlobalVariablesService::get('pieces_magasin') . ") AND slor_typlig = 'P' AND (slor_refp not like '%-L' and slor_refp not like '%-CTRL')";
          break;
        case "ACHAT_LOCAUX":
          $vtypeligne = " AND slor_constp in (" . GlobalVariablesService::get('achat_locaux') . ")";
          break;
        case "LUBRIFIANTS":
          $vtypeligne = " AND slor_constp in (" . GlobalVariablesService::get('lub') . ")  AND slor_typlig = 'P'";
          break;
        default:
          $vtypeligne = " ";
          break;
      }
    } else {
      $vtypeligne = "";
    }


    $statement = " SELECT  $vplan as plan,
                            slor_numor as numOr,
                            slor_numcf as numCis,
                            sitv_interv as Intv,
                            trim(sitv_comment) as commentaire,
                            sitv_datepla as datePlanning,
                            trim(slor_constp) as cst,
                            trim(slor_refp) as ref,
                            trim(slor_desi) as desi,
                            slor_qterel AS QteReliquat,
                            CASE 
                              WHEN slor_typlig = 'P' 
                                THEN
                                  (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) 
		                          ELSE 
                                slor_qterea 
	                          	END AS QteRes_Or,
                            slor_qterea AS Qteliv,
                            slor_qteres AS QteAll,
                            
                      CASE  
                        WHEN slor_natcm = 'C' THEN 'COMMANDE'
                        WHEN slor_natcm = 'L' THEN 'RECEPTION'
                      END AS Statut_ctrmq,
                      CASE 
                        WHEN slor_natcm = 'C' THEN 
                          slor_numcf
                        WHEN slor_natcm = 'L' THEN 
                          (SELECT MAX(fllf_numcde) FROM frn_llf WHERE fllf_numliv = slor_numcf
                          AND fllf_ligne = slor_noligncm
                          AND fllf_refp = slor_refp)
                      END  AS numeroCmd,

                      CASE WHEN slor_qteres = (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) AND slor_qterel >0 THEN
                        trim('A LIVRER')
                      WHEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) = slor_qteres AND slor_qterel = 0 AND slor_qterea = 0 THEN
                        trim('DISPO STOCK')
                      WHEN slor_qterea =  (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) THEN
                         trim('LIVRE')
                      WHEN slor_natcm = 'C' THEN 
                                ( SELECT libelle_type 
                                  FROM  gcot_acknow_cat 
                                  WHERE CAST( Numero_PO as varchar(10)) = CAST(slor_numcf  as varchar(10))
                                  AND Parts_Number = slor_refp  
                                  AND Parts_CST = slor_constp 
                                  AND Line_Number = slor_noligncm 
		   		                        AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat)
                                                             FROM gcot_acknow_cat 
                                                             WHERE CAST( Numero_PO as varchar(10)) = CAST(slor_numcf  as varchar(10)) 
                                                             AND Parts_Number = slor_refp  
                                                             AND Parts_CST = slor_constp 
                                                             AND Line_Number = slor_noligncm )
					                    	 )
                      WHEN slor_typcf = 'CIS' THEN 
		                            ( SELECT libelle_type 
                                  FROM  gcot_acknow_cat 
                                  WHERE  CAST( Numero_PO as varchar(10)) = CAST(nlig_numcf  as varchar(10))
                                  AND Parts_Number = slor_refp  
                                  AND Parts_CST = slor_constp 
                                  AND (Line_Number = slor_nolign OR Line_Number = nlig_noligncm )
	                                AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat)
                                                             FROM gcot_acknow_cat 
                                                             WHERE  CAST( Numero_PO as varchar(10)) = CAST(nlig_numcf  as varchar(10))
                                                             AND Parts_Number = slor_refp  
                                                             AND Parts_CST = slor_constp 
                                                             AND (Line_Number = slor_nolign OR Line_Number = nlig_noligncm ) )
				                         )
	                    END as Statut,

                    CASE WHEN slor_qteres = (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) AND slor_qterel >0 THEN
                    TO_CHAR((
		                                 SELECT spic_datepic
                                     FROM (
                                        SELECT spic_datepic,
                                         ROW_NUMBER() OVER (ORDER BY spic_datepic ASC) AS rn
                                         FROM sav_pic
                                         WHERE spic_numor = slor_numor
                                        AND spic_refp = slor_refp
                                        AND spic_nolign = slor_nolign
                                           ) AS ranked_dates
                                       WHERE rn = 1
                             ), '%Y-%m-%d')

	                  WHEN slor_qterea = (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) THEN
                  	TO_CHAR((
		                        (SELECT sliv_date 
		                        FROM sav_liv 
                            WHERE sliv_numor = slor_numor 
		                        AND sliv_nolign = slor_nolign)), '%Y-%m-%d')
	                  WHEN slor_natcm = 'C' THEN 
 		                    TO_CHAR((	
                                  ( SELECT date_creation
                                    FROM  gcot_acknow_cat 
                                    WHERE CAST( Numero_PO as varchar(10)) = CAST(slor_numcf  as varchar(10))
                                    AND Parts_Number = slor_refp  
                                    AND Parts_CST = slor_constp 
                                    AND (Line_Number = slor_noligncm OR Line_Number = slor_nolign)
                                    AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat) 
                                                               FROM gcot_acknow_cat 
                                                               WHERE CAST( Numero_PO as varchar(10)) = CAST(slor_numcf  as varchar(10)) 
                                                               AND Parts_Number = slor_refp  
                                                               AND Parts_CST = slor_constp 
                                                               AND (Line_Number = slor_noligncm OR Line_Number = slor_nolign) )
	                        	       )
                                 ), 
                                 '%Y-%m-%d')
                    WHEN slor_typcf = 'CIS' THEN 
		                       TO_CHAR((
                                  ( SELECT date_creation
                                    FROM  gcot_acknow_cat 
                                    WHERE  CAST( Numero_PO as varchar(10)) = CAST(nlig_numcf  as varchar(10))
                                    AND Parts_Number = slor_refp  
                                    AND Parts_CST = slor_constp 
                                    AND (Line_Number = slor_nolign OR Line_Number = nlig_noligncm )
                                    AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat) 
                                                               FROM gcot_acknow_cat 
                                                               WHERE  CAST( Numero_PO as varchar(10)) = CAST(nlig_numcf  as varchar(10))
                                                               AND Parts_Number = slor_refp  
                                                               AND Parts_CST = slor_constp 
                                                               AND (Line_Number = slor_nolign OR Line_Number = nlig_noligncm ))
                                    )
                                 ), '%Y-%m-%d')
	                  END AS dateStatut,

                      CASE  WHEN slor_qterea <> (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) THEN 
	                     ( SELECT message FROM  gcot_acknow_cat 
                          WHERE CAST( Numero_PO as varchar(10)) = CAST(slor_numcf  as varchar(10))
                          AND Parts_Number = slor_refp  
                          AND Parts_CST = slor_constp 
                          AND (Line_Number = slor_noligncm OR Line_Number = slor_nolign)
		   		                AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat) 
                                                      FROM gcot_acknow_cat 
                                                      WHERE CAST( Numero_PO as varchar(10)) = CAST(slor_numcf  as varchar(10)) 
                                                      AND Parts_Number = slor_refp  
                                                      AND Parts_CST = slor_constp 
                                                      AND (Line_Number = slor_noligncm OR Line_Number = slor_nolign))
					            	) 
                        WHEN slor_typcf = 'CIS' THEN 
                                  ( SELECT message FROM  gcot_acknow_cat 
                                            WHERE  CAST( Numero_PO as varchar(10)) = CAST(nlig_numcf  as varchar(10))
                                            AND Parts_Number = slor_refp  
                                            AND Parts_CST = slor_constp 
                                            AND (Line_Number = slor_nolign OR Line_Number = nlig_noligncm )
                                            AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat) 
                                                                         FROM gcot_acknow_cat 
                                                                         WHERE  CAST( Numero_PO as varchar(10)) = CAST(nlig_numcf  as varchar(10))
                                                                         AND Parts_Number = slor_refp  
                                                                         AND Parts_CST = slor_constp 
                                                                         AND (Line_Number = slor_nolign OR Line_Number = nlig_noligncm ) )
                                  )
	                    END as Message ,
                    CASE  
                      WHEN nlig_natcm = 'C' THEN 'COMMANDE'
                      WHEN nlig_natcm = 'L' THEN 'RECEPTION'
                    END AS Statut_ctrmq_cis,
                    
                    CASE
                    WHEN nlig_natcm = 'C' THEN 
                     nlig_numcf   
                    WHEN nlig_natcm = 'L'THEN
                     (SELECT MAX(fllf_numcde) FROM frn_llf WHERE fllf_numliv = nlig_numcf
                          AND fllf_ligne = nlig_noligncm
                          AND fllf_refp = nlig_refp)
                    END as numerocdecis   
                                      

                FROM sav_lor
	              JOIN sav_itv ON slor_numor = sitv_numor AND sitv_interv = slor_nogrp / 100
              LEFT JOIN neg_lig ON slor_numcf = nlig_numcde AND slor_refp = nlig_refp
               WHERE slor_numor = '" . $numOr . "'
                 AND sitv_interv = '" . $itv . "'
                 AND (slor_refp not like '%-L' and slor_refp not like '%-CTRL')
                
                $vtypeligne
                AND slor_constp NOT LIKE '%ZDI%'
                GROUP BY 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20
      ";

    $result = $this->connect->executeQuery($statement);
    $data = $this->connect->fetchResults($result);
    $resultat = $this->convertirEnUtf8($data);
    return $resultat;
  }

  //
}
