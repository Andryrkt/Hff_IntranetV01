<?php

namespace App\Model\magasin\cis;

use App\Model\Model;
use App\Model\Traits\ConversionModel;

class CisALivrerModel extends Model
{
    use ConversionModel;
    
    public function listOrALivrer()
    {
        $statement = "SELECT
                    seor_refdem AS Num_DIT,
                    slor_numcf AS Num_CIS, 
                    nlig_datecde AS Date_CIS,
                    -- Agence service créditeur
                    TRIM(slor_succ) || ' - ' || TRIM(slor_servcrt) AS agence_service_travaux,
                    slor_numor AS Num_Or, 
                    seor_dateor AS Date_OR, 
                    -- Agence service débiteur ou client
                    TRIM(CASE slor_natop 
                        WHEN 'CES' THEN TRIM(slor_succdeb) || ' - ' || TRIM(slor_servdeb)
                        WHEN 'VTE' THEN TRIM(TO_CHAR(slor_numcli)) || ' - ' || 
                            (SELECT cbse_nomcli 
                            FROM cli_bse, cli_soc 
                            WHERE csoc_soc = slor_soc 
                            AND cbse_numcli = slor_numcli 
                            AND cbse_numcli = csoc_numcli)
                    END) AS agence_service_debiteur_ou_client, 
                    TRUNC(slor_nogrp / 100) AS NItv, 
                    slor_nolign AS NumLigne, 
                    TRIM(slor_constp) AS Cst, 
                    TRIM(slor_refp) AS Ref, 
                    TRIM(slor_desi) AS Designations, 
                    TRUNC(nlig_qtecde) AS quantiterCommander, 
                    TRUNC(nlig_qtealiv) AS quantiterALivrer, 
                    TRUNC(nlig_qteliv) AS quantiterLivrer 



                FROM 
                    neg_lig
                INNER JOIN 
                    sav_lor ON slor_soc = nlig_soc 
                    AND slor_numcf = nlig_numcde 
                    AND slor_noligncm = nlig_nolign 
                    AND slor_refp = nlig_refp 
                    AND slor_constp = nlig_constp 
                    AND slor_numcf > 0
                INNER JOIN 
                    sav_eor ON seor_soc = slor_soc 
                    AND seor_succ = slor_succ 
                    AND seor_numor = slor_numor
                WHERE 
                    slor_numcf > 0 
                    AND slor_constp NOT IN ('LUB') -- Exclure certains types
                    -- Ajouter des conditions supplémentaires ici si nécessaire
                    AND slor_numcf IN (
                        SELECT 
                            nlig_numcde 
                        FROM 
                            neg_lig
                        WHERE 
                            nlig_constp NOT IN ('LUB')
                            AND nlig_soc = 'HF'
                            -- Ajouter des conditions supplémentaires ici si nécessaire
                        GROUP BY 
                            nlig_numcde 
                        HAVING 
                            SUM(nlig_qtecde) > (SUM(nlig_qtealiv) + SUM(nlig_qteliv))
                            AND SUM(nlig_qtealiv) > 0
                    )
                -- Ajouter des conditions supplémentaires ici pour la validation DocuWare
                -- AND slor_numor IN (<liste_or_validé_docuware>) 
                ORDER BY 
                    slor_numor, 
                    slor_nogrp, 
                    slor_nolign";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }
}