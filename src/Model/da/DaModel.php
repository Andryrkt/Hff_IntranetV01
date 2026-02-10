<?php

namespace App\Model\da;

use App\Model\Model;
use App\Service\GlobalVariablesService;

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

    public function getAllDesignationZST($codeFamille, $codeSousFamille)
    {
        $statement = "SELECT
                        TRIM(a.abse_fams1) AS codefamille,
                        TRIM(a.abse_fams2) AS codesousfamille,
                        TRIM(a.abse_refp) AS referencepiece,
                        TRIM(a.abse_desi) AS designation,
                        TRIM(f.fbse_nomfou) AS fournisseur,
                        a.abse_numf AS numerofournisseur,
                        fr.afrn_pxach AS prix
                    FROM art_bse a
                    LEFT JOIN frn_bse f
                        ON f.fbse_numfou = a.abse_numf
                    LEFT JOIN art_frn fr
                        ON fr.afrn_refp   = a.abse_refp
                        AND fr.afrn_numf   = a.abse_numf
                        AND fr.afrn_constp = a.abse_constp
                        AND fr.afrn_dated = (
                            SELECT MAX(d.afrn_dated)
                            FROM art_frn d
                            WHERE d.afrn_refp   = a.abse_refp
                                AND d.afrn_numf   = a.abse_numf
                                AND d.afrn_constp = a.abse_constp
                        )
                    WHERE a.abse_constp = 'ZST'
                    AND a.abse_refp <> 'ST'
                    AND a.abse_numf <> '99'
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

    public function getAllDesignationZDI()
    {
        $statement = "SELECT
                    TRIM(a.abse_fams1) AS codefamille,
                    TRIM(a.abse_fams2) AS codesousfamille,
                    TRIM(a.abse_refp) AS referencepiece,
                    TRIM(a.abse_desi) AS designation,
                    TRIM(f.fbse_nomfou) AS fournisseur,
                    a.abse_numf AS numerofournisseur
                FROM art_bse a
                LEFT JOIN frn_bse f
                    ON f.fbse_numfou = a.abse_numf
                WHERE a.abse_constp = 'ZDI'
                AND a.abse_numf <> '99'
                ";
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
            INNER JOIN frn_fou ON ffou_numfou = afrn_numf and ffou_soc = 'HF' and ffou_solv = 'ST'
            WHERE abse_constp = 'ZST'

            UNION

                SELECT distinct
            fbse_numfou as numerofournisseur,
            trim(fbse_nomfou) as nomfournisseur
            FROM frn_bse
            INNER JOIN frn_fou ON ffou_numfou = fbse_numfou and ffou_soc = 'HF' and ffou_solv = 'ST'
            WHERE fbse_categ = 'ALT'

            ORDER BY nomfournisseur
            ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }

    public function getAllArticleStocke()
    {
        $statement = "SELECT 
                        a.abse_constp AS constp,
                        TRIM(a.abse_refp) AS refp,
                        TRIM(a.abse_desi) AS designation,
                        af.afrn_numf AS numero_fournisseur,
                        TRIM(fbse_nomfou) AS nom_fournisseur,
                        af.afrn_pxach AS prix_unitaire 
                    FROM art_bse a 
                    LEFT JOIN art_frn af 
                        ON afrn_constp = abse_constp 
                        AND afrn_refp = abse_refp
                    INNER JOIN art_soc 
                        ON asoc_soc = 'HF' 
                        AND asoc_constp = a.abse_constp 
                        AND asoc_refp = a.abse_refp
                    LEFT JOIN frn_bse 
                        ON af.afrn_numf = fbse_numfou
                    WHERE a.abse_constp IN ('ALI','BOI','CEN','FBU','HAB','OUT','INF','MIN')
                        AND (af.afrn_dated = (
                                SELECT MAX(afrn_dated) 
                                FROM art_frn 
                                WHERE afrn_constp = a.abse_constp 
                                AND afrn_refp = a.abse_refp
                            )
                            OR af.afrn_dated is null
                        )
                    ORDER BY designation";
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

    public function getSituationCde(?string $ref = '', ?string $numDit, string $numDa, ?string $designation = '', ?string $numOr, ?string $statutBc)
    {
        if (!$numOr) return [];
        $designation = str_replace("'", "''", mb_convert_encoding($designation, 'ISO-8859-1', 'UTF-8'));

        $statutCde = [
            'A soumettre à validation',
            'A envoyer au fournisseur',
            'Partiellement dispo',
            'Complet non livré',
            'Tous livrés',
            'Partiellement livré',
            'BC envoyé au fournisseur'
        ];

        $statement = " SELECT DISTINCT
                        slor_natcm,
                        TRIM(slor_refp) as ref,
                        TRIM(slor_desi) as desi,
                        TRIM(seor_refdem) as num_dit,

                        CASE
                            WHEN slor_natcm = 'C' THEN c.fcde_numcde
                            WHEN slor_natcm = 'L' THEN cde.fcde_numcde
                        END AS num_cde,

                        CASE
                            WHEN slor_natcm = 'C' THEN c.fcde_posc
                            WHEN slor_natcm = 'L' THEN cde.fcde_posc
                        END AS position_bc,

                        CASE
                            WHEN slor_natcm = 'C' THEN c.fcde_posl
                            WHEN slor_natcm = 'L' THEN cde.fcde_posl
                        END AS position_livraison
                        
                    FROM Informix.sav_lor slor
                    INNER JOIN Informix.sav_eor seor 
                        ON seor.seor_numor = slor.slor_numor 
                    AND seor.seor_soc = slor.slor_soc 
                    AND seor.seor_succ = slor.slor_succ 
                    AND slor.slor_soc = 'HF'

                    --INNER JOIN Informix.sav_itv sitv 
                    --  ON sitv.sitv_numor = slor.slor_numor 
                    --AND sitv.sitv_soc = slor.slor_soc 
                    --AND sitv.sitv_succ = slor.slor_succ 
                    -- AND slor.slor_soc = 'HF'

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
                        slor.slor_constp ='ZST' 
                        AND slor.slor_typlig = 'P'
                        -- AND slor.slor_refp NOT LIKE 'PREST%' selon la demande hoby rahalahy 04/08/2025
                        and slor_numor = '$numOr'
                        and TRIM(REPLACE(REPLACE(slor_refp, '\t', ''), CHR(9), '')) LIKE '%$ref%'
                                    and TRIM(REPLACE(REPLACE(slor_desi, '\t', ''), CHR(9), '')) like '%$designation%'
                                    --and seor.seor_refdem = '$numDit'
            ";

        if ($statutBc && in_array($statutBc, $statutCde)) {
            $statement .= " AND (
                            (slor.slor_natcm = 'C' AND TRIM(REPLACE(REPLACE(c.fcde_cdeext, '\t', ''), CHR(9), '')) = '$numDa') 
                            OR (slor.slor_natcm = 'L' AND TRIM(REPLACE(REPLACE(cde.fcde_cdeext, '\t', ''), CHR(9), '')) = '$numDa')
                            )";
        }
        $statement .= " ORDER BY slor_natcm desc ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }

    public function getInfoDaDirect(string $numDa, ?string $ref = '', ?string $designation = '')
    {
        $designation = str_replace("'", "''", mb_convert_encoding($designation, 'ISO-8859-1', 'UTF-8'));

        $statement = " SELECT
                TRIM(fcde_cdeext) as num_da,
                fcde_numfou as num_fou,
                (select fbse_nomfou from informix.frn_bse where fbse_numfou = fcde_numfou) as nom_fou,
                fcde_numcde as num_cde,
                fcdl_constp as constructeur,
                TRIM(fcdl_refp) as ref,
                TRIM(fcdl_desi) as desi,
                fcde_posc as position_bc,
                fcde_posl as position_livraison,
                ROUND(fcdl_qte) as qte_dem,
                ROUND(fcdl_solde) as qte_en_attente,
                ROUND(fcdl_qtefa) as qte_livree,
                sum(fllf_qteliv) as qte_dispo

                FROM informix.frn_cde
                inner join informix.frn_cdl on fcdl_numcde = fcde_numcde
                LEFT join informix.frn_llf on fllf_numcde = fcdl_numcde and fllf_ligne = fcdl_ligne
                where fcdl_constp = 'ZDI'
                and TRIM(REPLACE(REPLACE(fcde_cdeext, '\t', ''), CHR(9), '')) = '$numDa'
                and TRIM(fcdl_refp) LIKE '%$ref%'
                and TRIM(fcdl_desi) like '%$designation%'
                GROUP BY fcde_cdeext,fcde_numfou,num_fou,fcde_numcde,fcdl_constp,fcdl_refp,fcdl_desi,fcde_posc,fcde_posl,qte_dem,qte_en_attente,qte_livree
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }

    public function getNumeroOrReappro(string $numDa): ?string
    {
        $statement = " SELECT seor_numor as num_or
                    from informix.sav_eor 
                    where seor_lib = '$numDa'
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data[0]['num_or'] ?? null;
    }

    // public function getAllConstructeur(string $numDit)
    // {
    //     $statement = "SELECT DISTINCT slor_constp as constructeur
    //         FROM sav_lor
    //         INNER JOIN sav_eor on seor_numor = slor_numor and slor_soc = seor_soc and slor_succ = seor_succ and slor_soc = 'HF'
    //         where seor_refdem = '$numDit'
    //     ";

    //     $result = $this->connect->executeQuery($statement);
    //     $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

    //     return array_column($data, 'constructeur');
    // }

    public function getEvolutionQteDaAvecDit(?string $numDit, string $ref = '', string $designation = '', ?string $numOr, $statutBc, string $numDa, bool $daReappro)
    {
        if (!$numOr) return [];

        $designation = str_replace("'", "''", mb_convert_encoding($designation, 'ISO-8859-1', 'UTF-8'));


        $statutCde = [
            'A soumettre à validation',
            'A envoyer au fournisseur',
            'Partiellement dispo',
            'Complet non livré',
            'Tous livrés',
            'Partiellement livré',
            'BC envoyé au fournisseur'
        ];

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
                (select sum(fllf_qteliv) from frn_llf l where  l.fllf_numcde = cde.fcde_numcde and slor.slor_refp = l.fllf_refp and l.fllf_ligne = slor.slor_noligncm and cde.fcde_cdeext like 'DAP%') as qte_receptionnee,
                    --slor_qterea as qte_livree,
                CASE 
                            WHEN  (slor_natcm is null or slor_natcm='') or slor_natcm = 'L' then slor_qterea
                            else 0
                        END as qte_livree,

                ROUND((select sum(fllf_qteaff) from frn_llf l where  l.fllf_numcde = cde.fcde_numcde and slor.slor_refp = l.fllf_refp and l.fllf_ligne = slor.slor_noligncm and cde.fcde_cdeext like 'DAP%') - slor_qterea) as qte_dispo,
                
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
                                slor.slor_typlig = 'P'
                                --AND slor.slor_refp NOT LIKE 'PREST%'
                                and slor_numor = '$numOr'
                                --and seor.seor_refdem = '$numDit'
                                AND TRIM(REPLACE(REPLACE(slor_refp, '\t', ''), CHR(9), '')) = '$ref'
                        and TRIM(REPLACE(REPLACE(slor_desi, '\t', ''), CHR(9), '')) = '$designation'
                        
                ";

        if ($statutBc && in_array($statutBc, $statutCde) && !$daReappro) {
            $statement .= " AND (
                    (slor.slor_natcm = 'C' AND TRIM(REPLACE(REPLACE(c.fcde_cdeext, '\t', ''), CHR(9), '')) = '$numDa') 
                    OR (slor.slor_natcm = 'L' AND TRIM(REPLACE(REPLACE(cde.fcde_cdeext, '\t', ''), CHR(9), '')) = '$numDa')
                    )";
        } elseif ($daReappro) {
            $statement .= " AND seor.seor_lib = '$numDa'
                AND slor.slor_constp  in (" . GlobalVariablesService::get('reappro') . ")
            ";
        }

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }

    public function getEvolutionQteDaDirect(string $numCde, string $ref = '', string $designation = '')
    {
        if (!$numCde) return [];

        $designation = str_replace("'", "''", mb_convert_encoding($designation, 'ISO-8859-1', 'UTF-8'));

        $statement = " SELECT  
                fcdl_constp as cst, 
                (
                    select fcde_numcde from frn_cde where fcde_numcde = c.fcdl_numcde
                ) as num_cde,
                TRIM(fcdl_refp) as reference,
                TRIM(fcdl_desi) as designation, 
                ROUND(fcdl_qte) as qte_dem,
                ROUND(fcdl_qteli) as qte_receptionnee,
                ROUND(fcdl_qtefa) as qte_livree
                    FROM frn_cdl c 
                WHERE fcdl_constp ='ZDI' 
                AND fcdl_numcde = '$numCde'
                AND fcdl_refp = '$ref'
                AND fcdl_desi = '$designation'
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }

    public function getNumOrValideZst(string $numOrString)
    {
        $statement = " SELECT DISTINCT slor_numor as num_or
                    from Informix.sav_lor 
                    where slor_constp ='ZST'
                    and slor_numor in ($numOrString)
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return array_column($data, 'num_or');
    }

    /**
     * Récupérer les références autorisées
     */
    public function getAllReferenceAutorisees(): array
    {
        $statement = "SELECT 
                        TRIM(abs.abse_refp) as reference, 
                        TRIM(abs.abse_constp) as constp,
                        TRIM(abs.abse_desi) as desi,
                        af.afrn_numf as num_frn, 
                        TRIM(fbse.fbse_nomfou) as nom_frn,
                        af.afrn_pxach as prix_unitaire 
                    FROM art_bse abs
                    LEFT JOIN art_frn af 
                        ON af.afrn_constp = abs.abse_constp 
                        AND af.afrn_refp = abs.abse_refp
                    INNER JOIN art_soc asoc 
                        ON asoc.asoc_constp = abs.abse_constp 
                        AND asoc.asoc_refp = abs.abse_refp
                    LEFT JOIN frn_bse fbse 
                        ON af.afrn_numf = fbse.fbse_numfou
                    WHERE abs.abse_constp IN ('ALI','BOI','CEN','FBU','HAB','OUT','ZDI','INF','MIN')
                        AND asoc.asoc_soc = 'HF'
                        AND (af.afrn_dated = (
                                SELECT MAX(afrn_dated) 
                                FROM art_frn 
                                WHERE afrn_constp = abs.abse_constp 
                                AND afrn_refp = abs.abse_refp
                            )
                            OR af.afrn_dated is null
                        )
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }

    /**
     * recupère le numéro et le nom du fournissuer
     * 
     * cette méthode utilise les tables frn_cdl et frn_bse pour recupérer le numéro et le nom du fournisseur
     * en utilisant comme jointure le numero du fournissuer
     * 
     */
    public function getNumAndNomFournisseurSelonReference(string $numCde, string $ref): array
    {
        $statement = " SELECT fcdl_numfou as num_fournisseur, 
                fbse_nomfou as nom_fournisseur
            from informix.frn_cdl 
            inner join informix.frn_bse on fcdl_numfou = fbse_numfou 
            where fcdl_numcde ='$numCde' and fcdl_refp ='$ref'
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }

    /**
     * recupère le numéro de ligne et le numéro d'intervention dans ips
     * 
     * @param string $ref
     * @param string $desi
     * @param string $numOr
     * @return array qui a un ou plusieurs éléments
     */
    public function getNumLigneAntItvIps(string $ref, string $desi, string $numOr): array
    {
        $statement = " SELECT 
                    slor_nogrp/100 as numero_intervention , 
                    slor_nolign as numero_ligne,
                    ROUND(
                        CASE
                            WHEN slor_typlig = 'P' THEN (
                                slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec
                            )
                        END
                    ) AS qte_dem
                    from informix.sav_lor 
                    where slor_numor ='$numOr' 
                    and slor_refp = '$ref' 
                    and slor_desi = '$desi'
                    order by qte_dem desc
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }

    public function getMontantBcDaDirect(string $numCde)
    {
        $statement = " SELECT fcde_mtn as montant_total 
                        from informix.frn_cde 
                        where fcde_numcde ='$numCde'
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data[0]['montant_total'] ?? 0;
    }

    public function getAllCodeCentrale()
    {
        $statement = "SELECT c.code_centrale AS code, c.designation_central AS desi FROM centrale_nrj c ";
        $resultStmt = $this->connexion->query($statement);
        $data = [];
        while ($result = odbc_fetch_array($resultStmt)) {
            $data[] = $this->convertirEnUtf8($result);
        }
        return $data;
    }

    public function getInfoLivraison(string $numCde)
    {
        $statement = "SELECT distinct 
                        f.fllf_numliv AS num_liv, 
                        f.fllf_numcde AS num_cde,
                        f2.fliv_dateclot AS date_clot, 
                        TRIM(f2.fliv_livext) AS ref_fac_bl,
                        f2.fliv_mtn AS montant_fac_bl
                    from Informix.frn_llf f 
                    inner join Informix.frn_liv f2 on f.fllf_numliv = f2.fliv_numliv 
                where f.fllf_numcde = '$numCde' and f2.fliv_soc ='HF'";
        $result = $this->connect->executeQuery($statement);
        $rows = $this->convertirEnUtf8($this->connect->fetchResults($result));

        // On réindexe directement par num_liv en une seule étape
        return array_column($rows, null, 'num_liv');
    }

    public function getInfoBC(string $numCde)
    {
        $statement = "SELECT 
                TRIM(fbse_nomfou) as nom_fournisseur, 
                fbse_numfou as num_fournisseur,
                TRIM(fbse_tel) as tel_fournisseur, 
                TRIM(fbse_adr1) as adr1_fournisseur, 
                TRIM(fbse_adr2) as adr2_fournisseur, 
                TRIM(fbse_ptt) as ptt_fournisseur, 
                TRIM(fbse_adr4) as adr4_fournisseur, 
                fcde_numcde as num_cde,
                fcde_date as date_cde,
                TRIM(fcde_succ) as succ_cde, 
                TRIM(fcde_serv) as serv_cde, 
                TRIM(fcde_ope) as nom_ope, 
                TRIM(fcde_cdeext) as num_cde_ext, 
                TRIM(fcde_lib) as libelle_cde, 
                fcde_mtn as mtn_cde,
                fcde_ttc as ttc_cde,
                TRIM(fcde_devise) as devise,
                TRIM(fcde_typcde) as type_cde 
            from frn_cde 
            inner join frn_bse on fbse_numfou = fcde_numfou
            where fcde_numcde = '$numCde'";
        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));
        return $data ? $data[0] : [];
    }

    /**
     * Recupère la date de creation d'une commande dans IPS
     *
     * @param integer $numcde
     * @return \DateTime|null
     */
    public function getdateCreationBc(int $numcde): ?\DateTime
    {
        $statement = "SELECT fcde_datec as date_creation_cde 
        from Informix.frn_cde 
        where fcde_numcde = '$numcde'";
        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));
        return !empty($data) ? new \DateTime(array_column($data, 'date_creation_cde')[0]) : null;
    }

    public function getDateReceptionArticle(int $numcde)
    {
        $statement = " SELECT  first 1 fliv_datec as date_reception_article
                    from frn_cdl, frn_llf, frn_liv
                    where (fcdl_numcde = fllf_numcde and fcdl_ligne = fllf_ligne and fcdl_soc = fllf_soc and fcdl_succ = fllf_succ)
                    and (fllf_soc = fliv_soc and fllf_succ = fliv_succ and fllf_numliv = fliv_numliv)
                    and fllf_numcde = '$numcde'
                    order by fliv_datec asc
        ";

        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return !empty($data) ? new \DateTime(array_column($data, 'date_reception_article')[0]) : null;
    }
}
