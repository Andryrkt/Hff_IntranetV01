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
  public function recuperationMaterielplanifier($criteria, $lesOrValides)
  {
    if(!empty($lesOrValides)){
      $vOrvalDw = "AND seor_numor ||'-'||sitv_interv in ('".$lesOrValides."') ";
    }else{
      $vOrvalDw = " AND seor_numor ||'-'||sitv_interv in ('')";
    }

    $vligneType = $this->typeLigne($criteria);  
    $vPiecesSum = $this->sumPieces($criteria);
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
                      $vYearsStatutPlan as annee,
                      $vMonthStatutPlan as mois,
                      seor_numor ||'-'||sitv_interv as orIntv,

                      (  SELECT SUM( CASE WHEN slor_typlig = 'P' AND slor_constp NOT like '%ZDI%' THEN
                                                slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec
                                          ELSE slor_qterea END )
                        FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv  $vPiecesSum ) as QteCdm,
                    	(  SELECT SUM(slor_qterea ) FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv  $vPiecesSum ) as QtLiv,
                      (  SELECT SUM(slor_qteres )FROM sav_lor as A  , sav_itv  AS B WHERE  A.slor_numor = B.sitv_numor AND  B.sitv_interv = A.slor_nogrp/100 AND A.slor_numor = C.slor_numor and B.sitv_interv  = D.sitv_interv   $vPiecesSum ) as QteALL

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

                    AND $vYearsStatutPlan = $annee
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

        
        $result = $this->connect->executeQuery($statement);
                //  dump($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return $resultat;
  }

  public function exportExcelPlanning($criteria, $lesOrValides){
    if(!empty($lesOrValides)){
      $vOrvalDw = "AND seor_numor ||'-'||sitv_interv in ('".$lesOrValides."') ";
    }else{
      $vOrvalDw = " AND seor_numor ||'-'||sitv_interv in ('')";
    }

    $vligneType = $this->typeLigne($criteria);  
    $vPiecesSum = $this->sumPieces($criteria);
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
                      $vYearsStatutPlan as annee,
                      $vMonthStatutPlan as mois,
                      seor_numor ||'-'||sitv_interv as orIntv,
                      slor_pos
                      
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

                    AND $vYearsStatutPlan = $annee
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
                    group by 1,2,3,4,5,6,7,8,9,10,11,12,13,14
		                order by 1,5  ";      

        
        $result = $this->connect->executeQuery($statement);
                  // dump($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return $resultat;

  }
  public function recuperationDetailPieceInformix($numOrIntv,$criteria){
    if(!empty($criteria['typeligne'])){
        switch($criteria['typeligne']){
          case "TOUTES": 
            $vtypeligne = " ";
            break;
        case "PIECES_MAGASIN":
            $vtypeligne = " AND  slor_constp  <> 'LUB'  AND slor_constp not like 'Z%'    AND slor_typlig = 'P'";
            break;
        case "ACHAT_LOCAUX":
            $vtypeligne = " AND slor_constp  = 'ZST'" ;
            break;
        case "LUBRIFIANTS":
            $vtypeligne = " AND slor_constp = 'LUB'   AND slor_typlig = 'P' ";
            break;
        }
    }
      $statement = " SELECT slor_numor as numOr,
                            slor_numcf as numCis,
                            sitv_interv as Intv,
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
                            
                     CASE  WHEN slor_natcm = 'C' THEN 
                     'COMMANDE'
                      WHEN slor_natcm = 'L' THEN 
                      'RECEPTION'
                      END AS Statut_ctrmq,
                      CASE WHEN slor_natcm = 'C' THEN 
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
                                  WHERE Numero_PO = slor_numcf 
                                  AND Parts_Number = slor_refp  
                                  AND Parts_CST = slor_constp 
                                  AND Line_Number = slor_noligncm 
		   		                        AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat)
                                                             FROM gcot_acknow_cat 
                                                             WHERE Numero_PO = slor_numcf  
                                                             AND Parts_Number = slor_refp  
                                                             AND Parts_CST = slor_constp 
                                                             AND Line_Number = slor_noligncm )
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
                                    WHERE Numero_PO = slor_numcf 
                                    AND Parts_Number = slor_refp  
                                    AND Parts_CST = slor_constp 
                                    AND Line_Number = slor_noligncm 
                                    AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat) 
                                                               FROM gcot_acknow_cat 
                                                               WHERE Numero_PO = slor_numcf  
                                                               AND Parts_Number = slor_refp  
                                                               AND Parts_CST = slor_constp 
                                                               AND Line_Number = slor_noligncm )
	                        	       )
                                 ), '%Y-%m-%d')
	                  END AS dateStatut,

                      CASE  WHEN slor_qterea <> (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) THEN
	                     ( SELECT message FROM  gcot_acknow_cat 
                          WHERE Numero_PO = slor_numcf 
                          AND Parts_Number = slor_refp  
                          AND Parts_CST = slor_constp 
                          AND Line_Number = slor_noligncm 
		   		                AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat) 
                                                      FROM gcot_acknow_cat 
                                                      WHERE Numero_PO = slor_numcf  
                                                      AND Parts_Number = slor_refp  
                                                      AND Parts_CST = slor_constp 
                                                      AND Line_Number = slor_noligncm )
					            	)
	                    END as Message ,
                     CASE  WHEN nlig_natcm = 'C' THEN 
                    'COMMANDE'
                     WHEN nlig_natcm = 'L' THEN 
                    'RECEPTION'
                    END AS Statut_ctrmq_cis,
                    nlig_numcf as numerocdecis                        

                FROM sav_lor
	              JOIN sav_itv ON slor_numor = sitv_numor AND sitv_interv = slor_nogrp / 100
              LEFT JOIN neg_lig ON slor_numcf = nlig_numcde AND slor_refp = nlig_refp
                WHERE slor_numor || '-' || sitv_interv = '".$numOrIntv."'
                --AND slor_typlig = 'P'
                $vtypeligne
                AND slor_constp NOT LIKE '%ZDI%'
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
public function recuperationEtaMag($numOr, $refp){
        $squery = " SELECT Eta_ivato,
                    Eta_magasin
                    FROM Ces_magasin
                    WHERE Po_number = '" .$numOr."'
                    AND Part_no = '".$refp."'
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
public function recuperationPartiel($numcde, $refp){
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
  * gcot ORD
  */
  public function recuperationinfodGcot ($numcde){
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
        and ofs_id = '".$numItv."'
        where skw.ofh_id ='".$numOr."'
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
        where sitv_numor = '".$numOr."'
        and sitv_interv = '".$numItv."' 
        and ssal_numsal <> 9999
      ";

    $result = $this->connect->executeQuery($statement);

    $data = $this->connect->fetchResults($result);

    return $this->convertirEnUtf8($data);
  }
}