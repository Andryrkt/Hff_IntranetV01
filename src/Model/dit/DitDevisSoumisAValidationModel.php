<?php

namespace App\Model\dit;

use App\Model\Model;
use App\Model\Traits\ConversionModel;

class DitDevisSoumisAValidationModel extends Model
{
    use ConversionModel;
    
    public function recupNumeroDevis($numDit)
    {
        $statement = "SELECT  seor_numor  as numDevis
                from sav_eor
                where seor_refdem = '".$numDit."'
                AND seor_serv = 'DEV'
                ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupDevisSoumisValidation($numDevis)
    {
      $statement = "SELECT
          sitv_succdeb as SERV_DEBITEUR,  
          slor_numor,
          sitv_datdeb,
          trim(seor_refdem) as NUMERO_DIT,
          sitv_interv as NUMERO_ITV,
          trim(sitv_comment) as LIBELLE_ITV,
          count(slor_constp) as NOMBRE_LIGNE,
          Sum(
              CASE
                  WHEN slor_typlig = 'P' 
                  THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
                  WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_qterea
              END 
              * 
              CASE
                  WHEN slor_typlig = 'P' THEN slor_pxnreel
                  WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
              END
          ) as MONTANT_ITV,

          Sum(
              CASE
                  WHEN slor_typlig = 'P'
                  AND slor_constp NOT like 'Z%'
                  AND slor_constp <> 'LUB' THEN (nvl (slor_qterel, 0) + nvl (slor_qterea, 0) + nvl (slor_qteres, 0) + nvl (slor_qtewait, 0) - nvl (slor_qrec, 0))
              END 
              * 
              CASE
                  WHEN slor_typlig = 'P' THEN slor_pxnreel
                  WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
              END
          ) AS MONTANT_PIECE,

          Sum(
              CASE
                  WHEN slor_typlig = 'M' THEN slor_qterea
              END * CASE
                  WHEN slor_typlig = 'P' THEN slor_pxnreel
                  WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
              END
          ) AS MONTANT_MO,

          Sum(
              CASE
                  WHEN slor_constp = 'ZST' THEN (
                      slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec
                  )
              END * CASE
                  WHEN slor_typlig = 'P' THEN slor_pxnreel
                  WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
              END
          ) AS MONTANT_ACHATS_LOCAUX,

          Sum(
              CASE
                  WHEN slor_constp <> 'ZST'
                  AND slor_constp like 'Z%' THEN slor_qterea
              END * CASE
                  WHEN slor_typlig = 'P' THEN slor_pxnreel
                  WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
              END
          ) AS MONTANT_DIVERS,

          Sum(
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

          from sav_eor, sav_lor, sav_itv
          WHERE
              seor_numor = slor_numor
              AND seor_serv = 'DEV'
              AND sitv_numor = slor_numor
              AND sitv_interv = slor_nogrp / 100
              AND seor_soc = 'HF'
              AND slor_soc=seor_soc
              AND sitv_soc=seor_soc
          AND sitv_pos NOT IN('FC', 'FE', 'CP', 'ST')
          --AND sitv_servcrt IN ('ATE','FOR','GAR','MAN','CSP','MAS','LR6','LST')
          AND seor_numor = '".$numDevis."'
          --AND SEOR_SUCC = '01'
          group by 1, 2, 3, 4, 5, 6
          order by slor_numor, sitv_interv
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }
}