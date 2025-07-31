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
            abse_numf as numerofournisseur,
            (
                SELECT trim(fbse_nomfou) 
                    FROM frn_bse 
                    WHERE fbse_numfou = a.abse_numf
            ) AS fournisseur,
            (
                SELECT c.afrn_pxach
                    FROM art_frn c 
                    WHERE c.afrn_refp = a.abse_refp 
                    AND c.afrn_numf = a.abse_numf
                    AND c.afrn_constp = a.abse_constp
                    AND c.afrn_dated = (
                        SELECT MAX(d.afrn_dated) 
                            FROM Informix.art_frn d 
                            WHERE d.afrn_refp = a.abse_refp 
                            AND d.afrn_numf = a.abse_numf
                            AND d.afrn_constp = a.abse_constp
                        )
            ) AS prix
                FROM art_bse a
                    WHERE abse_constp = 'ZST'                    
                    AND abse_refp <> 'ST' 
                    ";
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
        $statement = "SELECT c.afrn_pxach as prix
            FROM art_frn c
            INNER JOIN art_bse a 
                ON c.afrn_refp = a.abse_refp 
                AND c.afrn_numf = a.abse_numf
                AND c.afrn_constp = a.abse_constp
            WHERE c.afrn_dated = (
                SELECT MAX(d.afrn_dated) 
                FROM art_frn d 
                WHERE d.afrn_refp = a.abse_refp 
                AND d.afrn_numf = a.abse_numf
                AND d.afrn_constp = a.abse_constp
            )
            and a.abse_constp = 'ZST'
            and a.abse_refp = '$referencePiece'
            ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        if (empty(array_column($data, 'prix'))) {
            return ['0'];
        }

        return array_column($data, 'prix');
    }

    public function getSituationCde(?string $ref = '', string $numDit, string $numDa, ?string $designation = '', string $numOr)
    {
        $designation = str_replace("'", "''", mb_convert_encoding($designation, 'ISO-8859-1', 'UTF-8'));

        $statement = "SELECT DISTINCT
                        slor_natcm,
                        TRIM(slor_refp),
                        TRIM(seor_refdem),

                        CASE
                            WHEN slor_natcm = 'C' THEN c.fcde_numcde
                            WHEN slor_natcm = 'L' THEN cde.fcde_numcde
                        END AS num_cde,

                        CASE
                            WHEN slor_natcm = 'C' THEN c.fcde_posc
                            WHEN slor_natcm = 'L' THEN cde.fcde_posc
                        END AS position_bc

                    FROM Informix.sav_lor slor
                    INNER JOIN Informix.sav_eor seor 
                        ON seor.seor_numor = slor.slor_numor 
                    AND seor.seor_soc = slor.slor_soc 
                    AND seor.seor_succ = slor.slor_succ 
                    AND slor.slor_soc = 'HF'

                    INNER JOIN Informix.sav_itv sitv 
                        ON sitv.sitv_numor = slor.slor_numor 
                    AND sitv.sitv_soc = slor.slor_soc 
                    AND sitv.sitv_succ = slor.slor_succ 
                    AND slor.slor_soc = 'HF'

                    -- jointure pour natcm = 'C'
                    LEFT JOIN Informix.frn_cde c
                        ON slor.slor_natcm = 'C' AND c.fcde_numcde = slor.slor_numcf

                    -- jointure pour natcm = 'L'
                    LEFT JOIN Informix.frn_llf llf
                        ON slor.slor_natcm = 'L' 
                    AND llf.fllf_numliv = slor.slor_numcf

                    LEFT JOIN Informix.frn_cde cde
                        ON llf.fllf_numcde = cde.fcde_numcde
                    AND llf.fllf_soc = cde.fcde_soc
                    AND llf.fllf_succ = cde.fcde_succ

                    WHERE
                        slor.slor_constp = 'ZST' 
                        AND slor.slor_typlig = 'P'
                        AND slor.slor_refp NOT LIKE 'PREST%'
                        and slor_numor = '$numOr'
                        and TRIM(slor_refp) LIKE '%$ref%'
                                    and TRIM(slor.slor_desi) like '%$designation%'
                                    and seor.seor_refdem = '$numDit'
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

    public function getEvolutionQte(?string $numDit, string $numDa, string $ref = '', string $designation = '', string $numOr)
    {
        $designation = str_replace("'", "''", mb_convert_encoding($designation, 'ISO-8859-1', 'UTF-8'));

        $statement = " SELECT 
 
        slor_constp as cst,
        slor_natcm,
        TRIM(slor_refp) as reference,
                        TRIM(slor_desi) as designation,    
        ROUND(
                CASE
                    WHEN slor_typlig = 'P' THEN (
                        slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec
                    )
                END
            ) AS qte_dem,
            ROUND(CASE WHEN slor_typlig = 'P' THEN ( slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec)END) - (select sum(fllf_qteliv) from frn_llf l where fllf_ligne = slor.slor_noligncm and fllf_numcde = cde.fcde_numcde) as qte_reliquat,
            (select sum(fllf_qteliv) from frn_llf l where  l.fllf_numcde = cde.fcde_numcde and slor.slor_refp = l.fllf_refp and l.fllf_ligne = slor.slor_noligncm) as qte_receptionnee,
            slor_qterea as qte_livree,

        ROUND((select sum(fllf_qteaff) from frn_llf l where  l.fllf_numcde = cde.fcde_numcde and slor.slor_refp = l.fllf_refp and l.fllf_ligne = slor.slor_noligncm) - slor_qterea) as qte_dispo,
        
            CASE
                WHEN slor_natcm = 'C' THEN c.fcde_numcde
                WHEN slor_natcm = 'L' THEN cde.fcde_numcde
            END AS num_cde,
        
        slor_numcf
        FROM sav_lor slor
        
        INNER JOIN Informix.sav_eor seor 
                        ON seor.seor_numor = slor.slor_numor 
                    AND seor.seor_soc = slor.slor_soc 
                    AND seor.seor_succ = slor.slor_succ 
                    AND slor.slor_soc = 'HF'

        -- jointure pour natcm = 'C'
        LEFT JOIN Informix.frn_cde c
            ON slor.slor_natcm = 'C' 
            AND c.fcde_numcde = slor.slor_numcf
        
        -- jointure pour natcm = 'L'
        LEFT JOIN Informix.frn_llf llf
            ON slor.slor_natcm = 'L' 
            AND llf.fllf_numliv = slor.slor_numcf and slor.slor_noligncm = fllf_ligne
        
        LEFT JOIN Informix.frn_cde cde
            ON llf.fllf_numcde = cde.fcde_numcde
            AND llf.fllf_soc = cde.fcde_soc
            AND llf.fllf_succ = cde.fcde_succ

                    WHERE
                        slor.slor_constp = 'ZST' 
                        AND slor.slor_typlig = 'P'
                        AND slor.slor_refp NOT LIKE 'PREST%'
                        and slor_numor = '$numOr'
                        and seor.seor_refdem = '$numDit'
                        AND TRIM(slor.slor_refp) = '$ref'
                and TRIM(slor.slor_desi) = '$designation'
        ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }
}
