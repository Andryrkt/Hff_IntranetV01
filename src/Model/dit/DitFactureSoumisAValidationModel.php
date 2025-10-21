<?php

namespace App\Model\dit;

use App\Model\Model;
use App\Service\GlobalVariablesService;
use App\Controller\Traits\ConversionTrait;

class DitFactureSoumisAValidationModel extends Model
{
    use ConversionTrait;


    public function recupTypeFacture($numFac)
    {
        $statement = "SELECT slor_typeor  
                    FROM sav_lor 
                    WHERE slor_numfac = '" . $numFac . "'
        ";


        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), 'slor_typeor');
    }

    public function recupQterea($numFac)
    {
        $statement = "SELECT  slor_qterea 
                    FROM sav_lor 
                    WHERE slor_numfac = '" . $numFac . "'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), 'slor_qterea');
    }

    public function recupNumeroSoumission($numOr)
    {
        $sql = "SELECT COALESCE(MAX(numero_soumission)+1, 1) AS numSoumissionEncours
                FROM facture_soumis_a_validation
                WHERE numero_or = '" . $numOr . "'";

        $exec = $this->connexion->query($sql);
        $result = odbc_fetch_array($exec);

        return $result['numSoumissionEncours'];
    }

    /*
    public function recupStatut($numOr, $numItv)
    {
        $sql = "SELECT statut 
        FROM ors_soumis_a_validation 
        WHERE numeroVersion IN (SELECT MAX(numeroVersion) FROM ors_soumis_a_validation WHERE numeroOR = '".$numOr."') 
        AND numeroOR = '".$numOr."'
        AND numeroItv = '".$numItv."'";
            
        $exec = $this->connexion->query($sql);
        $result = odbc_fetch_array($exec);

        return $result['statut'];
    }
*/
    public function recupInfoFact($numOR, $numFact)
    {
        $statement = " SELECT
                    slor_numfac AS numeroFac, 
                    slor_numor AS numeroOr, 
                    ROUND(slor_nogrp / 100) AS numeroItv,
                    SUM(slor_pxnreel * slor_qterea) AS montantFactureItv,
                    slor_succdeb AS agenceDebiteur,
                    slor_servdeb AS serviceDebiteur,
                    TRIM(sitv_comment) AS libelleItv,
                    SUM(
                        CASE
                            WHEN slor_typlig = 'P' THEN (
                                slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec
                            )
                            WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_qterea
                        END * slor_pxnreel
                    ) AS montantItv
                FROM
                    sav_lor
                JOIN
                    sav_itv ON sitv_numor = slor_numor
                        AND sitv_interv = slor_nogrp / 100
                WHERE
                slor_numor = '" . $numOR . "'
                    AND slor_numfac = '" . $numFact . "'
                    --AND sitv_servcrt IN ('ATE', 'FOR', 'GAR', 'MAN', 'CSP', 'MAS', 'LR6', 'LST') 
                GROUP BY
                    slor_numfac, slor_numor, numeroItv, slor_succdeb, slor_servdeb, libelleItv
                ORDER BY
                    numeroItv;
            ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupEtatOr($numOr)
    {
        $statement = " SELECT 
                CASE 
                    WHEN COUNT(*) > 0 THEN 'PF'
                    ELSE 'CF'
                END AS etat_facturation_or
            FROM sav_lor
            WHERE slor_numor = '" . $numOr . "' 
            AND NVL(slor_numfac, 0) = 0 ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), 'etat_facturation_or');
    }

    public function recupOrSoumisValidation($numOr, $numFact)
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
        --AND sitv_servcrt IN ('ATE','FOR','MAN','GAR','CSP','MAS', 'LR6', 'LST')
        AND seor_numor = '" . $numOr . "'
        AND slor_numfac = '" . $numFact . "'
        --AND SEOR_SUCC = '01'
        group by 1, 2, 3, 4, 5
        order by slor_numor, sitv_interv
    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupNombreFacture($numOr, $numFact)
    {
        $statement = "SELECT count(slor_numfac) as nbFact 
                    FROM sav_lor where slor_numor = '" . $numOr . "'
                    AND slor_numfac = '" . $numFact . "'
                    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupNumeroItv($numOr, $numFact)
    {
        $statement = "SELECT
                    slor_nogrp / 100 AS numeroItv
                FROM
                    sav_lor
                JOIN
                    sav_itv ON sitv_numor = slor_numor
                            AND sitv_interv = slor_nogrp / 100
                WHERE
                    --sitv_servcrt IN ('ATE', 'FOR', 'GAR', 'MAN', 'CSP', 'MAS', 'LR6', 'LST')
                     slor_numor = '" . $numOr . "'
                    AND slor_numfac = '" . $numFact . "'
                GROUP BY
                numeroOr, numeroItv
                ORDER BY
                    numeroItv
        ";
        $result = $this->connect->executeQuery($statement);

        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return array_column($data, 'numeroItv');
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

    public function recuperationStatutItv($numOr, $numItv)
    {
        // $statement = " SELECT 
        //         trim(seor_refdem) as referenceDIT,
        //         seor_numor as numeroOr,
        //         TRUNC(sum(CASE WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) 
        //                         WHEN slor_typlig IN ('F','M','U','C') THEN slor_qterea 
        //                 END)) AS quantiteDemander,
        //         TRUNC(sum(slor_qteres)) as quantiteReserver,
        //         CASE 
        //             WHEN slor_typlig  IN ('F','M','U','C') THEN TRUNC(sum(slor_qterea)) ELSE TRUNC(sum(sliv_qteliv)) END 
        //         as quantiteLivree,
        //         TRUNC(sum(slor_qterel)) as quantiteReliquat
        //         from sav_lor 
        //         inner join sav_eor on seor_soc = slor_soc and seor_succ = slor_succ 
        //         and seor_numor = slor_numor
        //         left join sav_liv on sliv_soc = slor_soc and sliv_succ = slor_succ and sliv_numor = seor_numor and slor_nolign = sliv_nolign

        //         where 
        //         slor_soc = 'HF'
        //         --and slor_succ = '01'
        //         --and slor_typlig = 'P'
        //         and seor_serv ='SAV'
        //         and slor_constp in (".GlobalVariablesService::get('tous').")
        //         and slor_numor = '".$numOr."'
        //         and TRUNC(slor_nogrp/100) in (".$numItv.")
        //         group by 1,2
        // ";

        $statement = " SELECT 
                    TRIM(seor_refdem) AS referenceDIT,
                    seor_numor AS numeroOr,
                    TRUNC(SUM(
                        CASE 
                            WHEN slor_typlig = 'P' 
                            THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec) 
                            WHEN slor_typlig IN ('F', 'M', 'U', 'C') 
                            THEN slor_qterea 
                        END
                    )) AS quantiteDemander,
                    TRUNC(SUM(slor_qteres)) AS quantiteReserver,
                    TRUNC(SUM(
                        CASE 
                            WHEN slor_typlig IN ('F', 'M', 'U', 'C') 
                            THEN slor_qterea 
                            ELSE sliv_qteliv 
                        END
                    )) AS quantiteLivree,
                    TRUNC(SUM(slor_qterel)) AS quantiteReliquat
                FROM sav_lor 
                INNER JOIN sav_eor 
                    ON seor_soc = slor_soc 
                    AND seor_succ = slor_succ 
                    AND seor_numor = slor_numor
                LEFT JOIN sav_liv 
                    ON sliv_soc = slor_soc 
                    AND sliv_succ = slor_succ 
                    AND sliv_numor = seor_numor 
                    AND slor_nolign = sliv_nolign
                WHERE 
                    slor_soc = 'HF'
                    AND seor_serv = 'SAV'
                    --AND slor_constp IN (" . GlobalVariablesService::get('tous') . ")
                    AND slor_numor = '" . $numOr . "'
                    AND TRUNC(slor_nogrp / 100) IN (" . $numItv . ")
                GROUP BY 
                    1,2
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function orStatutEstValide($numOr, $numItv)
    {
        $sql = " SELECT 
                case when statut = 'Validé' then 'Validé'else 'Non validé' end as Statut
                from ors_soumis_a_validation
                where numeroOR = '$numOr' 
                and numeroItv = '$numItv' 
                and numeroVersion = (select max(numeroversion) from ors_soumis_a_validation where numeroOR = '$numOr' and numeroItv = '$numItv')
        ";

        $exec = $this->connexion->query($sql);
        $tab = [];
        while ($result = odbc_fetch_array($exec)) {
            $tab[] = $result;
        }
        return array_column($tab, 'Statut');
    }
}
