<?php

namespace App\Model\inventaire;

use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Controller\Traits\FormatageTrait;

class InventaireModel extends Model
{
    use ConversionModel;
    use FormatageTrait;
    use InventaireModelTrait;
    public function recuperationAgenceIrium()
    {
        $statement = " SELECT  trim(asuc_num) as asuc_num ,
                               trim(asuc_lib) as asuc_lib
                      FROM agr_succ
                      WHERE asuc_codsoc = 'HF'
                      AND  (ASUC_NUM like '01' 
                      or ASUC_NUM like '02' 
                      or ASUC_NUM like '10'
                       or ASUC_NUM like '20'
                       or ASUC_NUM like '30'
                       or ASUC_NUM like '40'
                       or ASUC_NUM like '50'
                       or ASUC_NUM like '60'
                       or ASUC_NUM like '92'
                       
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

    public function listeInventaire($criteria)
    {
        $agence = $this->agence($criteria);
        $dateD = $this->dateDebut($criteria);
        $dateF = $this->dateFin($criteria);
        $statement = "SELECT  
                ainvi_numinv_mait as numero_inv, 
                ainvi_date as ouvert_le, 
                (SELECT MAX(DATE (ladm_date)) FROM log_art_invi A 
                JOIN log_adm b ON A.ladm_id = b.ladm_id
                WHERE A.ainvi_soc = ainvi.ainvi_soc
                AND A.ainvi_numinv = (SELECT  MAX(ainvi_numinv) FROM art_invi WHERE ainvi_numinv_mait = ainvi.ainvi_numinv_mait )
                AND A.ainvi_cloture = 'O'
                ) AS date_clo,
                TRIM(ainvi_comment) as description,
                 (
                    select
                        Count(distinct astp_casier)
                    from
                        art_invp,
                        art_stp
                    WHERE
                        ainvp_soc = ainvi_soc
                        AND ainvp_succ = ainvi_succ
                        AND ainvp_numinv = ainvi_numinv
                        AND ainvp_stktheo <> 0
                        AND astp_succ = ainvp_succ
                        AND astp_constp = ainvp_constp
                        AND astp_refp = ainvp_refp
                )  as nbre_casier,
                count(ainvp_refp) as nbre_ref,
                ROUND(sum(ainvp_stktheo)) as qte_comptee,
                 CASE
                    WHEN (
                        select
                            Count(ainvp_refp) from art_invp WHERE  ainvp_soc = ainvi_soc
                            AND ainvp_succ = ainvi_succ
                            AND ainvp_numinv = ainvi_numinv
                            AND ainvp_ecart <> 0
                            ) = 0
                        AND (
                        select Count(ainvp_refp) from art_invp WHERE ainvp_soc = ainvi_soc
                            AND ainvp_succ = ainvi_succ
                            AND ainvp_numinv = ainvi_numinv
                            AND ainvp_ctrlok = 0
                            AND ainvp_nbordereau > 0
                            ) = 0 THEN 
                            'SOLDE'
                    ELSE 
                    (SELECT DECODE (ainvi_cloture, 'O', 'CLOTURE', 'ENCOURS') 
                    FROM  art_invi WHERE ainvi_numinv = ( SELECT MAX(ainvi_numinv) FROM  art_invi WHERE ainvi_numinv_mait = ainvi.ainvi_numinv_mait)        
                     )
                END as statut,
                trunc(sum(ainvp_prix * ainvp_stktheo)) as Montant
                FROM
                art_invi ainvi
                INNER JOIN art_invp s ON s.ainvp_numinv = ainvi.ainvi_numinv_mait
                  WHERE ainvi.ainvi_soc = 'HF'
                AND ainvi.ainvi_sequence = 1
                AND (
                    s.ainvp_stktheo <> 0
                    OR s.ainvp_ecart <> 0
                )
             AND ainvi.ainvi_comment NOT LIKE 'KPI STOCK%'
                $agence
                $dateD
                $dateF
                group by
                ainvi_numinv_mait,
                ainvi_date,
                ainvi_comment,
                ainvi_cloture,
                nbre_casier,
                date_clo,
                statut
                order by ainvi_numinv_mait desc
        ";
        // dd($statement);
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return $resultat;
    }

    public function maxNumInv($numInv)
    {
        $statement = "SELECT  max(ainvi_numinv) as numInvMax
                      FROM art_invi WHERE ainvi_numinv_mait = '" . $numInv . "' 
                      ";
        $result = $this->connect->executeQuery($statement);
        //  dump($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return $resultat;
    }

    public function inventaireLigneEC($numInvMax)
    {
        $statement = "SELECT 
                    COUNT(distinct ainvp_refp) as nombre_ref,
                    trunc(SUM(ainvp_stktheo * ainvp_prix)) as Mont_Total,
                    SUM(CASE WHEN ainvp_ecart > 0 THEN 1 ELSE 0 END) AS nbre_ref_ecarts_positif,
                    SUM(CASE WHEN ainvp_ecart < 0 THEN 1 ELSE 0 END) AS nbre_ref_ecarts_negatifs,
                    SUM(CASE WHEN ainvp_ecart > 0 THEN 1 ELSE 0 END) + SUM(CASE WHEN ainvp_ecart < 0 THEN 1 ELSE 0 END) AS total_nbre_ref_ecarts,
                        ROUND(
                            (SUM(CASE WHEN ainvp_ecart > 0 THEN 1 ELSE 0 END) +
                            SUM(CASE WHEN ainvp_ecart < 0 THEN 1 ELSE 0 END)) 
                            / COUNT(DISTINCT ainvp_refp) * 100
                            ) as pourcentage_ref_avec_ecart,
                    trunc(SUM(ainvp_ecart * ainvp_prix)) as montant_ecart,
                    TRUNC(
                        (SUM(ainvp_ecart * ainvp_prix) / SUM(ainvp_stktheo * ainvp_prix)
                        ) * 100
                        ) as pourcentage_ecart
                    FROM art_invp WHERE  (ainvp_stktheo <> 0 or ( ainvp_ecart <> 0 ))
                    and ainvp_numinv = '" . $numInvMax . "'
                    ";
        // dump($statement);
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return $resultat;
    }
    public function inventaireDetail($numInv)
    {
        $statement = "SELECT    ainvp_datecpt as dateInv,  
                                ainvp_soc as soc,
                                 ainvp_succ as succ, 
                                 ainvp_constp as cst, 
                                 TRIM(ainvp_refp) as refp,
                                  TRIM(abse_desi) as desi ,
                                   TRIM(astp_casier) as casier,
                                    round(ainvp_stktheo) as stock_theo, 
                        '' as qte_comptee, 
                        round(ainvp_ecart) as ecart,
                        		CASE
                                WHEN ainvp_stktheo != 0 THEN
                                     ROUND((ainvp_ecart / ainvp_stktheo) * 100 )|| '%' 
                                ELSE
                                '100'
                                END as pourcentage_nbr_ecart,
                        ROUND(ainvp_prix) as PMP,
                        ROUND(ainvp_prix * ainvp_stktheo)as montant_inventaire,
                        ROUND(ainvp_prix * ainvp_ecart) as montant_ajuste,
                        CASE
                        WHEN ROUND((ainvp_prix * ainvp_stktheo)) != 0 THEN
                        ROUND( ( ainvp_prix * ainvp_ecart) / (ainvp_prix * ainvp_stktheo) * 100 ) || ' %'
                        ELSE
                        '100'
                        END  as pourcentage_ecart
                        FROM art_invp
                        INNER JOIN art_bse on abse_constp = ainvp_constp and abse_refp = ainvp_refp
                        INNER JOIN art_stp on astp_constp = ainvp_constp and astp_refp = ainvp_refp
                        WHERE ainvp_numinv = (select max(ainvi_numinv) from art_invi where ainvi_numinv_mait = '" . $numInv . "')
                        and ainvp_ecart <> 0 and astp_casier not in ('NP','@@@@','CASIER C')
                        group by 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15
                        ";
        //   dump($statement);
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return $resultat;
    }

    public function sumInventaireDetail($numInv)
    {
        $statement = "SELECT  
                            SUM( round(ainvp_stktheo) ) as stock_theo,
                            SUM(round(ainvp_ecart) ) as ecart,
                            CASE
                            WHEN SUM(ainvp_stktheo) != 0 THEN
                                ROUND((SUM(ainvp_ecart) / SUM(ainvp_stktheo)) * 100) || '%'
                            ELSE
                                '100'
                            END AS pourcentage_nbr_ecart,
                           ROUND( SUM(ainvp_prix) )as PMP,
                            ROUND( SUM(ainvp_prix * ainvp_stktheo) ) as montant_inventaire,
                            ROUND( SUM(ainvp_prix * ainvp_ecart ) )as montant_ecart,
                            CASE
                            WHEN ROUND(SUM(ainvp_prix * ainvp_stktheo)) != 0 THEN
                                ROUND((SUM(ainvp_prix * ainvp_ecart) / SUM(ainvp_prix * ainvp_stktheo)) * 100) || ' %'
                            ELSE
                                '100'
                            END AS pourcentage_ecart   
                            FROM art_invp
                            INNER JOIN art_bse on abse_constp = ainvp_constp and abse_refp = ainvp_refp
                            INNER JOIN art_stp on astp_constp = ainvp_constp and astp_refp = ainvp_refp
                            WHERE ainvp_numinv = (select max(ainvi_numinv) from art_invi where ainvi_numinv_mait = '" . $numInv . "')
                            AND  ainvp_ecart <> 0 AND astp_casier NOT IN ('NP','@@@@','CASIER C')

                     
        ";
        //  dump($statement);
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return $resultat;
    }
    public function countSequenceInvent($numInv)
    {
        $statement = " SELECT DISTINCT(ainvi_sequence) as nb_sequence
                                        FROM art_invi 
                                        WHERE ainvi_numinv_mait ='" . $numInv . "'
                        ";
        $result = $this->connect->executeQuery($statement);
        //  dump($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return $resultat;
    }

    public function qteCompte($numInv, $nb_sequence, $refp)
    {
        $statement = " SELECT ROUND((ainvp_stktheo + ainvp_ecart)) as qte_comptee
                        FROM art_invp
                        INNER JOIN art_bse on abse_constp = ainvp_constp 
                        and abse_refp = ainvp_refp
                        INNER JOIN art_stp on astp_constp = ainvp_constp 
                        and astp_refp = ainvp_refp
                        WHERE ainvp_numinv = (select ainvi_numinv from art_invi where ainvi_numinv_mait = '" . $numInv . "' and ainvi_sequence = '" . $nb_sequence . "')
                        and ainvp_refp ='" . $refp . "'
                        and ainvp_ecart <> 0 and astp_casier not in ('NP','@@@@','CASIER C')
        ";
        $result = $this->connect->executeQuery($statement);
        //  dump($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return $resultat;
    }
    public function bordereauListe($numInv, $criteria)
    {
        if ($criteria['choix'] == 'ECART') {
            $ecart = "AND AINVP_ECART <> 0";
        } else {
            $ecart = "";
        }
        $statement = " SELECT   ainvp_numinv as numInv,
                                ainvp_nbordereau as numBordereau ,
                               ainvp_nligne as ligne,
                                (select astp_casier 
                                from art_stp
                                 WHERE astp_soc = ainvp_soc 
                                 AND astp_succ = ainvp_succ 
                                 AND astp_constp = ainvp_constp 
                                 AND astp_refp = ainvp_refp) as casier ,
	                            ainvp_constp as cst, 
                                TRIM(ainvp_refp) as refp,
                                TRIM((select abse_desi 
                                from art_bse 
                                WHERE abse_constp = ainvp_constp 
                                AND abse_refp = ainvp_refp)) as descrip,
                                ROUND(ainvp_stktheo) as qte_theo,
	                            ROUND((select astp_reserv 
                                from art_stp 
                                WHERE astp_soc = ainvp_soc 
                                AND astp_succ = ainvp_succ 
                                AND astp_constp = ainvp_constp
                                AND astp_refp = ainvp_refp)) as qte_alloue,
	                            ainvp_date as dateinv
                        from art_invp
	                    WHERE ainvp_soc = 'HF'  
	                    AND ainvp_numinv = ( select  max(ainvi_numinv) from art_invi  where ainvi_numinv_mait = '" . $numInv . "')
	                    AND ainvp_nbordereau > 0
                        $ecart
                    	order by 2,3
                    ";
        //  dd($statement);
        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return $resultat;
    }
}
