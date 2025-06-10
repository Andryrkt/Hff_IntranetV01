<?php

namespace App\Model\dit;


use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Service\GlobalVariablesService;
use Symfony\Component\Validator\Constraints\IsNull;

class DitOrSoumisAValidationModel extends Model
{
    use ConversionModel;
    public function recupOrSoumisValidation($numOr)
    {
        $statement = "SELECT
        slor_numor,
        sitv_datdeb,
        trim(seor_refdem) as NUMERo_DIT,
        sitv_interv as NUMERO_ITV,
        trim(sitv_comment) as LIBELLE_ITV,
        count(slor_constp) as NOMBRE_LIGNE,
        Sum(
            CASE
                WHEN slor_typlig = 'P' THEN (
                    slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec
                )
                WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_qterea
            END * CASE
                WHEN slor_typlig = 'P' THEN slor_pxnreel
                WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
            END
        ) as MONTANT_ITV,

        Sum(
            CASE
                WHEN slor_typlig = 'P'
                AND slor_constp NOT like 'Z%'
                AND slor_constp <> 'LUB' THEN (
                    nvl (slor_qterel, 0) + nvl (slor_qterea, 0) + nvl (slor_qteres, 0) + nvl (slor_qtewait, 0) - nvl (slor_qrec, 0)
                )
            END * CASE
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
                WHEN slor_typlig = 'P'
                AND slor_constp NOT like 'Z%'
                AND slor_constp = 'LUB' THEN (
                    nvl (slor_qterel, 0) + nvl (slor_qterea, 0) + nvl (slor_qteres, 0) + nvl (slor_qtewait, 0) - nvl (slor_qrec, 0)
                )
            END * CASE
                WHEN slor_typlig = 'P' THEN slor_pxnreel
                WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
            END
        ) AS MONTANT_LUBRIFIANTS

        from sav_eor, sav_lor, sav_itv
        WHERE
            seor_numor = slor_numor
            AND seor_serv <> 'DEV'
            AND sitv_numor = slor_numor
            AND sitv_interv = slor_nogrp / 100

        --AND sitv_pos NOT IN('FC', 'FE', 'CP', 'ST')
        --AND sitv_servcrt IN ('ATE','FOR','GAR','MAN','CSP','MAS')
        AND seor_numor = '" . $numOr . "'
        --AND SEOR_SUCC = '01'
        group by
            1,
            2,
            3,
            4,
            5
        order by slor_numor, sitv_interv
    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }


    public function recupererNumdevis($numOr)
    {
        $statement = "SELECT  seor_numdev  
                from sav_eor
                where seor_numor = '" . $numOr . "'";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupNumeroOr($numDit)
    {
        $statement = " SELECT 
            seor_numor as numOr
            from sav_eor
            where seor_refdem = '" . $numDit . "'
            AND seor_serv = 'SAV'

        ";
        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupNumeroMatricule($numDit, $numOr)
    {
        $statement = " SELECT 
            seor_nummat as numMatricule
            from sav_eor
            where seor_refdem = '" . $numDit . "'
            AND seor_numor = '" . $numOr . "'
            AND seor_serv = 'SAV'

        ";
        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupNbDatePlanningVide($numOr)
    {
        $statement = "SELECT count(*) as nbPlanning
        from sav_itv 
        where sitv_numor = '" . $numOr . "' 
        and sitv_datepla is null";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupPositonOr($numor)
    {
        $statement = " SELECT seor_pos as position from sav_eor where seor_numor = '" . $numor . "'";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return  $this->convertirEnUtf8($data);
    }

    public function recupNbPieceMagasin($numOr)
    {
        $statement = " SELECT
            count(slor_constp) as nbr_sortie_magasin 
            from sav_lor 
            where slor_constp in (" . GlobalVariablesService::get('pieces_magasin') . ") 
            and slor_typlig = 'P' 
            and slor_numor = '" . $numOr . "'
            ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupNbAchatLocaux($numOr)
    {
        $statement = " SELECT
            count(slor_constp) as nbr_achat_locaux 
            from sav_lor 
            where slor_constp in (" . GlobalVariablesService::get('achat_locaux') . ")  
            and slor_numor = '" . $numOr . "'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupRefClient($numOr)
    {
        $statement = " SELECT seor_lib  
                    from sav_eor 
                    where seor_numor='" . $numOr . "'
                    ";
        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    // public function recupBlockageStatut($numOr)
    // {
    //     $sqlNumVersMax = " SELECT MAX(numeroVersion) as numversionMax
    //             FROM ors_soumis_a_validation
    //             WHERE numeroOR = '{$numOr}'";

    //     $numVersionMax = $this->retournerResult28($sqlNumVersMax);

    //     if ($numVersionMax[0]['numversionMax'] == 0 || is_null($numVersionMax[0]['numversionMax'])) {
    //         dump("pas de numéro or");
    //         return "ne pas bloquer";
    //     } else {
    //         dump("misy version");
    //         $sql1 = "SELECT
    //             CASE
    //                 WHEN COUNT(*) > 0 THEN 'ne pas bloquer'
    //                 ELSE 'bloquer'
    //             END AS retour
    //         FROM ors_soumis_a_validation
    //         WHERE numeroOR = '41326877'
    //         AND numeroVersion = {$numVersionMax[0]['numversionMax']}
    //         AND (
    //             statut = 'Validé' 

    //         )
    //         ";

    //         $sql2 = "SELECT
    //                 statut
    //             FROM
    //                 ors_soumis_a_validation
    //             WHERE
    //                 numeroOR = '51303448'
    //                 AND numeroVersion = :numVersionMax
    //                 AND REPLACE(REPLACE(statut, 'b\"', ''), '\"', '') LIKE 'Validé%'
    //             ";

    //         $sql3 = "SELECT statut_or from demande_intervention where numero_or = '51303448'";
    //     }




    //     $statement = $this->connexion->query($sql2);
    //     $data = [];
    //     while ($tabType = odbc_fetch_array($statement)) {
    //         $data[] = $tabType;
    //     }
    //     dd($data);

    //     dd("fin");

    //     $sql2 = " SELECT COUNT(*) as nb FROM ors_soumis_a_validation WHERE numeroOR= '{$numOr}' ";

    //     // // if ($this->retournerResult28($sql2) == 0) {
    //     //     // return 'ne pas bloquer';
    //     // // } else {
    //     //     $sql = " SELECT
    //     //         CASE
    //     //             WHEN COUNT(*) > 0 THEN 'ne pas bloquer'
    //     //             ELSE 'bloquer'
    //     //         END AS retour
    //     //     FROM ors_soumis_a_validation
    //     //     WHERE numeroOR = '{$numOr}'
    //     //     AND numeroVersion = (
    //     //         SELECT MAX(numeroVersion)
    //     //         FROM ors_soumis_a_validation
    //     //         WHERE numeroOR = '{$numOr}'
    //     //     )
    //     //     AND (
    //     //         statut LIKE '%Validé%' OR
    //     //         statut LIKE '%Refusé%' OR
    //     //         statut LIKE '%Livré partiellement%' OR
    //     //         statut LIKE '%Modification demandée par client%'
    //     //     )
    //     // ";

    //     //return $this->retournerResult28($sql);
    //     // }
    // }

    public function constructeurPieceMagasin(string $numOr)
    {
        $statement = " SELECT
            CASE
                WHEN COUNT(CASE WHEN slor_constp = 'CAT' THEN 1 END) > 0
                AND COUNT(CASE WHEN slor_constp IN (" . GlobalVariablesService::get('pieceMagasinSansCat') . ") THEN 1 END) > 0
                THEN TRIM('CP')
            
                WHEN COUNT(CASE WHEN slor_constp = 'CAT' THEN 1 END) > 0
                AND COUNT(CASE WHEN slor_constp IN (" . GlobalVariablesService::get('pieceMagasinSansCat') . ") THEN 1 END) = 0
                THEN TRIM('C')

                WHEN COUNT(CASE WHEN slor_constp = 'CAT' THEN 1 END) = 0
                AND COUNT(CASE WHEN slor_constp IN (" . GlobalVariablesService::get('pieceMagasinSansCat') . ") THEN 1 END) = 0
                THEN TRIM('N')

                WHEN COUNT(CASE WHEN slor_constp = 'CAT' THEN 1 END) = 0
                AND COUNT(CASE WHEN slor_constp IN (" . GlobalVariablesService::get('pieceMagasinSansCat') . ") THEN 1 END) > 0
                THEN TRIM('P')
            END AS retour
        FROM sav_lor
        WHERE slor_numor = '" . $numOr . "'
            ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function countAgServDebit($numOr)
    {
        $statement = " SELECT count(distinct sitv_servdeb) as retour
                    from sav_itv 
                    where sitv_numor = '{$numOr}'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function getNumcli($numOr)
    {
        $statement = " SELECT seor_numcli as numcli
                    FROM sav_eor
                    WHERE seor_numor = '$numOr'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), 'numcli');
    }

    public function numcliExiste($numcli)
    {
        $statement = " SELECT  
        case
            when count(*) = 1 then 'existe_bdd' else ''
        end as numcli
        from cli_bse
        INNER JOIN cli_soc on csoc_numcli = cbse_numcli and csoc_soc = 'HF' where cbse_numcli ='$numcli' and cbse_numcli > 0
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), 'numcli');
    }
}