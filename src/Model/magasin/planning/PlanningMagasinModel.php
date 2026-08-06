<?php

namespace App\Model\magasin\planning;

use App\Model\Model;


class PlanningMagasinModel extends Model
{
    public function getPlanningMagasin()
    {
        $statement = "SELECT
                o.fcdl_numcde AS numero_commande,
                o.fbse_numfou as numero_fournisseur,
                o.fbse_nomfou as nom_fournisseur,
                o.asuc_lib || ' - ' || o.atab_lib AS agence_service,
                o.fcde_datec as date_commande,
                CASE
                    WHEN o.total_facture > 0 AND o.total_facture < o.total_qte THEN 'Partiellement facturé'
                    WHEN o.total_dispo > 0 AND o.total_dispo < o.total_qte THEN 'Partiellement dispo'
                    WHEN o.total_dispo = o.total_qte AND o.total_facture = 0 THEN 'Complet non facturé'
                    WHEN o.total_recu = 0 THEN 'Aucune reception'
                    WHEN o.total_facture = o.total_qte THEN 'Complet facturé'
                    ELSE 'Autre'
                END AS statut
            FROM (
                SELECT
                    l.fcdl_numcde,
                    l.fbse_numfou,
                    l.fbse_nomfou,
                    l.asuc_lib,
                    l.atab_lib,
                    l.fcde_datec,
                    SUM(l.fcdl_qte)     AS total_qte,
                    SUM(l.qte_recue)    AS total_recu,
                    SUM(l.qte_facturee) AS total_facture,
                    SUM(l.qte_dispo)    AS total_dispo
                FROM (
                    SELECT
                        fcdl.fcdl_numcde,
                        fcdl.fcdl_ligne,
                        fcdl.fcdl_qte,
                        fbse_numfou,
                        fbse_nomfou,
                        asuc_lib,
                        atab_lib,
                        fcde_datec,
                        NVL(SUM(fllf.fllf_qteliv), 0) AS qte_recue,
                        NVL(SUM(fllf.fllf_qtefac), 0) AS qte_facturee,
                        NVL(SUM(CASE WHEN fllf.fllf_majstk = 'O' THEN fllf.fllf_qteliv ELSE 0 END), 0) AS qte_dispo
                    FROM frn_cdl fcdl
                    INNER JOIN frn_cde
                        ON fcdl_soc    = fcde_soc
                    AND fcdl_succ   = fcde_succ
                    AND fcdl_numcde = fcde_numcde
                    INNER JOIN frn_bse
                        ON fbse_numfou = fcde_numfou
                    INNER JOIN agr_succ
                        ON asuc_num = fcde_succ
                    INNER JOIN agr_tab
                        ON atab_code = fcde_serv
                    AND atab_nom  = 'SER'
                    LEFT JOIN frn_llf fllf ON fllf.fllf_soc = fcdl.fcdl_soc AND fllf.fllf_numcde = fcdl.fcdl_numcde AND fllf.fllf_ligne = fcdl.fcdl_ligne
                    WHERE year(fcde_date) = 2026
                    AND (fcde_cdeext NOT LIKE '%CIS%' OR fcde_cdeext IS NULL)
                    AND fcdl_constp IN ('AGR','ATC','AUS','CAT','CGM','CMX','DNL','DYN','GRO','HYS','JDR','KIT','MAN','MNT','OLY','OOM','PAR','PDV','PER','PUB','REM','SHM','TBI','THO')
                    GROUP BY fcdl.fcdl_numcde, fcdl.fcdl_ligne, fcdl.fcdl_qte, fbse_numfou, fbse_nomfou, asuc_lib, atab_lib, fcde_datec
                ) l
                GROUP BY l.fcdl_numcde, l.fbse_numfou, l.fbse_nomfou, l.asuc_lib, l.atab_lib, l.fcde_datec
            ) o
            ORDER BY o.fcdl_numcde
            ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupListeFournissseur()
    {
        $statement = " SELECT  
                        FBSE_NUMFOU AS num_fournisseur,
                        UPPER(FBSE_NOMFOU) AS nom_fournisseur
                    FROM 
                        FRN_BSE
                    JOIN 
                        FRN_FOU ON FBSE_NUMFOU = FFOU_NUMFOU
                    WHERE 
                        FFOU_SOC = 'HF'
                    ORDER BY 
                        FBSE_NOMFOU;
        ";

        $result = $this->connect->executeQuery($statement);


        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }
}
