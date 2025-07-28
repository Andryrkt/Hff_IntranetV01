<?php

namespace App\Model\dit\migration;

use App\Model\Model;
use App\Model\Traits\ConversionModel;

class MigrationDevisModels extends Model
{
    use ConversionModel;

    /**
     * Methode pour recupérer l'information du devis pour enregistrer dans le base de donnée
     *
     * @param string $numDevis
     * @param bool $estCeForfait
     * @return void
     */
    public function recupDevisSoumisValidation(string $numDevis): array
    {

        $statement = " SELECT sitv_succdeb as num_agence, slor_numor as numero_devis, sitv_datdeb, trim(seor_refdem) as numero_dit, sitv_interv as numero_itv, trim(sitv_comment) as libell_itv, trim(sitv_natop) as nature_operation, trim(seor_devise) as devise, count(slor_constp) as nombre_ligne, Sum(
            CASE
                WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
                WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_qterea
            END 
            * 
            CASE
                WHEN slor_typlig = 'P' THEN slor_pxnreel
                WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
            END
        ) as MONTANT_ITV,  Sum(
            CASE
                WHEN slor_typlig = 'P' AND slor_constp in ('AGR','ATC','AUS','CAT','CGM','CMX','DNL','DYN','GRO','HYS','JDR','KIT','MAN','MNT','OLY','OOM','PAR','PDV','PER','PUB','REM','SHM','TBI','THO') 
                THEN (nvl(slor_qterel, 0) + nvl(slor_qterea, 0) + nvl(slor_qteres, 0) + nvl(slor_qtewait, 0) - nvl(slor_qrec, 0))
            END 
            * 
            CASE
                WHEN slor_typlig = 'P' THEN slor_pxnreel
                WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
            END
        ) AS MONTANT_PIECE,  Sum(
                CASE
                    WHEN slor_typlig = 'M' THEN slor_qterea
                END 
                *
                CASE
                WHEN slor_typlig = 'P' THEN slor_pxnreel
                WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
            END
            ) AS MONTANT_MO,  Sum(
                CASE
                    WHEN slor_constp = 'ZST' THEN (
                        slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec
                    )
                END 
                *
                CASE
                WHEN slor_typlig = 'P' THEN slor_pxnreel
                WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
            END
            ) AS MONTANT_ACHATS_LOCAUX
        ,  Sum(
                CASE
                    WHEN slor_constp <> 'ZST'
                    AND slor_constp like 'Z%' THEN slor_qterea
                END 
                *
                CASE
                WHEN slor_typlig = 'P' THEN slor_pxnreel
                WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
            END
            ) AS MONTANT_DIVERS
        ,  Sum(
                CASE
                    WHEN 
                        slor_typlig = 'P'
                        AND slor_constp NOT like 'Z%'
                        AND slor_constp = 'LUB' 
                    THEN (nvl (slor_qterel, 0) + nvl (slor_qterea, 0) + nvl (slor_qteres, 0) + nvl (slor_qtewait, 0) - nvl (slor_qrec, 0))
                END 
                * 
                CASE
                WHEN slor_typlig = 'P' THEN slor_pxnreel
                WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
            END
            ) AS MONTANT_LUBRIFIANTS
        ,  sum(
                CASE
                    WHEN slor_constp = 'ZDI' AND slor_refp = 'FORFAIT' AND sitv_natop = 'VTE'
                    THEN nvl((slor_pxnreel * slor_qtewait), 0)
                END
            ) AS MONTANT_FORFAIT
         
                    FROM sav_eor, sav_lor, sav_itv
                    WHERE 
            seor_numor = slor_numor
            AND seor_serv = 'DEV'
            AND sitv_numor = slor_numor
            AND sitv_interv = slor_nogrp / 100
            AND seor_soc = 'HF'
            AND slor_soc = seor_soc
            AND sitv_soc = seor_soc
            AND sitv_pos NOT IN ('FC', 'FE', 'CP', 'ST')
            AND seor_numor in ({$numDevis})
         
                    GROUP BY 1, 2, 3, 4, 5, 6, 7, 8
                    ORDER BY slor_numor, sitv_interv
        
        ";
        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }
}
