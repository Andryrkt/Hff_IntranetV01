<?php

namespace App\Model\magasin\cdeFrn\soumission;

use App\Model\Model;
use App\Model\Informix\InsertQueryBuilder;
use App\Model\Informix\SelectWhereCondition;
use App\Dto\Magasin\cdeFrn\CommandeSoumissionDTO;
use App\Dto\Magasin\Commande\Soumission\BcSoumisMagasinDTO;
use App\Factory\magasin\cdeFrn\soumission\CommandeSoumissionFactory;

class CdeSoumissionModel extends Model
{
    private SelectWhereCondition $selectCond;

    public function __construct()
    {
        parent::__construct();
        $this->selectCond = new SelectWhereCondition();
    }

    /** 
     * Méthode pour retourner les infos sur la commande avec $numCde
     * 
     * @param string $numCde       numéro de la commande
     * @param string $userMail     email de l'utilisateur
     * @param string $succursale   succursale
     * @param string $codeSociete  code société
     * 
     * @return ?CommandeSoumissionDTO
     */
    public function findInfoCommande(string $numCde, string $userMail, string $succursale = '1', string $codeSociete = 'CO'): ?CommandeSoumissionDTO
    {
        $startDate = (new \DateTime('first day of -6 months'))->format("Ym");
        $endDate   = (new \DateTime('last day of last month'))->format("Ym");

        $statement = "SELECT 
            fcde_numcde as num_cde,
            fcde_date AS date_cde,
            (
                SELECT TRIM(atab_lib)
                FROM {$this->dbIps}.agr_tab
                WHERE atab_code = fcde_typcde AND atab_nom  = 'TOP'
            ) AS type_cde,
            fcde_numfou AS num_frn,
            (
                SELECT TRIM(fbse_nomfou)
                FROM {$this->dbIps}.frn_bse, {$this->dbIps}.frn_fou
                WHERE  fbse_numfou = fcde_numfou
                AND    fbse_numfou = ffou_numfou
                AND    ffou_soc    = fcde_soc
            ) AS nom_frn,
            (
                SELECT TRIM(asuc_lib)
                FROM {$this->dbIps}.agr_succ
                WHERE asuc_num = fcde_succ
            ) AS agence_lib,
            (
                SELECT TRIM(atab_lib)
                FROM {$this->dbIps}.agr_tab
                WHERE atab_nom  = 'SER' AND atab_code = fcde_serv
            ) AS service_lib,
            TRIM(fcdl_constp) AS cst,
            CASE TRIM(abse_libre1)
                WHEN 'A' THEN '(A)'
                ELSE '(B)'
            END AS av_bt,
            TRIM(fcdl_refp) AS refp,
            (
                SELECT afrn_cond
                FROM {$this->dbIps}.art_frn
                WHERE afrn_numf    = fcde_numfou
                AND   afrn_constp  = fcdl_constp
                AND   afrn_refp    = fcdl_refp
                AND   afrn_dated   = (
                    SELECT MAX(afrn_dated)
                    FROM {$this->dbIps}.art_frn
                    WHERE afrn_numf   = fcde_numfou
                    AND   afrn_constp = fcdl_constp
                    AND   afrn_refp   = fcdl_refp
                )
            ) AS package_qty,
            TRIM(fcdl_desi) AS desi,
            CASE NVL(
                (
                    SELECT SUM(astp_stock - astp_reserv)
                    FROM {$this->dbIps}.art_stp
                    WHERE astp_constp = fcdl_constp
                    AND astp_refp IN (
                        SELECT armp_ref
                        FROM {$this->dbIps}.art_rmp
                        WHERE armp_nivr   = 2
                        AND armp_constp = fcdl_constp
                        AND armp_refp   = fcdl_refp
                    )
                ), 0
            )   WHEN 0 THEN ''
                ELSE '(*)'
            END AS npr,
            TRIM(abse_libre2) AS fms,
            fcdl_qte AS qte_cde,
            (
                SELECT NVL(astp_stock - astp_reserv, 0)
                FROM {$this->dbIps}.art_stp
                WHERE astp_constp = fcdl_constp
                AND astp_refp   = fcdl_refp
                AND astp_succ   = '$succursale'
            ) AS stock_dispo,
            (
                SELECT astp_min1
                FROM {$this->dbIps}.art_stp
                WHERE astp_constp = fcdl_constp
                AND astp_refp   = fcdl_refp
                AND astp_succ   = '$succursale'
            ) AS stock_min,
            (
                SELECT astp_max1
                FROM {$this->dbIps}.art_stp
                WHERE astp_constp = fcdl_constp
                AND astp_refp   = fcdl_refp
                AND astp_succ   = '$succursale'
            ) AS stock_max,
            (
                SELECT NVL(SUM(asta_qtesor), 0)
                FROM {$this->dbIpsRegix}.art_sta
                WHERE asta_constp = fcdl_constp
                AND asta_refp   = fcdl_refp
                AND asta_per >= '$startDate'
                AND asta_per <= '$endDate'
            ) AS vte_der_mois,
            (
                SELECT NVL(SUM(asta_nblign), 0)
                FROM {$this->dbIpsRegix}.art_sta
                WHERE asta_constp = fcdl_constp
                AND asta_refp   = fcdl_refp
                AND asta_per >= '$startDate'
                AND asta_per <= '$endDate'
            ) AS nbr_vente,
            fcdl_pxach * (1 - (fcdl_txrem / 100)) AS prix_unit,
            fcdl_qte * fcdl_pxach * (1 - (fcdl_txrem / 100)) AS montant,
            fcdl_qte * abse_poids AS poids_total
        FROM {$this->dbIps}.frn_cdl, {$this->dbIps}.frn_cde, {$this->dbIps}.art_bse
        WHERE fcdl_numcde = fcde_numcde
            AND fcde_numcde = '$numCde'
            AND fcdl_constp = abse_constp
            AND fcdl_refp   = abse_refp
            AND fcde_soc    = fcdl_soc
            AND fcde_succ   = fcdl_succ
            AND fcde_soc    = '$codeSociete'
        ORDER BY fcdl_ref";

        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);

        if (empty($data)) return null;

        // --- Extraction des couples (cst, refp) distincts ---
        $pairs = [];
        foreach ($data as $row) {
            $cst  = trim($row['cst']);
            $refp = trim($row['refp']);
            $pairs["$cst|$refp"] = ['cst' => $cst, 'refp' => $refp];
        }

        // --- Requête 2 en une seule fois (batch) ---
        $detailsData = $this->findDetailsForPairs($numCde, $pairs);

        return (new CommandeSoumissionFactory)->hydrate($data, $detailsData, $userMail);
    }

    /** 
     * Méthode pour retourner en une seule requête tous les détails (SAV + VTE NEG) pour l'ensemble des couples (cst, refp) trouvés dans la requête de `findInfoCommande`.
     * 
     * @param string $numCde
     * @param array<string,array{cst:string,refp:string}> $pairs
     * 
     * @return array<string,array>
     */
    private function findDetailsForPairs(string $numCde, array $pairs): array
    {
        if (empty($pairs)) return [];

        // Regroupement des refp par cst (plus sargable qu'une concaténation)
        $byCst = [];
        foreach ($pairs as ['cst' => $cst, 'refp' => $refp]) {
            $byCst[$cst][] = $refp;
        }

        $conditionsOR = []; // conditions pour l'OR
        foreach ($byCst as $cst => $refps) {
            $conditionsOR[] = "(slor_constp = '$cst' {$this->selectCond->in('slor_refp',$refps)})";
        }
        $whereOr = implode(' OR ', $conditionsOR);

        $conditionsNeg = []; // conditiosn pour la vente NEG
        foreach ($byCst as $cst => $refps) {
            $conditionsNeg[] = "(nlig_constp = '$cst' {$this->selectCond->in('nlig_refp',$refps)})";
        }
        $whereOrNeg = implode(' OR ', $conditionsNeg);

        $statement = "SELECT 
            TRIM(slor_constp) AS cst, 
            TRIM(slor_refp)   AS refp,
            TRIM(seor_lib)    AS lib, 
            seor_numor        AS num_doc, 
            seor_numcli       AS num_cli, 
            TRIM(seor_nomcli) AS nom_cli,
            TRIM('OR')        AS rmq,
            CASE
                WHEN plan.min_start IS NULL 
                THEN TO_CHAR(sitv_datepla, '%Y-%m-%d')
                ELSE TO_CHAR(plan.min_start, '%Y-%m-%d')
            END AS datepla
        FROM {$this->dbIps}.sav_eor
        JOIN {$this->dbIps}.sav_lor
            ON seor_numor = slor_numor 
            AND slor_soc = seor_soc 
            AND slor_succ = seor_succ
        JOIN {$this->dbIps}.sav_itv
            ON sitv_numor = slor_numor
            AND sitv_interv = TRUNC(slor_nogrp / 100)
            AND sitv_soc = seor_soc 
            AND sitv_succ = seor_succ
        LEFT JOIN (
            SELECT ofh_id, ofs_id, MIN(ska_d_start) AS min_start
            FROM {$this->dbIps}.ska
                JOIN {$this->dbIps}.skw 
                ON skw.skw_id = ska.skw_id
            GROUP BY ofh_id, ofs_id
        ) plan ON plan.ofh_id = seor_numor AND plan.ofs_id = sitv_interv
        WHERE   slor_numcf  = '$numCde'
            AND slor_natcm  = 'C'
            AND seor_serv   = 'SAV'
            AND ({$whereOr})

        UNION

        SELECT
            TRIM(nlig_constp) AS cst, 
            TRIM(nlig_refp)   AS refp,
            TRIM(nent_refcde) AS lib,
            nent_numcde       AS num_doc, 
            nent_numcli       AS num_cli, 
            TRIM(nent_nomcli) AS nom_cli,
            TRIM('VTE NEG')   AS rmq, 
            CASE 
                WHEN nent_delai IS NOT NULL 
                THEN TO_CHAR(nent_delai, '%Y-%m-%d') 
                ELSE NULL 
            END AS datepla
        FROM {$this->dbIps}.neg_ent
        JOIN {$this->dbIps}.neg_lig 
            ON nent_numcde = nlig_numcde
        WHERE nlig_numcf  = '$numCde'
            AND ({$whereOrNeg})";

        $result = $this->connect->executeQuery($statement);
        $rows   = $this->connect->fetchResults($result);

        $grouped = [];
        foreach ($rows as $row) {
            $cst  = trim($row['cst']);
            $refp = trim($row['refp']);
            $grouped["$cst|$refp"][] = $row;
        }

        return $grouped;
    }

    /** 
     * Enregistrer du Bc soumis Magasin dans BD
     * 
     * @param BcSoumisMagasinDTO $bcSoumisMagasinDto
     * 
     * @return void
     */
    public function enregistrerBcSoumisMagasin(BcSoumisMagasinDTO $bcSoumisMagasinDto): void
    {
        // S'assurer que la connexion est ouverte
        $this->connect->connect();
        try {
            // Construire la requête d'insertion et l'exécuter
            $builder = new InsertQueryBuilder("{$this->dbIrium}.bc_soumis_magasin");
            $builder->setData([
                'numero_cde'                  => $bcSoumisMagasinDto->numeroCommande,
                'statut'                    => $bcSoumisMagasinDto->statut,
                'operateur'                 => $bcSoumisMagasinDto->operateur,
                'date_heure_soumission'       => $bcSoumisMagasinDto->dateHeureSoumission,
                'deposer_dw'                 => $bcSoumisMagasinDto->deposerDw,
            ]);

            $result = $builder->build();

            $this->connect->executeQuery($result['sql'], $result['params']);
        } finally {
            // ne fermez ici que si vous êtes sûr que c'est la dernière opération
            $this->connect->close();
        }
    }
}
