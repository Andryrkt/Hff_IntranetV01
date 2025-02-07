<?php

namespace App\Model\dit;

use App\Service\GlobalVariablesService;

class RequestSoumisValidation
{

    public static function getPrixReelCase()
    {
        return "
            CASE
                WHEN slor_typlig = 'P' THEN slor_pxnreel
                WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
            END
        ";
    }

    public static function getMontantITV()
    {
        return "Sum(
            CASE
                WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
                WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_qterea
            END 
            * 
            ".self::getPrixReelCase()."
        ) as MONTANT_ITV";
    }

    public static function getMontantPiece()
    {
        return " Sum(
            CASE
                WHEN slor_typlig = 'P' AND slor_constp in (" . GlobalVariablesService::get('pieces_magasin') . ") 
                THEN (nvl(slor_qterel, 0) + nvl(slor_qterea, 0) + nvl(slor_qteres, 0) + nvl(slor_qtewait, 0) - nvl(slor_qrec, 0))
            END 
            * 
            ".self::getPrixReelCase()."
        ) AS MONTANT_PIECE";
    }

    public static function getMontantMo()
    {
        return " Sum(
                CASE
                    WHEN slor_typlig = 'M' THEN slor_qterea
                END 
                *
                ".self::getPrixReelCase()."
            ) AS MONTANT_MO";
    }

    public static function getMontantAchatLocaux()
    {
        return " Sum(
                CASE
                    WHEN slor_constp = 'ZST' THEN (
                        slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec
                    )
                END 
                *
                ".self::getPrixReelCase()."
            ) AS MONTANT_ACHATS_LOCAUX
        ";
    }

    public static function getMontantFraisDivers()
    {
        return " Sum(
                CASE
                    WHEN slor_constp <> 'ZST'
                    AND slor_constp like 'Z%' THEN slor_qterea
                END 
                *
                ".self::getPrixReelCase()."
            ) AS MONTANT_DIVERS
        ";
    }

    public static function getMontantLubrifiants()
    {
        return " Sum(
                CASE
                    WHEN 
                        slor_typlig = 'P'
                        AND slor_constp NOT like 'Z%'
                        AND slor_constp = 'LUB' 
                    THEN (nvl (slor_qterel, 0) + nvl (slor_qterea, 0) + nvl (slor_qteres, 0) + nvl (slor_qtewait, 0) - nvl (slor_qrec, 0))
                END 
                * 
                ".self::getPrixReelCase()."
            ) AS MONTANT_LUBRIFIANTS
        ";
    }

    public static function getMontantForfait()
    {
        return " sum(
                CASE
                    WHEN slor_constp = 'ZDI' AND slor_refp = 'FORFAIT' AND sitv_natop = 'VTE'
                    THEN nvl((slor_pxnreel * slor_qtewait), 0)
                END
            ) AS MONTANT_FORFAIT
        ";
    }

    public static function getConditions(array $condition)
    {
        return "
            seor_numor = slor_numor
            AND seor_serv = 'DEV'
            AND sitv_numor = slor_numor
            AND sitv_interv = slor_nogrp / 100
            AND seor_soc = 'HF'
            AND slor_soc = seor_soc
            AND sitv_soc = seor_soc
            AND sitv_pos NOT IN ('FC', 'FE', 'CP', 'ST')
            AND seor_numor = '{$condition['numDevis']}'
        ";
    }

    public static function buildQuery(array $condition)
    {
        $selectColumns = [
            "sitv_succdeb as SERV_DEBITEUR",
            "slor_numor",
            "sitv_datdeb",
            "trim(seor_refdem) as NUMERO_DIT",
            "sitv_interv as NUMERO_ITV",
            "trim(sitv_comment) as LIBELLE_ITV",
            "trim(sitv_natop) as NATURE_OPERATION",
            "trim(seor_devise) as devise",
            "count(slor_constp) as NOMBRE_LIGNE",
            self::getMontantITV(),
            self::getMontantPiece(),
            self::getMontantMo(),
            self::getMontantAchatLocaux(),
            self::getMontantFraisDivers(),
            self::getMontantLubrifiants(),
            self::getMontantForfait()
        ];

        $query = "SELECT " . implode(", ", $selectColumns) . " 
                    FROM sav_eor, sav_lor, sav_itv
                    WHERE " . self::getConditions($condition) . " 
                    GROUP BY 1, 2, 3, 4, 5, 6, 7, 8
                    ORDER BY slor_numor, sitv_interv";

        return $query;
    }

/** FORFAIT */
    // public static function getConditionsForfait(array $condition)
    // {
    //     return "
    //         seor_numor = slor_numor
    //         AND seor_serv = 'DEV'
    //         AND sitv_numor = slor_numor
    //         AND sitv_interv = slor_nogrp / 100
    //         AND seor_soc = 'HF'
    //         AND slor_soc = seor_soc
    //         AND sitv_soc = seor_soc
    //         AND sitv_pos NOT IN ('FC', 'FE', 'CP', 'ST')
    //         AND seor_numor = '{$condition['numDevis']}'
    //         AND slor_constp = 'ZDI' AND slor_refp = 'FORFAIT' AND sitv_natop = 'VTE' --ajout de ceci pour le forfait
    //     ";
    // }

    // public static function buildQueryForfait(array $condition)
    // {
    //     $selectColumns = [
    //         "sitv_succdeb as SERV_DEBITEUR",
    //         "slor_numor",
    //         "sitv_datdeb",
    //         "trim(seor_refdem) as NUMERO_DIT",
    //         "sitv_interv as NUMERO_ITV",
    //         "trim(sitv_comment) as LIBELLE_ITV",
    //         "trim(sitv_natop) as NATURE_OPERATION",
    //         "count(slor_constp) as NOMBRE_LIGNE",
    //         self::getMontantITV(),
    //         self::getMontantPiece(),
    //         self::getMontantMo(),
    //         self::getMontantAchatLocaux(),
    //         self::getMontantFraisDivers(),
    //         self::getMontantLubrifiants(),
    //         self::getMontantForfait()
    //     ];

    //     $query = "SELECT " . implode(", ", $selectColumns) . " 
    //                 FROM sav_eor, sav_lor, sav_itv
    //                 WHERE " . self::getConditionsForfait($condition) . " 
    //                 GROUP BY 1, 2, 3, 4, 5, 6, 7
    //                 ORDER BY slor_numor, sitv_interv";

    //     return $query;
    // }

}
