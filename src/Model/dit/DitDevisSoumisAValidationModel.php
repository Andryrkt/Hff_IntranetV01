<?php

namespace App\Model\dit;

use App\Model\Model;
use App\Model\Traits\ConversionModel;
use App\Service\GlobalVariablesService;

class DitDevisSoumisAValidationModel extends Model
{
    use ConversionModel;
    
    public function recupNumeroClient(string $numDevis)
    {
        $statement = " SELECT seor_numcli as numero_client
                        FROM sav_eor
                        WHERE seor_serv = 'DEV'
                        AND seor_numor = '".$numDevis."'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupNomClient(string $numDevis)
    {
        $statement = " SELECT TRIM(seor_nomcli) as nom_client
                        FROM sav_eor
                        WHERE seor_serv = 'DEV'
                        AND seor_numor = '".$numDevis."'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

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
                    SUM(slor.slor_constp) AS nbr_sortie_magasin
                FROM sav_lor slor
                INNER JOIN sav_eor seor ON slor.slor_numor = seor.seor_numor
                WHERE slor.slor_constp IN (".GlobalVariablesService::get('pieces_magasin').")
                AND slor.slor_typlig = 'P'
                AND seor.seor_numor = '$numDevis'
            ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function constructeurPieceMagasin(string $numDevis)
    {
        $statement = " SELECT
            CASE
                WHEN COUNT(CASE WHEN slor_constp = 'CAT' THEN 1 END) > 0
                AND COUNT(CASE WHEN slor_constp IN (".GlobalVariablesService::get('pieceMagasinSansCat').") THEN 1 END) > 0
                THEN TRIM('CP')
            
                WHEN COUNT(CASE WHEN slor_constp = 'CAT' THEN 1 END) > 0
                AND COUNT(CASE WHEN slor_constp IN (".GlobalVariablesService::get('pieceMagasinSansCat').") THEN 1 END) = 0
                THEN TRIM('C')

                WHEN COUNT(CASE WHEN slor_constp = 'CAT' THEN 1 END) = 0
                AND COUNT(CASE WHEN slor_constp IN (".GlobalVariablesService::get('pieceMagasinSansCat').") THEN 1 END) = 0
                THEN TRIM('N')

                WHEN COUNT(CASE WHEN slor_constp = 'CAT' THEN 1 END) = 0
                AND COUNT(CASE WHEN slor_constp IN (".GlobalVariablesService::get('pieceMagasinSansCat').") THEN 1 END) > 0
                THEN TRIM('P')
            END AS retour
        FROM sav_lor
        WHERE slor_numor = '".$numDevis."'
            ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    
    /**
     * Methode pour recupérer l'information du devis pour enregistrer dans le base de donnée
     *
     * @param string $numDevis
     * @param boolean $estCeForfait
     * @return void
     */
    public function recupDevisSoumisValidation(string $numDevis)
    {
        $statement = " SELECT sitv_succdeb as num_agence, slor_numor as numero_devis, sitv_datdeb, trim(seor_refdem) as numero_dit, sitv_interv as numero_itv, trim(sitv_comment) as libelle_itv, trim(sitv_natop) as nature_operation, trim(seor_devise) as devise, count(slor_constp) as nombre_ligne,
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
                    THEN (nvl(slor_qterel, 0) + nvl(slor_qterea, 0) + nvl(slor_qteres, 0) + nvl(slor_qtewait, 0) - nvl(slor_qrec, 0))
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
                    END 
                    *
                    CASE
                    WHEN slor_typlig = 'P' THEN slor_pxnreel
                    WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
                END
                ) AS MONTANT_MO,  Sum(
                    CASE
                        WHEN slor_constp in (".GlobalVariablesService::get('achat_locaux').") THEN (
                            slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec
                        )
                    END 
                    *
                    CASE
                    WHEN slor_typlig = 'P' THEN slor_pxnreel
                    WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
                END
                ) AS MONTANT_ACHATS_LOCAUX,  
            Sum(
                    CASE
                        WHEN slor_constp <> 'ZST'
                        AND slor_constp like 'Z%' THEN slor_qterea
                    END 
                    *
                    CASE
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
                ) AS MONTANT_FORFAIT,
            Sum(
                CASE
                    WHEN slor_constp<> 'ZDI' AND slor_refp <> 'FORFAIT' AND sitv_natop = 'VTE' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
                    WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_qterea
                END 
                * 
                CASE
                    WHEN slor_typlig = 'P' THEN slor_pxnreel
                    WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pxnreel
                END
            ) as MONTANT_VENTE,
            Sum(
                CASE
                    WHEN slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
                    WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_qterea
                END 
                * 
                CASE
                    WHEN slor_typlig = 'P' THEN slor_pmp
                    WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_pmp
                END
            ) as MONTANT_REVIENT
            
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
                AND seor_numor = ({$numDevis})
            
                GROUP BY 1, 2, 3, 4, 5, 6, 7, 8
                ORDER BY slor_numor, sitv_interv
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }




    public function recupConstRefPremDev(string $numDevis): array
    {
        $statement = " SELECT   TRIM(slor_constp||'-'|| slor_refp) as contructeur
                        FROM sav_lor
                        WHERE  slor_numor = '{$numDevis}' 
                        AND slor_nogrp = 100 
                        ORDER BY slor_nolign ASC
                        LIMIT 1
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupNbrItvDev(string $numDevis): array
    {
        $statement = " SELECT DISTINCT COUNT( slor_nogrp) as itv
                        FROM sav_lor 
                        WHERE slor_numor= '{$numDevis}' 
                        AND slor_nogrp != 100 
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
                    trim(slor_constp) as CST, 
                    trim(slor_refp) as RefPiece, 
                    slor_datel as dateLigne,
                    slor_pxnreel as prixVente,
                    slor_typlig as type_ligne,
                    seor_serv 
                    FROM sav_lor
                    inner join sav_eor 
                    on seor_soc= slor_soc and seor_succ = slor_succ and seor_numor = slor_numor and slor_soc ='HF'
                    WHERE slor_refp = '".$infoPieceClient['ref_piece'] ."'
                    and slor_constp in (".GlobalVariablesService::get('pieces_magasin').")
                    and seor_serv = 'SAV'
                    and slor_pos in('CP','FC') 
                    and slor_numcli = '".$infoPieceClient['num_client']."'
                    ORDER BY slor_datel DESC
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupNbrPieceMagasin($numDevis)
    {
        $statement = "SELECT count(slor_nolign)  as nbLigne
                        from sav_lor 
                        where slor_numor='{$numDevis}' 
                        and slor_constp in (".GlobalVariablesService::get('pieces_magasin').")
                    ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);           
    }
}