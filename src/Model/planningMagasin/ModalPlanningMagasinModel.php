<?php

namespace App\Model\planningMagasin;


use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Controller\Traits\FormatageTrait;
use App\Model\planningMagasin\planningMagasinModelTrait;

class ModalPlanningMagasinModel extends Model
{
  use ConversionModel;
  use FormatageTrait;
  use planningMagasinModelTrait;

  public function recupDetailPlanningMagasinInformix($numOrIntv)
  {
    $numOr  = "AND A.NLIG_NUMCDE ='" . $numOrIntv . "' ";
    
    $statement = " SELECT 'PLANIFIE' as plan,
A.NLIG_NUMCDE as numOr,
A.NLIG_NUMCF as numCis,
        A.NLIG_NOLIGN as Intv,
        (select NENT_REFCDE from NEG_ENT where NENT_SOC = A.NLIG_SOC and NENT_SUCC = A.NLIG_SUCC and NENT_NUMCDE = A.NLIG_NUMCDE group by 1) as commentaire,
        (select NENT_DATEXP from NEG_ENT where NENT_SOC = A.NLIG_SOC and NENT_SUCC = A.NLIG_SUCC and NENT_NUMCDE = A.NLIG_NUMCDE group by 1)   as datePlanning,
        trim(A.NLIG_constp) as cst,
        trim(A.NLIG_refp) as ref,
        trim(A.NLIG_desi) as desi,
        CASE  
            WHEN nvl(A.NLIG_numcf,0) > 0 THEN (A.NLIG_QTECDE - A.NLIG_QTEALIV)
            ELSE 0
        END AS QteReliquat,
     A.NLIG_QTECDE AS QteRes_Or,
        A.NLIG_QTELIV AS Qteliv,
        A.NLIG_QTEALIV AS QteAll,                 
        CASE  
            WHEN A.NLIG_natcm = 'C' THEN 'COMMANDE'
            WHEN A.NLIG_natcm = 'L' THEN 'RECEPTION'
        END AS Statut_ctrmq,
        CASE
            WHEN A.NLIG_natcm = 'C' THEN
                A.NLIG_numcf
            WHEN A.NLIG_natcm = 'L' THEN
                (SELECT MAX(fllf_numcde) FROM frn_llf WHERE fllf_numliv = A.NLIG_numcf
                          AND fllf_ligne = A.NLIG_noligncm
                          AND fllf_refp = A.NLIG_refp)
         END  AS numeroCmd,
CASE WHEN A.NLIG_QTEALIV = A.NLIG_QTECDE AND (CASE WHEN nvl(A.NLIG_numcf,0) > 0 THEN (A.NLIG_QTECDE - A.NLIG_QTEALIV) ELSE 0 END) > 0 THEN
                        trim('A LIVRER')
                      WHEN A.NLIG_QTECDE = A.NLIG_QTEALIV AND (CASE WHEN nvl(A.NLIG_numcf,0) > 0 THEN (A.NLIG_QTECDE - A.NLIG_QTEALIV) ELSE 0 END) = 0 AND A.NLIG_QTELIV = 0 THEN
                        trim('DISPO STOCK')
                      WHEN A.NLIG_QTELIV =  A.NLIG_QTECDE THEN
                         trim('LIVRE')
                      WHEN A.NLIG_natcm = 'C' THEN
                                ( SELECT libelle_type
                                  FROM  gcot_acknow_cat
                                  WHERE CAST( Numero_PO as varchar(10)) = CAST(A.NLIG_numcf  as varchar(10))
                                  AND Parts_Number = A.NLIG_refp  
                                  AND Parts_CST = A.NLIG_constp
                                  AND Line_Number = A.NLIG_noligncm
                         AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat)
                                                             FROM gcot_acknow_cat
                                                             WHERE CAST( Numero_PO as varchar(10)) = CAST(A.NLIG_numcf  as varchar(10))  
                                                             AND Parts_Number = A.NLIG_refp  
                                                             AND Parts_CST = A.NLIG_constp
                                                             AND Line_Number = A.NLIG_noligncm )
                 )
                      WHEN A.NLIG_typcf = 'CIS' THEN
                           ( SELECT libelle_type
                                  FROM  gcot_acknow_cat
                                  WHERE  CAST( Numero_PO as varchar(10)) = CAST(B.nlig_numcf  as varchar(10))
                                  AND Parts_Number = B.NLIG_refp  
                                  AND Parts_CST = B.NLIG_constp
                                  AND (Line_Number = B.nlig_noligncm )
                               AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat)
                                                             FROM gcot_acknow_cat
                                                             WHERE  CAST( Numero_PO as varchar(10)) = CAST(B.nlig_numcf  as varchar(10))
                                                             AND Parts_Number = B.NLIG_refp  
                                                             AND Parts_CST = B.NLIG_constp
                                                             AND (Line_Number = B.nlig_noligncm ) )

                        group by 1)
                   END as Statut,

CASE WHEN A.NLIG_QTEALIV = A.NLIG_QTECDE AND (CASE WHEN nvl(A.NLIG_numcf,0) > 0 THEN (A.NLIG_QTECDE - A.NLIG_QTEALIV) ELSE 0 END) > 0 THEN
                    TO_CHAR((
                                SELECT npic_date
                                     FROM (
                                        SELECT npic_date,
                                         ROW_NUMBER() OVER (ORDER BY npic_date ASC) AS rn
                                         FROM neg_pic, neg_pil
                                         WHERE npic_numcde = A.NLIG_NUMCDE
AND npic_numcde = npil_numcde
                                        AND npil_refp = A.NLIG_refp
                                        AND npil_nolign = A.NLIG_nolign
                                           ) AS ranked_dates
                                       WHERE rn = 1
                             ), '%Y-%m-%d')
                 WHEN A.NLIG_QTEALIV = A.NLIG_QTECDE THEN
TO_CHAR(A.NLIG_DATEALLOC,'%Y-%m-%d')
WHEN A.NLIG_QTELIV = A.NLIG_QTECDE THEN
                  TO_CHAR((
                       (SELECT (select nliv_datexp from neg_liv where nliv_soc = nllf_soc and nliv_numliv = nllf_numliv)
                       FROM neg_llf
                            WHERE nllf_numcde = A.NLIG_numcde
                       AND nllf_nolign = A.NLIG_nolign)), '%Y-%m-%d')
                 WHEN A.NLIG_natcm = 'C' THEN
                    TO_CHAR((
                                  ( SELECT date_creation
                                    FROM  gcot_acknow_cat
                                    WHERE CAST( Numero_PO as varchar(10)) = CAST(A.NLIG_numcf  as varchar(10))
                                    AND Parts_Number = A.NLIG_refp  
                                    AND Parts_CST = A.NLIG_constp
                                    AND (Line_Number = A.NLIG_noligncm OR Line_Number = A.NLIG_nolign)
                                    AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat)
                                                               FROM gcot_acknow_cat
                                                               WHERE CAST( Numero_PO as varchar(10)) = CAST(A.NLIG_numcf  as varchar(10))  
                                                               AND Parts_Number = A.NLIG_refp  
                                                               AND Parts_CST = A.NLIG_constp
                                                               AND (Line_Number = A.NLIG_noligncm OR Line_Number = A.NLIG_nolign) )
                             )
                                 ),
                                 '%Y-%m-%d')
                    WHEN A.NLIG_typcf = 'CIS' THEN
                      TO_CHAR((
                                  ( SELECT date_creation
                                    FROM  gcot_acknow_cat
                                    WHERE  CAST( Numero_PO as varchar(10)) = CAST(B.nlig_numcf  as varchar(10))
                                    AND Parts_Number = B.NLIG_refp  
                                    AND Parts_CST = B.NLIG_constp
                                    AND (Line_Number = B.nlig_noligncm )
                                    AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat)
                                                               FROM gcot_acknow_cat
                                                               WHERE  CAST( Numero_PO as varchar(10)) = CAST(B.nlig_numcf  as varchar(10))
                                                               AND Parts_Number = B.NLIG_refp  
                                                               AND Parts_CST = B.NLIG_constp
                                                               AND (Line_Number = B.nlig_noligncm ))
                                    )
                                 ), '%Y-%m-%d')
                 END AS dateStatut,

CASE  WHEN A.NLIG_QTELIV <> A.NLIG_QTECDE and A.NLIG_typcf = 'C' THEN
                    ( SELECT message FROM  gcot_acknow_cat
                          WHERE CAST( Numero_PO as varchar(10)) = CAST(A.NLIG_numcf  as varchar(10))
                          AND Parts_Number = A.NLIG_refp  
                          AND Parts_CST = A.NLIG_constp
                          AND (Line_Number = A.NLIG_noligncm OR Line_Number = A.NLIG_nolign)
                 AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat)
                                                      FROM gcot_acknow_cat
                                                      WHERE CAST( Numero_PO as varchar(10)) = CAST(B.NLIG_NUMCF  as varchar(10))  
                                                      AND Parts_Number = A.NLIG_refp  
                                                      AND Parts_CST = A.NLIG_constp
                                                      AND (Line_Number = A.NLIG_noligncm))
         )
                        WHEN A.NLIG_QTELIV <> A.NLIG_QTECDE and A.NLIG_typcf = 'CIS' THEN
                                  ( SELECT message FROM  gcot_acknow_cat
                                            WHERE  CAST( Numero_PO as varchar(10)) = CAST(B.nlig_numcf  as varchar(10))
                                            AND Parts_Number = B.NLIG_refp  
                                            AND Parts_CST = B.NLIG_constp
                                            AND (Line_Number = B.nlig_noligncm )
                                            AND id_gcot_acknow_cat = ( SELECT MAX(id_gcot_acknow_cat)
                                                                         FROM gcot_acknow_cat
                                                                         WHERE  CAST( Numero_PO as varchar(10)) = CAST(B.nlig_numcf  as varchar(10))
                                                                         AND Parts_Number = B.NLIG_refp  
                                                                         AND Parts_CST = B.NLIG_constp
                                                                         AND (Line_Number = B.nlig_noligncm ) )

                                  )
                   END as Message,

   CASE  
                      WHEN B.nlig_natcm = 'C' THEN 'COMMANDE'
                      WHEN B.nlig_natcm = 'L' THEN 'RECEPTION'
                    END AS Statut_ctrmq_cis,
                    CASE
                    WHEN B.nlig_natcm = 'C' THEN
                     B.nlig_numcf  
                    WHEN B.nlig_natcm = 'L' THEN
                     (SELECT MAX(fllf_numcde) FROM frn_llf WHERE fllf_numliv = B.nlig_numcf
                          AND fllf_ligne = B.nlig_noligncm
                          AND fllf_refp = B.nlig_refp)
                    END as numerocdecis
                   
                FROM NEG_LIG A
LEFT JOIN NEG_LIG B ON (A.nlig_soc = b.nlig_soc and A.nlig_numcf = B.nlig_numcde AND A.nlig_constp = B.nlig_constp and A.nlig_refp = B.nlig_refp AND A.nlig_nolign = B.nlig_noligncm)              
WHERE A.NLIG_SUCC in ('01','20','30','40','50','60')
       AND A.NLIG_NATOP in ('DIR')
--AND A.NLIG_QTEFAC = 0
AND A.NLIG_constp  not in ('ZDI','Nmc')
                $numOr


   GROUP BY 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20
ORDER BY 6,2, A.NLIG_NOLIGN
      ";
    // dump($statement);
    $result = $this->connect->executeQuery($statement);
    $data = $this->connect->fetchResults($result);
    $resultat = $this->convertirEnUtf8($data);
    return $resultat;
  }

  // recupCIS
  public function recupOrcis($numOritv)
  {
    $statement = "SELECT  decode(nent_succ,'01','','60','','80','','CIS') as succ
                 from NEG_ENT, NEG_LIG 
                where  nent_succ =nlig_succ
                and nent_numcde = nlig_numcde
                AND nent_numcde ='" . $numOritv . "'
                     ";
    $result = $this->connect->executeQuery($statement);
    $data = $this->connect->fetchResults($result);
    $resultat = $this->convertirEnUtf8($data);
    return $resultat;
  }
  //
  public function recupInfodGcot($numcde)
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
   * Etat partiel piece
   */
  public function recupPartiel($numcde, $refp)
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
 * qteCIS
 */
public function recupeQteCISlig($numOr, $itv, $refp)
  {
    $statement = "SELECT 
                  trunc(nvl(nlig_qtecde,0)) as qteorlig,
                  trunc(nvl(nlig_qtealiv,0) )as qtealllig,
                  trunc(nvl((nlig_qtecde - nlig_qtealiv - nlig_qteliv) ,0))as qtereliquatlig,
                  trunc(nvl(nlig_qteliv,0)) as qtelivlig
                  
                  from neg_lig
                  where nlig_natop = 'CIS'
                  and nlig_numcde ='".$numOr."'
                  --AND  NLIG_NOLIGN  = '".$itv."'
                  and nlig_refp ='" . $refp . "'
        ";
    // dump($statement);
    $result = $this->connect->executeQuery($statement);
    $data = $this->connect->fetchResults($result);
    $resultat = $this->convertirEnUtf8($data);
    return $resultat;
  }
  /**
   * Date LIve ALL
   */
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
                    Eta_magasin
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
    $statement = " SELECT NVL(TRUNC(fcdl_solde), 0) as solde,
                          NVL(TRUNC(fcdl_solde), 0) as qte
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
  
  public function recupClientPlanningMagasin()
  {
    $statement = "SELECT 
                        nent_numcli as numclient,
                        trim(cbse_nomcli) as nom_client
                        from neg_ent, neg_lig, agr_succ, agr_tab ser, agr_usr ope, cli_bse, cli_soc
                        where nent_soc = 'HF'
                        and nlig_soc = nent_soc and nlig_numcde = nent_numcde
                        and asuc_numsoc = nent_soc and asuc_num = nent_succ
                        and csoc_soc = nent_soc and csoc_numcli = cbse_numcli and cbse_numcli = nent_numcli
                        AND (nent_servcrt = ser.atab_code AND ser.atab_nom = 'SER')
                        AND (nent_usr = ausr_num)
                        AND nent_natop not in ('DEV')
                        AND nent_posf not in ('CP')
                        AND to_char(nent_numcli) not like '150%'
                        group by 1,2
         ";
    $result = $this->connect->executeQuery($statement);

    $data = $this->connect->fetchResults($result);

    return $this->convertirEnUtf8($data);
  }
}
