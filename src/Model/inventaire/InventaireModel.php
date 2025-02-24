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
                TRIM(ainvi_comment) as description,
                '' as nbre_casier,
                count(ainvp_refp) as nbre_ref,
                ROUND(sum(ainvp_stktheo)) as qte_comptee,
                '' as statut,
                trunc(sum(ainvp_prix * ainvp_stktheo)) as Montant
                FROM  art_invi 
                INNER  JOIN art_invp ON ainvp_numinv = ainvi_numinv_mait
                WHERE ainvi_soc ='HF'    
                AND ainvi_sequence = 1
                AND (ainvp_stktheo <> 0 or ( ainvp_ecart <> 0 ))
                $agence
                $dateD
                $dateF
                group by 1,2,3,4
                order by ainvi_numinv_mait desc
        ";
        $result = $this->connect->executeQuery($statement);
        //  dd($statement);
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
                    CONCAT(
                        ROUND(
                            (SUM(CASE WHEN ainvp_ecart > 0 THEN 1 ELSE 0 END) +
                            SUM(CASE WHEN ainvp_ecart < 0 THEN 1 ELSE 0 END)) 
                            / COUNT(DISTINCT ainvp_refp) * 100
                            ), 
                        '%'
                    ) as pourcentage_ref_avec_ecart,
                    trunc(SUM(ainvp_ecart * ainvp_prix)) as montant_ecart,
                     CONCAT(
                        TRUNC(
                        (SUM(ainvp_ecart * ainvp_prix) / SUM(ainvp_stktheo * ainvp_prix)) * 100), 
                    '%') as pourcentage_ecart
                    FROM art_invp WHERE  (ainvp_stktheo <> 0 or ( ainvp_ecart <> 0 ))
                    and ainvp_numinv = '" . $numInvMax . "'
                    ";
        $result = $this->connect->executeQuery($statement);
        //  dump($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return $resultat;
    }
    public function inventaireDetail($numInv)
    {
        $statement = "SELECT  
                                ainvp_soc as soc,
                                 ainvp_succ as succ, 
                                 ainvp_constp as cst, 
                                 TRIM(ainvp_refp) as refp,
                                  TRIM(abse_desi) as desi ,
                                   TRIM(astp_casier) as casier,
                                    round(ainvp_stktheo) as stock_theo, 
                        '' as qte_comptee, 
                        round(ainvp_ecart) as ecart,
                        ROUND((ainvp_ecart / ainvp_stktheo) * 100 )|| '%' as pourcentage_nbr_ecart,
                        ainvp_prix as PMP,
                        ainvp_prix * ainvp_stktheo as montant_inventaire,
                        ainvp_prix * ainvp_ecart as montant_ajuste
                        FROM art_invp
                        INNER JOIN art_bse on abse_constp = ainvp_constp and abse_refp = ainvp_refp
                        INNER JOIN art_stp on astp_constp = ainvp_constp and astp_refp = ainvp_refp
                        WHERE ainvp_numinv = (select max(ainvi_numinv) from art_invi where ainvi_numinv_mait = '" . $numInv . "')
                        and ainvp_ecart <> 0 and astp_casier not in ('NP','@@@@','CASIER C')
                        ";
        $result = $this->connect->executeQuery($statement);
        //  dump($statement);
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

    public function qteCompte($numInv,$nb_sequence,$refp)
    {
        $statement = " SELECT ROUND((ainvp_stktheo + ainvp_ecart)) as qte_comptee
                        FROM art_invp
                        INNER JOIN art_bse on abse_constp = ainvp_constp 
                        and abse_refp = ainvp_refp
                        INNER JOIN art_stp on astp_constp = ainvp_constp 
                        and astp_refp = ainvp_refp
                        WHERE ainvp_numinv = (select ainvi_numinv from art_invi where ainvi_numinv_mait = '".$numInv."' and ainvi_sequence = '".$nb_sequence."')
                        and ainvp_refp ='".$refp."'
                        and ainvp_ecart <> 0 and astp_casier not in ('NP','@@@@','CASIER C')
        ";
        $result = $this->connect->executeQuery($statement);
        //  dump($statement);
        $data = $this->connect->fetchResults($result);
        $resultat = $this->convertirEnUtf8($data);
        return $resultat;
    }
}
