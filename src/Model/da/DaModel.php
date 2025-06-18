<?php

namespace App\Model\da;

use App\Model\Model;

class DaModel extends Model
{
    public function getAllFamille()
    {
        $statement = "SELECT distinct 
            trim(atab_code) as code, 
            trim(atab_lib) as libelle
            FROM agr_tab
            INNER JOIN art_bse ON abse_fams1 = atab_code
            WHERE abse_constp = 'ZST' and atab_nom = 'STA'";
        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return array_combine(array_column($data, 'libelle'), array_column($data, 'code'));
    }

    public function getAllSousFamille()
    {
        $statement = "SELECT DISTINCT 
                        TRIM(a.abse_fams2) AS code, 
                        TRIM(t.atab_lib) AS libelle
                    FROM art_bse a
                    INNER JOIN agr_tab t 
                        ON t.atab_nom = 'S/S' 
                        AND t.atab_code = a.abse_fams2
                    WHERE a.abse_constp = 'ZST' 
                    AND a.abse_fams1 IN (
                        SELECT DISTINCT TRIM(t2.atab_code) AS code
                        FROM agr_tab t2
                        INNER JOIN art_bse a2 
                            ON a2.abse_fams1 = t2.atab_code
                        WHERE a2.abse_constp = 'ZST' 
                        AND t2.atab_nom = 'STA'
                    )";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return array_combine(array_column($data, 'libelle'), array_column($data, 'code'));
    }

    public function getTheSousFamille($codeFamille)
    {
        $statement = "SELECT DISTINCT 
                    trim(abse_fams2) as code, 
                    trim(atab_lib) as libelle
                    FROM art_bse
                    INNER JOIN agr_tab ON atab_nom = 'S/S' AND atab_code = abse_fams2
                    WHERE abse_constp = 'ZST' AND abse_fams1 = '$codeFamille'";
        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }

    public function getLibelleFamille($codeFamille)
    {
        $statement = "SELECT DISTINCT TRIM(t.atab_lib) AS libelle
                FROM agr_tab t
                INNER JOIN art_bse a ON a.abse_fams1 = t.atab_code
                WHERE t.atab_code = '$codeFamille' 
                AND t.atab_nom = 'STA'
                AND a.abse_constp = 'ZST'
                LIMIT 1";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data[0]['libelle'] ?? ''; // Retourne '' si non trouvé
    }

    public function getLibelleSousFamille($codeSousFamille, $codeFamille)
    {
        $statement = "SELECT DISTINCT TRIM(t.atab_lib) AS libelle
                FROM art_bse a
                INNER JOIN agr_tab t ON t.atab_nom = 'S/S' AND t.atab_code = a.abse_fams2
                WHERE a.abse_constp = 'ZST' 
                AND a.abse_fams1 = '$codeFamille'
                AND a.abse_fams2 = '$codeSousFamille'
                LIMIT 1";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data[0]['libelle'] ?? ''; // Retourne '' si non trouvé
    }

    public function getAllDesignation($codeFamille, $codeSousFamille)
    {
        $statement = "SELECT 
            trim(abse_fams1) as codefamille,
            trim(abse_fams2) as codesousfamille,
            trim(abse_refp) as referencepiece,
            trim(abse_desi) as designation,
            abse_pxstd as prix,
            fbse_numfou as numerofournisseur,
            trim(fbse_nomfou) as fournisseur
            FROM art_frn
            INNER JOIN art_bse ON abse_refp = afrn_refp AND afrn_constp = abse_constp
            INNER JOIN frn_bse ON fbse_numfou = afrn_numf
            WHERE abse_constp = 'ZST'
            AND fbse_numfou in ('6001537','6001625')";
        if ($codeFamille !== '-') {
            $statement .= " AND abse_fams1 = '$codeFamille'";
            if ($codeSousFamille !== '-') {
                $statement .= " AND abse_fams2 = '$codeSousFamille'";
            }
        }
        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }

    public function getAllFournisseur()
    {
        $statement = "SELECT DISTINCT
            fbse_numfou as numerofournisseur,
            trim(fbse_nomfou) as nomfournisseur
            FROM art_frn
            INNER JOIN art_bse ON abse_refp = afrn_refp AND afrn_constp = abse_constp
            INNER JOIN frn_bse ON fbse_numfou = afrn_numf
            WHERE abse_constp = 'ZST'";
        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }

    public function getPrixUnitaire($referencePiece)
    {
        $statement = "SELECT 
            abse_pxstd as prix
            FROM art_bse
            WHERE abse_constp = 'ZST'
            and abse_refp = '$referencePiece'
            ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        if (empty(array_column($data, 'prix'))) {
            return ['0'];
        }

        return array_column($data, 'prix');
    }

    public function getSituationCde(?string $ref = '', string $numDit)
    {
        $statement = " SELECT DISTINCT
                slor_natcm,
                slor_refp,
                seor_refdem,
                CASE
                    when slor_natcm = 'C' then (select fcde_numcde from Informix.frn_cde where fcde_numcde = slor_numcf)
                    when slor_natcm = 'L' then (select distinct fcde_numcde from Informix.frn_cde inner join frn_llf on fllf_numcde = fcde_numcde and fllf_soc = fcde_soc and fllf_succ = fcde_succ and fllf_numliv = slor_numcf)
                END as num_cde,
                CASE
                    when slor_natcm = 'C' then (select fcde_posc from Informix.frn_cde where fcde_numcde = slor_numcf)
                    when slor_natcm = 'L' then (select distinct fcde_posc from Informix.frn_cde inner join frn_llf on fllf_numcde = fcde_numcde and fllf_soc = fcde_soc and fllf_succ = fcde_succ and fllf_numliv = slor_numcf)
                END as position_bc           
                FROM Informix.sav_lor
                INNER JOIN Informix.sav_eor on seor_numor = slor_numor and slor_soc = seor_soc and slor_succ = seor_succ and slor_soc = 'HF'
                INNER JOIN Informix.sav_itv on sitv_numor = slor_numor and slor_soc = sitv_soc and slor_succ = sitv_succ and slor_soc = 'HF'
                WHERE
                slor_constp = 'ZST' 
                and slor_refp <> 'ST'
                and slor_typlig = 'P'
                and slor_natcm in ('C', 'L')
                and slor_refp not like ('PREST%')
                and slor_refp = '$ref'
                and seor_refdem = '$numDit'
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }

    public function getAllConstructeur(string $numDit)
    {
        $statement = "SELECT DISTINCT slor_constp as constructeur
            FROM sav_lor
            INNER JOIN sav_eor on seor_numor = slor_numor and slor_soc = seor_soc and slor_succ = seor_succ and slor_soc = 'HF'
            where seor_refdem = '$numDit'
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return array_column($data, 'constructeur');
    }

    public function getEvolutionQte(string $numDit)
    {
        $statement = " SELECT
                TRIM(seor_refdem) as num_dit,
                ROUND(CASE
                    when slor_typlig = 'P' THEN (slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)
                END) as qte_dem,
                ROUND(slor_qterel) as qte_reliquat,
                ROUND(slor_qteres) as qte_a_livrer,
                ROUND(slor_qterea) as qte_livee

                FROM sav_lor
                INNER JOIN sav_eor on seor_numor = slor_numor and slor_soc = seor_soc and slor_succ = seor_succ and slor_soc = 'HF'
                INNER JOIN sav_itv on sitv_numor = slor_numor and slor_soc = sitv_soc and slor_succ = sitv_succ and slor_soc = 'HF'
                WHERE
                slor_constp = 'ZST' 
                and slor_refp <> 'ST'
                and slor_typlig = 'P'
                and slor_natcm in ('C', 'L')
                and slor_refp not like ('PREST%')
                and seor_refdem='$numDit'
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }
}
