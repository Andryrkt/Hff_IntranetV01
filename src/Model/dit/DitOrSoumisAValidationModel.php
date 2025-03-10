<?php

namespace App\Model\dit;


use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Service\GlobalVariablesService;

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
        AND sitv_servcrt IN (
            'ATE',
            'FOR',
            'GAR',
            'MAN',
            'CSP',
            'MAS'
        )
        AND seor_numor = '".$numOr."'
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
                where seor_numor = '".$numOr."'"
                ;

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupNumeroOr($numDit)
    {
        $statement = " SELECT 
            seor_numor as numOr
            from sav_eor
            where seor_refdem = '".$numDit."'
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
            where seor_refdem = '".$numDit."'
            AND seor_numor = '".$numOr."'
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
        where sitv_numor = '".$numOr."' 
        and sitv_datepla is null";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupPositonOr($numor)
    {
        $statement = " SELECT seor_pos as position from sav_eor where seor_numor = '".$numor."'";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return  $this->convertirEnUtf8($data);
    }

    public function recupNbPieceMagasin($numOr)
    {
        $statement = " SELECT
            count(slor_constp) as nbr_sortie_magasin 
            from sav_lor 
            where slor_constp in (".GlobalVariablesService::get('pieces_magasin').") 
            and slor_typlig = 'P' 
            and slor_numor = '".$numOr."'
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
            where slor_constp in (".GlobalVariablesService::get('achat_locaux').")  
            and slor_numor = '".$numOr."'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupRefClient($numOr)
    {
        $statement =" SELECT seor_lib  
                    from sav_eor 
                    where seor_numor='".$numOr."'
                    ";
        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupBlockageStatut($numOr)
    {
        $sql = " SELECT
                case when count(statut) > 0 then 'bloquer' else 'ne pas bloquer' end as retour
            FROM ors_soumis_a_validation
            WHERE numeroOR = '{$numOr}'
            AND numeroVersion = (
                SELECT MAX(numeroVersion)
                FROM ors_soumis_a_validation
                WHERE numeroOR = '{$numOr}'
            )
            and statut not like ('%Validé%')
            and statut not like ('%Refusé%')
        ";

        return $this->retournerResult28($sql);
    }

    public function constructeurPieceMagasin(string $numOr)
    {
        $statement = " SELECT
            slor.slor_constp as constructeur
            from sav_lor slor
            INNER JOIN sav_eor seor ON slor.slor_numor = seor.seor_numor
            where slor.slor_constp in (".GlobalVariablesService::get('pieces_magasin').") 
            and slor.slor_typlig = 'P' 
            and seor.seor_numor = '".$numOr."'
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
}