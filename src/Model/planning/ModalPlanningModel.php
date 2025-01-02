<?php
namespace App\Model\planning;


use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Controller\Traits\FormatageTrait;

class ModalPlanningModel extends Model
{
   use ConversionModel;
   use FormatageTrait;
   use PlanningModelTrait;

   public function recuperationDetailPieceInformix($numOrIntv,$criteria){
    $vplan = "'".$criteria['plan']."'";
    $vligneType = $this->typeLigne($criteria); 
    if (strpos($numOrIntv, '-') !== false) { //la chaine contient des tirer
      $numOr = " AND slor_numor || '-' || sitv_interv = '".$numOrIntv."'";
    } else {
      $numOr = " AND slor_numor = '".$numOrIntv."'";
    }
      $statement = " SELECT $vplan as plan,
                            slor_numor as numOr,
                            slor_numcf as numCis,
                            sitv_interv as Intv,
                            trim(sitv_comment) as commentaire,
                            slor_datel as datePlanning,
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
                      WHEN slor_typcf = 'CIS' THEN
		                            ( SELECT libelle_type 
                                  FROM  gcot_acknow_cat 
                                  WHERE Numero_PO = nlig_numcf
                                  AND Parts_Number = slor_refp  
                                  AND Parts_CST = slor_constp 
                                  AND (Line_Number = slor_nolign OR Line_Number = nlig_noligncm )
	                                AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat)
                                                             FROM gcot_acknow_cat 
                                                             WHERE Numero_PO = nlig_numcf
                                                             AND Parts_Number = slor_refp  
                                                             AND Parts_CST = slor_constp 
                                                             AND (Line_Number = slor_nolign OR Line_Number = nlig_noligncm ))
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
                                    AND (Line_Number = slor_noligncm OR Line_Number = slor_nolign)
                                    AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat) 
                                                               FROM gcot_acknow_cat 
                                                               WHERE Numero_PO = slor_numcf  
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
                                    WHERE Numero_PO = nlig_numcf
                                    AND Parts_Number = slor_refp  
                                    AND Parts_CST = slor_constp 
                                    AND (Line_Number = nlig_noligncm OR Line_Number = slor_nolign)
                                    AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat) 
                                                               FROM gcot_acknow_cat 
                                                               WHERE Numero_PO = nlig_numcf
                                                               AND Parts_Number = slor_refp  
                                                               AND Parts_CST = slor_constp 
                                                               AND (Line_Number = nlig_noligncm OR Line_Number = slor_nolign))
                                    )
                                 ), '%Y-%m-%d')
	                  END AS dateStatut,

                      CASE  WHEN slor_qterea <> (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) THEN
	                     ( SELECT message FROM  gcot_acknow_cat 
                          WHERE Numero_PO = slor_numcf 
                          AND Parts_Number = slor_refp  
                          AND Parts_CST = slor_constp 
                          AND (Line_Number = slor_noligncm OR Line_Number = slor_nolign)
		   		                AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat) 
                                                      FROM gcot_acknow_cat 
                                                      WHERE Numero_PO = slor_numcf  
                                                      AND Parts_Number = slor_refp  
                                                      AND Parts_CST = slor_constp 
                                                      AND (Line_Number = slor_noligncm OR Line_Number = slor_nolign))
					            	)
                        WHEN slor_typcf = 'CIS' THEN
                                  ( SELECT message FROM  gcot_acknow_cat 
                                            WHERE Numero_PO = nlig_numcf
                                            AND Parts_Number = slor_refp  
                                            AND Parts_CST = slor_constp 
                                            AND (Line_Number = nlig_noligncm OR Line_Number = slor_nolign)
                                            AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat) 
                                                                         FROM gcot_acknow_cat 
                                                                         WHERE Numero_PO = nlig_numcf
                                                                         AND Parts_Number = slor_refp  
                                                                         AND Parts_CST = slor_constp 
                                                                         AND (Line_Number = slor_noligncm OR Line_Number = slor_nolign) )
                                  )
	                    END as Message ,
                    CASE  
                      WHEN nlig_natcm = 'C' THEN 'COMMANDE'
                      WHEN nlig_natcm = 'L' THEN 'RECEPTION'
                    END AS Statut_ctrmq_cis,
                    nlig_numcf as numerocdecis                        

                FROM sav_lor
	              JOIN sav_itv ON slor_numor = sitv_numor AND sitv_interv = slor_nogrp / 100
              LEFT JOIN neg_lig ON slor_numcf = nlig_numcde AND slor_refp = nlig_refp
                WHERE slor_constp NOT LIKE '%ZDI%'
                --AND slor_typlig = 'P'
                $numOr
                $vligneType
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
public function recuperationEtaMag($numcde, $refp,$cst){
  if($cst == 'CAT'){
    $cst = 'K230';
  }else{
    $cst = $cst;
  }
        $squery = " SELECT Eta_ivato,
                    Eta_magasin
                    FROM Ces_magasin
                    WHERE Cust_ref = '" .$numcde."'
                    AND Part_no = '".$refp."'
                    AND custCode = '".$cst."'
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