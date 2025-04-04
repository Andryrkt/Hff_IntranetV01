<?php

namespace App\Model\cde;

use App\Model\Model;
use App\Model\Traits\ConversionModel;

class CdefnrSoumisAValidationModel extends Model
{
    use ConversionModel;

    public function recupListeFournissseur()
    {
        $statement=" SELECT  
                        FBSE_NUMFOU AS num_fournisseur,
                        UPPER(FBSE_NOMFOU) AS nom_fournisseur
                    FROM 
                        FRN_BSE
                    JOIN 
                        FRN_FOU ON FBSE_NUMFOU = FFOU_NUMFOU
                    WHERE 
                        FFOU_SOC = 'HF'
                        --AND FBSE_NOMFOU LIKE CONCAT('%', nomFournisseur, '%') pour le recherche
                    ORDER BY 
                        FBSE_NOMFOU;
        ";

        $result = $this->connect->executeQuery($statement);


        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupCdeFnrNonReceptionner($numFournisseur)
    {
        $statement =" SELECT
                FCDE_SUCC||FCDE_SERV  AS code_agence_service,
                TRIM(ASUC_LIB)||' - '||trim(ATAB_LIB) AS libelle_agence_service,
                FCDE_NUMCDE AS num_cde,
                DATE(FCDE_DATE) AS date_cde,
                TO_CHAR(FCDE_NUMFOU) AS num_fournisseur ,
                FBSE_NOMFOU AS nom_fournisseur,
                FCDE_LIB  AS libelle_cde,
                FCDE_TTC AS prix_cde_ttc,
                FCDE_TTC*FCDE_TXDEV AS prix_cde_ttc_devise,
                FCDE_DEVISE AS devise_cde,
                FCDE_TYPCDE AS type_cde
                FROM FRN_CDE, FRN_CDL, AGR_SUCC, AGR_TAB, FRN_BSE
                WHERE FCDE_SUCC = ASUC_NUM
                AND FCDE_NUMFOU = FBSE_NUMFOU
                AND ATAB_CODE = FCDE_SERV AND ATAB_NOM = 'SER'
                AND (FCDE_NUMCDE = FCDL_NUMCDE AND FCDE_SOC = FCDL_SOC AND FCDE_SUCC = FCDL_SUCC)
                AND FCDE_NUMCDE NOT IN (select FLLF_NUMCDE from FRN_LLF WHERE FLLF_SOC = FCDE_SOC AND FLLF_SUCC = FCDE_SUCC)
                --and date(FCDE_DATE) >= dateDebut 
                --and date(FCDE_DATE) <= dateFin
                and FCDE_NUMFOU = '".$numFournisseur."'
                AND FCDE_SOC = 'HF'
                AND FCDE_SERV IN ('NEG')
                AND (FCDE_TYPCDE <> 'CIS' OR (fcde_typcde = 'CIS' AND Length(to_char(fcde_numfou)) = 7))
                AND FCDE_NUMCDE IN (select FCDL_NUMCDE from FRN_CDL WHERE FCDE_SOC = FCDL_SOC)
                AND FCDE_TTC <> 0
                GROUP by 1,2,3,4,5,6,7,8,9,10,11

                UNION ALL

                SELECT
                FCDE_SUCC||FCDE_SERV  AS code_agence_service,
                TRIM(ASUC_LIB)||' - '||TRIM(ATAB_LIB) AS libelle_agence_service,
                FCDE_NUMCDE AS num_cde,
                DATE(FCDE_DATE) AS date_cde,
                TO_CHAR(FCDE_NUMFOU) AS num_fournisseur,
                FBSE_NOMFOU AS nom_fournisseur,
                FCDE_LIB AS libelle_cde,
                Sum(FCDL_SOLDE*FCDL_PXACH) AS prix_cde_ttc,
                Sum(FCDL_SOLDE*FCDL_PXACH*FCDE_TXDEV) AS prix_cde_ttc_devise,
                FCDE_DEVISE AS devise_cde,
                FCDE_TYPCDE AS type_cde
                FROM FRN_CDE, FRN_CDL, AGR_SUCC, AGR_TAB, FRN_BSE
                WHERE FCDE_SUCC = ASUC_NUM
                AND FCDE_NUMFOU = FBSE_NUMFOU
                AND ATAB_CODE = FCDE_SERV AND ATAB_NOM = 'SER'
                AND (FCDE_NUMCDE = FCDL_NUMCDE AND FCDE_SOC = FCDL_SOC AND FCDE_SUCC = FCDL_SUCC)
                AND FCDE_NUMCDE IN (select FLLF_NUMCDE from FRN_LLF WHERE FLLF_SOC = FCDE_SOC AND FLLF_SUCC = FCDE_SUCC)
                --and date(FCDE_DATE) >= dateDebut
                --and date(FCDE_DATE) <= dateFin
                and FCDE_NUMFOU = '".$numFournisseur."' 
                AND FCDE_SOC = 'HF'
                AND FCDE_SERV IN ('NEG')
                AND (FCDE_TYPCDE <> 'CIS' OR (fcde_typcde = 'CIS' AND Length(to_char(fcde_numfou)) = 7))
                AND FCDE_NUMCDE IN (select FCDL_NUMCDE from FRN_CDL WHERE FCDE_SOC = FCDL_SOC)
                AND (FCDL_QTE <> FCDL_QTELI AND FCDL_QTE <> 0 AND FCDL_SOLDE <> 0)
                GROUP by 1,2,3,4,5,6,7,10,11
                ORDER by 4, 3
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return $this->convertirEnUtf8($data);
    }

    public function recupListeInitialCdeFrn($numFournisseur, $numCde = "")
    {
        $numCde = !empty($numCde) ? " AND fcde_numcde = '".$numCde."'" : "";
        
        $statement = " SELECT
                fcde_numcde AS num_cde, 
                fcde_date AS date_cde,
                fcde_numfou AS num_fournisseur,
                fcde_lib AS libelle_cde, 
                fcde_ttc AS prix_cde_ttc,
                fcde_ttc*fcde_txdev AS prix_cde_ttc_devise, 
                fcde_devise AS devise_cde,
                fcde_typcde AS type_cde
                FROM frn_cde
                WHERE fcde_soc = 'HF'
                and fcde_succ = '01' and fcde_serv = 'NEG'
                and fcde_numcde not in (select fllf_numcde from frn_llf where fllf_soc = fcde_soc and fllf_succ = fcde_succ)
                and fcde_numfou = '".$numFournisseur."'
                $numCde
                and fcde_mtn <> 0
                order by fcde_date desc
        ";

            $result = $this->connect->executeQuery($statement);

            $data = $this->connect->fetchResults($result);

            return $this->convertirEnUtf8($data);
    }

    public function recupListeCdeFrn(string $numCde04)
    {
        $statement = " SELECT
                fcde_numcde AS num_cde, 
                fcde_date AS date_cde,
                fcde_numfou AS num_fournisseur,
                fcde_lib AS libelle_cde, 
                fcde_ttc AS prix_cde_ttc,
                TRUNC(fcdl_qte) AS nbr_piece,
                fcde_devise AS devise_cde,
                fcde_typcde AS type_cde,
                fcdl_constp AS constructeur,
                TRIM(fcdl_refp) AS ref_piece

                FROM frn_cde
	            JOIN frn_cdl ON fcde_numcde = fcdl_numcde and fcdl_soc = 'HF' and fcde_succ =fcdl_succ
                WHERE fcde_soc = 'HF'
                and fcde_succ = '01' and fcde_serv = 'NEG'
                and fcde_numcde not in (select fllf_numcde from frn_llf where fllf_soc = fcde_soc and fllf_succ = fcde_succ)
                and fcde_numcde in ({$numCde04})
                and fcde_mtn <> 0
                order by fcde_date desc
        ";

            $result = $this->connect->executeQuery($statement);

            $data = $this->connect->fetchResults($result);

            return $this->convertirEnUtf8($data);
    }

    public function recupNumCdeFrn(string $numCde04)
    {
        $statement = " SELECT
                fcde_numcde AS num_cde,
                fcde_numfou AS num_fournisseur

                FROM frn_cde
                WHERE fcde_soc = 'HF'
                and fcde_succ = '01' and fcde_serv = 'NEG'
                and fcde_numcde not in (select fllf_numcde from frn_llf where fllf_soc = fcde_soc and fllf_succ = fcde_succ)
                and fcde_numcde in ({$numCde04})
                and fcde_mtn <> 0
                order by fcde_date desc
        ";

            $result = $this->connect->executeQuery($statement);

            $data = $this->connect->fetchResults($result);

            return $this->convertirEnUtf8($data);
    }

    public function findsCde04()
    {
        $sqls = " SELECT Cust_ref from Ces_magasin where Eta_ivato = '1900-01-01' and Eta_magasin = '1900-01-01' ";

        return array_column($this->retournerResult04($sqls),'Cust_ref');
    }
}