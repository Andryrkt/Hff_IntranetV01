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

    public function getTheSousFamille(string $codeFamille)
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

    public function getLibelleFamille(string $codeFamille)
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

    public function getLibelleSousFamille(string $codeSousFamille, string $codeFamille)
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

    public function getAllDesignationDaViaOR(string $codeFamille, string $codeSousFamille, string $codeSociete = "")
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
                    INNER JOIN art_soc asoc
                        ON asoc.asoc_soc = '$codeSociete' 
                        AND asoc.asoc_constp = a.abse_constp 
                        AND asoc.asoc_refp = a.abse_refp
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

    public function fetchDesignationDaDirect(string $codeSociete)
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
                INNER JOIN art_soc asoc
                    ON asoc.asoc_soc = '$codeSociete' 
                    AND asoc.asoc_constp = a.abse_constp 
                    AND asoc.asoc_refp = a.abse_refp
                WHERE (
                    a.abse_constp = 'ZDI' 
                    OR (
                        a.abse_constp='CAR' AND a.abse_refp in ('GO','SP95')
                        )
                    )
                AND a.abse_numf <> '99'
                ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }

    public function getAllFournisseur(string $codeSociete)
    {
        $statement = "SELECT DISTINCT
            fbse_numfou as numerofournisseur,
            trim(fbse_nomfou) as nomfournisseur
            FROM art_frn
            INNER JOIN art_bse ON abse_refp = afrn_refp AND afrn_constp = abse_constp
            INNER JOIN frn_bse ON fbse_numfou = afrn_numf
            INNER JOIN frn_fou ON ffou_numfou = afrn_numf and ffou_soc = '$codeSociete' and ffou_solv = 'ST'
            WHERE abse_constp = 'ZST'

            UNION

                SELECT distinct
            fbse_numfou as numerofournisseur,
            trim(fbse_nomfou) as nomfournisseur
            FROM frn_bse
            INNER JOIN frn_fou ON ffou_numfou = fbse_numfou and ffou_soc = '$codeSociete' and ffou_solv = 'ST'

            ORDER BY nomfournisseur
            ";
        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $data;
    }

    public function getAllArticleStocke(string $codeSociete)
    {
        $statement = "SELECT 
                        a.abse_constp AS constp,
                        TRIM(a.abse_refp) AS refp,
                        TRIM(a.abse_desi) AS designation,
                        af.afrn_numf AS numero_fournisseur,
                        TRIM(fbse_nomfou) AS nom_fournisseur,
                        CASE 
                            WHEN a.abse_pmp > 0 THEN a.abse_pmp
                            WHEN af.afrn_pxach > 0 THEN af.afrn_pxach
                            ELSE 0
                        END AS prix_unitaire
                    FROM art_bse a 
                    LEFT JOIN art_frn af 
                        ON afrn_constp = abse_constp 
                        AND afrn_refp = abse_refp
                    INNER JOIN art_soc 
                        ON asoc_soc = '$codeSociete' 
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

    /**
     * Récupérer les références autorisées
     */
    public function getAllReferenceAutorisees(string $codeSociete): array
    {
        $statement = "SELECT 
                        TRIM(abs.abse_refp) as reference, 
                        TRIM(abs.abse_constp) as constp,
                        TRIM(abs.abse_desi) as desi,
                        af.afrn_numf as num_frn, 
                        TRIM(fbse.fbse_nomfou) as nom_frn,
                        CASE 
                            WHEN TRIM(abs.abse_constp) = 'ZDI' THEN 
                                CASE 
                                    WHEN af.afrn_pxach > 0 THEN af.afrn_pxach
                                    ELSE NULL
                                END
                            ELSE
                                CASE
                                    WHEN abs.abse_pmp > 0 THEN abs.abse_pmp
                                    WHEN af.afrn_pxach > 0 THEN af.afrn_pxach
                                    ELSE 0
                                END
                        END AS prix_unitaire
                    FROM art_bse abs
                    LEFT JOIN art_frn af 
                        ON af.afrn_constp = abs.abse_constp 
                        AND af.afrn_refp = abs.abse_refp
                    INNER JOIN art_soc asoc 
                        ON asoc.asoc_constp = abs.abse_constp 
                        AND asoc.asoc_refp = abs.abse_refp
                    LEFT JOIN frn_bse fbse 
                        ON af.afrn_numf = fbse.fbse_numfou
                    WHERE (
                            abs.abse_constp IN ('ALI','BOI','CEN','FBU','HAB','OUT','ZDI','INF','MIN')
                            OR (
                                abs.abse_constp='CAR' AND abs.abse_refp in ('GO','SP95')
                            )
                        )
                        AND asoc.asoc_soc = '$codeSociete'
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

    public function getMontantBcDaDirect(string $numCde, string $codeSociete)
    {
        $statement = " SELECT fcde_mtn as montant_total 
                        from informix.frn_cde 
                        where fcde_numcde ='$numCde'
                        and fcde_soc = '$codeSociete'
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

    public function getInfoLivraison(string $numCde, string $codeSociete)
    {
        $statement = "SELECT distinct 
                        f.fllf_numliv AS num_liv, 
                        f.fllf_numcde AS num_cde,
                        f2.fliv_dateclot AS date_clot, 
                        TRIM(f2.fliv_livext) AS ref_fac_bl,
                        f2.fliv_mtn AS montant_fac_bl
                    from Informix.frn_llf f 
                    inner join Informix.frn_liv f2 on f.fllf_numliv = f2.fliv_numliv 
                where f.fllf_numcde = '$numCde' and f2.fliv_soc ='$codeSociete'
        ";
        $result = $this->connect->executeQuery($statement);
        $rows = $this->convertirEnUtf8($this->connect->fetchResults($result));

        // On réindexe directement par num_liv en une seule étape
        return array_column($rows, null, 'num_liv');
    }

    public function getHistoriqueLivraison(string $numCde)
    {
        $statement = "SELECT 
                        fllf_numliv as num_liv, 
                        (
                            select TRIM(fliv_livext) from Informix.frn_liv 
                            where fliv_soc = fcde_soc and fliv_numliv = fllf_numliv
                        ) as ref_fac_bl,  
                        (
                            select fliv_dateclot from Informix.frn_liv 
                            where fliv_soc = fcde_soc and fliv_numliv = fllf_numliv
                        ) as date_clot,
                        fllf_numfac as numero_facture_ips, 
                        sum(fllf_qteliv * fllf_pxach) as montant_fac_bl
                    from Informix.frn_cde, Informix.frn_llf 
                    where fcde_numcde = '$numCde'
                    and fcde_soc = fllf_soc
                    and fcde_numcde = fllf_numcde
                    group by 1,2,3,4
                    order by 1,3
        ";
        $result = $this->connect->executeQuery($statement);
        $rows = $this->convertirEnUtf8($this->connect->fetchResults($result));

        return $rows;
    }


    public function getInfoBC(string $numCde, string $codeSociete)
    {
        $statement = "SELECT 
                TRIM(fbse_nomfou) as nom_fournisseur, 
                fbse_numfou as num_fournisseur,
                -- TRIM(fbse_tel) as tel_fournisseur,        -- champ à ne pas afficher dans le PDF       
                -- TRIM(fbse_adr1) as adr1_fournisseur,      -- champ à ne pas afficher dans le PDF
                -- TRIM(fbse_adr2) as adr2_fournisseur,      -- champ à ne pas afficher dans le PDF
                -- TRIM(fbse_ptt) as ptt_fournisseur,        -- champ à ne pas afficher dans le PDF
                -- TRIM(fbse_adr4) as adr4_fournisseur,      -- champ à ne pas afficher dans le PDF      
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
            where fcde_numcde = '$numCde'
            and fcde_soc = '$codeSociete'";
        $result = $this->connect->executeQuery($statement);
        $data = $this->convertirEnUtf8($this->connect->fetchResults($result));
        return $data ? $data[0] : [];
    }
}
