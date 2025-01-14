<?php

namespace App\Model\dit;

use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Service\GlobalVariablesService;

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

    public function recupNbAchatLocaux(string $numDevis)
    {
        $statement = " SELECT
            count(slor.slor_constp) as nbr_achat_locaux 
            from sav_lor slor
            INNER JOIN sav_eor seor ON slor.slor_numor = seor.seor_numor
            where slor.slor_constp in (".GlobalVariablesService::get('achat_locaux').")
            and seor.seor_numor = '".$numDevis."'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupNbPieceMagasin(string $numDevis)
    {
        $statement = " SELECT
            count(slor.slor_constp) as nbr_sortie_magasin 
            from sav_lor slor
            INNER JOIN sav_eor seor ON slor.slor_numor = seor.seor_numor
            where slor.slor_constp in (".GlobalVariablesService::get('pieces_magasin').") 
            and slor.slor_typlig = 'P' 
            and seor.seor_numor = '".$numDevis."'
            ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    /**
     * 
     *
     * @param string $numDevis
     * @param boolean $estCeForfait
     * @return void
     */
    public function recupDevisSoumisValidation(string $numDevis, bool $estCeForfait)
    {
        
        $statement = "SELECT
            sitv_succdeb as SERV_DEBITEUR,  
            slor_numor,
            sitv_datdeb,
            trim(seor_refdem) as NUMERO_DIT,
            sitv_interv as NUMERO_ITV,
            trim(sitv_comment) as LIBELLE_ITV,
            trim(sitv_natop) as nature_opreration,
            count(slor_constp) as NOMBRE_LIGNE,
            Sum(
                CASE
                    WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
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
                    WHEN slor_typlig = 'P' AND slor_constp in (".GlobalVariablesService::get('pieces_magasin').") 
                    THEN (nvl (slor_qterel, 0) + nvl (slor_qterea, 0) + nvl (slor_qteres, 0) + nvl (slor_qtewait, 0) - nvl (slor_qrec, 0))
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
                    WHEN slor_constp in (".GlobalVariablesService::get('achat_locaux').") THEN (
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
                        AND slor_constp in (".GlobalVariablesService::get('lub').")
                    THEN (nvl (slor_qterel, 0) + nvl (slor_qterea, 0) + nvl (slor_qteres, 0) + nvl (slor_qtewait, 0) - nvl (slor_qrec, 0))
                END 
                * 
                CASE
                    WHEN slor_typlig = 'P' THEN slor_pxnreel
                    WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
                END
            ) AS MONTANT_LUBRIFIANTS,
            sum(
                CASE
                    WHEN slor_constp = 'ZDI' AND slor_refp = 'FORFAIT' AND sitv_natop = 'VTE'
                    THEN nvl((slor_pxnreel * slor_qtewait), 0)
                END
            ) AS MONTANT_FORFAIT

            from sav_eor, sav_lor, sav_itv
            WHERE seor_numor = slor_numor
            AND seor_serv = 'DEV'
            AND sitv_numor = slor_numor
            AND sitv_interv = slor_nogrp / 100
            AND seor_soc = 'HF'
            AND slor_soc=seor_soc
            AND sitv_soc=seor_soc
            AND sitv_pos NOT IN('FC', 'FE', 'CP', 'ST')
            AND seor_numor = '".$numDevis."'
            group by 1, 2, 3, 4, 5, 6, 7
            order by slor_numor, sitv_interv
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }


    public function recupNbrItvTypeVte($numDevis)
    {
        $statement = " SELECT COUNT(sitv_interv) as nb_vte
                    FROM sav_itv 
                    where sitv_natop = 'VTE' 
                    and sitv_numor = '".$numDevis."'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupNbrItvTypeCes($numDevis)
    {
        $statement = " SELECT COUNT(sitv_interv) as nb_ces
                    FROM sav_itv 
                    where sitv_natop = 'CES' 
                    and sitv_numor = '".$numDevis."'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupNumDitIps($numDevis)
    {
        $statement = " SELECT trim(seor_refdem) as num_dit
                    FROM sav_eor 
                    where seor_serv='DEV'
                    AND seor_numor = '".$numDevis."' 
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupServDebiteur($numDevis)
    {
        $statement = " SELECT sitv_succdeb as serv_debiteur
                        FROM sav_itv sitv 
                        inner join sav_eor seor on sitv.sitv_numor = seor.seor_numor and seor.seor_serv ='DEV'
                        WHERE seor.seor_numor = '".$numDevis."'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupInfoPieceClient(string $numDevis) 
    {
        $statement = " SELECT 
                        trim(slor_refp) as ref_piece,
                        trim(slor_constp) as constructeur,
                        slor_numcli as num_client,
                        slor_numor as num_devis
                        FROM sav_lor
                        WHERE slor_numor = '".$numDevis."'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    /**
     * Methode pour recupérer l'evolution de prix de chaque pièce
     *
     * @param array $infoPieceClient
     * @return void
     */
    public function recupInfoPourChaquePiece(array $infoPieceClient)
    {
        $statement = " SELECT FIRST 3 
                    slor_typlig as type_ligne,
                    trim(slor_constp) as CST, 
                    trim(slor_refp) as RefPiece, 
                    slor_datel as dateLigne, 
                    slor_pxnreel as prixVente 
                    FROM sav_lor 
                    WHERE slor_refp = '".$infoPieceClient['ref_piece'] ."'
                    and slor_constp = '".$infoPieceClient['constructeur']."'
                    and slor_numor = '".$infoPieceClient['num_devis']."'
                    and slor_natop = 'VTE' 
                    and slor_pos in ('CP','FC') 
                    and slor_numcli = '".$infoPieceClient['num_client']."'
                    and slor_constp in (".GlobalVariablesService::get('pieces_magasin').")
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }
}