<?php

namespace App\Model\magasin\cis;

use App\Model\Model;
use App\Model\Traits\ConversionModel;

class CisATraiterModel extends Model
{
    use ConversionModel;
    
    public function listOrATraiter()
    {
        $statement = "SELECT
                    seor_refdem AS NumDit,
                    slor_numcf AS NumCis, 
                    nlig_datecde AS DateCis, 
                    -- Agence service créditeur
                    TRIM(slor_succ) || ' - ' || TRIM(slor_servcrt) AS agenceServiceTravaux,
                    slor_numor AS NumOr, 
                    seor_dateor AS DateOr, 
                    -- Agence service débiteur
                    trim(CASE 
                        WHEN slor_natop = 'CES' THEN TRIM(slor_succdeb) || ' - ' || TRIM(slor_servdeb)
                        WHEN slor_natop = 'VTE' THEN TRIM(TO_CHAR(slor_numcli)) || ' - ' || 
                            (SELECT cbse_nomcli 
                            FROM cli_bse, cli_soc 
                            WHERE csoc_soc = slor_soc 
                            AND cbse_numcli = slor_numcli 
                            AND cbse_numcli = csoc_numcli)
                    END) AS agenceServiceDebiteur,
                    TRUNC(slor_nogrp / 100) AS NItv, 
                    slor_nolign AS NumLigne, 
                    trim(slor_constp) AS Cst, 
                    trim(slor_refp) AS Ref, 
                    trim(slor_desi) AS Designations,
                    -- Quantité demandée
                    TRUNC(CASE 
                        WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) 
                        WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_qterea 
                    END) AS Qte_dem


                FROM 
                    sav_lor  
                INNER JOIN 
                    neg_lig ON nlig_soc = slor_soc 
                    AND nlig_numcde = slor_numcf 
                    AND nlig_nolign = slor_noligncm
                INNER JOIN 
                    sav_eor ON seor_soc = slor_soc 
                    AND seor_succ = slor_succ 
                    AND seor_numor = slor_numor
                WHERE
                    slor_soc = 'HF' 
                    AND slor_numcf > 0 -- Ne filtre que les lignes d'OR contremarquées 
                    AND (
                        NVL(nlig_numcf, 0) = 0 -- La CIS n'est pas contremarquée
                        AND NVL(nlig_qtealiv, 0) = 0 -- Pas encore de quantité à livrer
                        AND NVL(nlig_qteliv, 0) = 0 -- Pas encore de quantité livrée
                    )
                    AND nlig_natop = 'CIS'
                    AND slor_constp NOT IN ('LUB', 'SHE', 'JOV')
                -- Ajouter d'autres conditions si nécessaire pour les pièces magasin et les achats locaux
                -- AND slor_numor IN (<liste_or_validé_docuware>)
                ORDER BY 
                    slor_datel, -- Date planning
                    slor_numor";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }
}