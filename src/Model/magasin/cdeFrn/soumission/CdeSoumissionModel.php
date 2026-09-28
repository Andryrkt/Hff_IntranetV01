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

    /*********************************************** 
     * Noms de tables utilisées 
     ***********************************************/
    private string $ipsAgrTab;
    private string $ipsFrnBse;
    private string $ipsFrnFou;
    private string $ipsAgrSucc;
    private string $ipsArtFrn;
    private string $ipsArtStp;
    private string $ipsArtRmp;
    private string $ipsArtSta;
    private string $ipsFrnCdl;
    private string $ipsFrnCde;
    private string $ipsArtBse;
    private string $ipsSavEor;
    private string $ipsSavLor;
    private string $ipsSavItv;
    private string $ipsSka;
    private string $ipsSkw;
    private string $ipsNegEnt;
    private string $ipsNegLig;

    public function __construct()
    {
        parent::__construct();
        $this->selectCond = new SelectWhereCondition();

        $this->ipsAgrTab  = "{$this->dbIps}:Informix.agr_tab";
        $this->ipsFrnBse  = "{$this->dbIps}:Informix.frn_bse";
        $this->ipsFrnFou  = "{$this->dbIps}:Informix.frn_fou";
        $this->ipsAgrSucc = "{$this->dbIps}:Informix.agr_succ";
        $this->ipsArtFrn  = "{$this->dbIps}:Informix.art_frn";
        $this->ipsArtStp  = "{$this->dbIps}:Informix.art_stp";
        $this->ipsArtRmp  = "{$this->dbIps}:Informix.art_rmp";
        $this->ipsArtSta  = "{$this->dbIps}:Informix.art_sta";
        $this->ipsFrnCdl  = "{$this->dbIps}:Informix.frn_cdl";
        $this->ipsFrnCde  = "{$this->dbIps}:Informix.frn_cde";
        $this->ipsArtBse  = "{$this->dbIps}:Informix.art_bse";
        $this->ipsSavEor  = "{$this->dbIps}:Informix.sav_eor";
        $this->ipsSavLor  = "{$this->dbIps}:Informix.sav_lor";
        $this->ipsSavItv  = "{$this->dbIps}:Informix.sav_itv";
        $this->ipsSka     = "{$this->dbIps}:Informix.ska";
        $this->ipsSkw     = "{$this->dbIps}:Informix.skw";
        $this->ipsNegEnt  = "{$this->dbIps}:Informix.neg_ent";
        $this->ipsNegLig  = "{$this->dbIps}:Informix.neg_lig";
    }

    /** 
     * Méthode pour retourner les infos sur la commande avec $numCde
     * 
     * @param string $numCde       numéro de la commande
     * @param string $userMail     email de l'utilisateur
     * @param string $codeSociete  code société
     * 
     * @return ?CommandeSoumissionDTO
     */
    public function findInfoCommande(string $numCde, string $userMail, string $codeSociete = 'HF'): ?CommandeSoumissionDTO
    {
        $startDate = (new \DateTime('first day of -6 months'))->format("Ym");
        $endDate   = (new \DateTime('last day of last month'))->format("Ym");

        $statement = "SELECT 
            fcde_numcde as num_cde,
            fcde_date AS date_cde,
            (
                SELECT TRIM(atab_lib)
                FROM {$this->ipsAgrTab}
                WHERE atab_code = fcde_typcde AND atab_nom  = 'TOP'
            ) AS type_cde,
            fcde_numfou AS num_frn,
            TRIM(fbse_nomfou) AS nom_frn,
            TRIM(fbse_devise) AS devise,
            (
                SELECT TRIM(asuc_lib)
                FROM {$this->ipsAgrSucc}
                WHERE asuc_num = fcde_succ
            ) AS agence_lib,
            (
                SELECT TRIM(atab_lib)
                FROM {$this->ipsAgrTab}
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
                FROM {$this->ipsArtFrn}
                WHERE afrn_numf    = fcde_numfou
                AND   afrn_constp  = fcdl_constp
                AND   afrn_refp    = fcdl_refp
                AND   afrn_dated   = (
                    SELECT MAX(afrn_dated)
                    FROM {$this->ipsArtFrn}
                    WHERE afrn_numf   = fcde_numfou
                    AND   afrn_constp = fcdl_constp
                    AND   afrn_refp   = fcdl_refp
                )
            ) AS package_qty,
            TRIM(fcdl_desi) AS desi,
            CASE NVL(
                (
                    SELECT SUM(astp_stock - astp_reserv)
                    FROM {$this->ipsArtStp}
                    WHERE astp_constp = fcdl_constp
                    AND astp_refp IN (
                        SELECT armp_ref
                        FROM {$this->ipsArtRmp}
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
                FROM {$this->ipsArtStp}
                WHERE astp_constp = fcdl_constp
                AND astp_refp   = fcdl_refp
                AND astp_succ   = '01'
            ) AS stock_dispo,
            (
                SELECT astp_min1
                FROM {$this->ipsArtStp}
                WHERE astp_constp = fcdl_constp
                AND astp_refp   = fcdl_refp
                AND astp_succ   = '01'
            ) AS stock_min,
            (
                SELECT astp_max1
                FROM {$this->ipsArtStp}
                WHERE astp_constp = fcdl_constp
                AND astp_refp   = fcdl_refp
                AND astp_succ   = '01'
            ) AS stock_max,
            (
                SELECT NVL(SUM(asta_qtesor), 0)
                FROM {$this->ipsArtSta}
                WHERE asta_constp = fcdl_constp
                AND asta_refp   = fcdl_refp
                AND asta_per >= '$startDate'
                AND asta_per <= '$endDate'
            ) AS vte_der_mois,
            (
                SELECT NVL(SUM(asta_nblign), 0)
                FROM {$this->ipsArtSta}
                WHERE asta_constp = fcdl_constp
                AND asta_refp   = fcdl_refp
                AND asta_per >= '$startDate'
                AND asta_per <= '$endDate'
            ) AS nbr_vente,
            fcdl_pxach * (1 - (fcdl_txrem / 100)) AS prix_unit,
            fcdl_qte * fcdl_pxach * (1 - (fcdl_txrem / 100)) AS montant,
            fcdl_qte * abse_poids AS poids_total
        FROM {$this->ipsFrnCde}, {$this->ipsFrnCdl}, {$this->ipsArtBse}, {$this->ipsFrnBse}, {$this->ipsFrnFou}
        WHERE   fcde_numcde = '$numCde'
            AND fcde_soc    = '$codeSociete'
            AND fcde_numcde = fcdl_numcde
            AND fcde_soc    = fcdl_soc
            AND fcde_succ   = fcdl_succ
            AND fcdl_constp = abse_constp
            AND fcdl_refp   = abse_refp
            AND fcde_numfou = fbse_numfou
            AND fbse_numfou = ffou_numfou
            AND fcde_soc    = ffou_soc
        ORDER BY fcdl_ref";

        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);

        if (empty($data)) return null;

        $pairs = [];
        foreach ($data as $row) {
            $cst  = trim($row['cst']);
            $refp = trim($row['refp']);
            $pairs["$cst|$refp"] = ['cst' => $cst, 'refp' => $refp];
        }

        $detailsParPiece = $this->findLignesSavEtVenteNegoceParPieces($numCde, $pairs);

        return (new CommandeSoumissionFactory)->hydrate($data, $detailsParPiece["lignesParPiece"], $detailsParPiece["ordresReparationValides"], $userMail);
    }

    /**
     * Retourne, en une seule requête, toutes les lignes liées à une commande fournisseur
     * (ordres de réparation SAV + ventes négoce) pour un ensemble de pièces.
     *
     * Une pièce est identifiée par le couple (constructeur, référence).
     *
     * @param string $numCde  Numéro de la commande fournisseur
     * @param array<string,array{cst:string,refp:string}> $piecesRecherchees Couples (cst, refp) issus de `findInfoCommande`
     *
     * @return array{ordresReparationValides:list<string>,lignesParPiece:array<string,list<array>>}
     */
    private function findLignesSavEtVenteNegoceParPieces(string $numCde, array $piecesRecherchees): array
    {
        if (empty($piecesRecherchees)) return ['ordresReparationValides' => [], 'lignesParPiece' => []];

        $refsParConstructeur = [];
        foreach ($piecesRecherchees as ['cst' => $cst, 'refp' => $refp]) {
            $refsParConstructeur[$cst][] = $refp;
        }

        $conditionsSav = [];
        foreach ($refsParConstructeur as $cst => $refps) {
            $conditionsSav[] = "(slor_constp = '$cst' {$this->selectCond->in('slor_refp',$refps)})";
        }
        $whereSav = implode(' OR ', $conditionsSav);

        $conditionsNegoce = [];
        foreach ($refsParConstructeur as $cst => $refps) {
            $conditionsNegoce[] = "(nlig_constp = '$cst' {$this->selectCond->in('nlig_refp',$refps)})";
        }
        $whereNegoce = implode(' OR ', $conditionsNegoce);

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
        FROM {$this->ipsSavEor}
        JOIN {$this->ipsSavLor}
            ON seor_numor = slor_numor 
            AND slor_soc = seor_soc 
            AND slor_succ = seor_succ
        JOIN {$this->ipsSavItv}
            ON sitv_numor = slor_numor
            AND sitv_interv = TRUNC(slor_nogrp / 100)
            AND sitv_soc = seor_soc 
            AND sitv_succ = seor_succ
        LEFT JOIN (
            SELECT ofh_id, ofs_id, MIN(ska_d_start) AS min_start
            FROM {$this->ipsSka}
                JOIN {$this->ipsSkw} 
                ON skw.skw_id = ska.skw_id
            GROUP BY ofh_id, ofs_id
        ) plan ON plan.ofh_id = seor_numor AND plan.ofs_id = sitv_interv
        WHERE   slor_numcf  = '$numCde'
            AND slor_natcm  = 'C'
            AND seor_serv   = 'SAV'
            AND ({$whereSav})

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
        FROM {$this->ipsNegEnt}
        JOIN {$this->ipsNegLig}
            ON nent_numcde = nlig_numcde
        WHERE nlig_numcf  = '$numCde'
            AND ({$whereNegoce})";

        $result = $this->connect->executeQuery($statement);
        $rows   = $this->connect->fetchResults($result);

        $lignesParPiece = [];
        $numerosOR      = [];

        foreach ($rows as $row) {
            $cst  = trim($row['cst']);
            $refp = trim($row['refp']);
            $lignesParPiece["$cst|$refp"][] = $row;

            if (trim($row['rmq']) === 'OR') $numerosOR[] = $row['num_doc'];
        }

        return [
            'ordresReparationValides' => $this->filterOrdresReparationValides($numerosOR),
            'lignesParPiece'          => $lignesParPiece,
        ];
    }

    /**
     * Filtre une liste de numéros d'OR pour ne conserver que ceux dont la
     * DERNIÈRE version soumise à validation a le statut « Validé ».
     *
     * @param list<string> $numerosOR Numéros d'ordres de réparation à contrôler
     *
     * @return list<string> ORs validés (liste vide si aucun)
     */
    private function filterOrdresReparationValides(array $numerosOR): array
    {
        if (empty($numerosOR)) return [];

        $sql = "WITH derniere_version AS (
                SELECT numeroOR, MAX(numeroVersion) AS max_version 
                FROM ors_soumis_a_validation
                GROUP BY numeroOR
            )
            SELECT osav.numeroOR 
            FROM ors_soumis_a_validation osav
            INNER JOIN derniere_version dv 
                ON osav.numeroOR = dv.numeroOR
                AND osav.numeroVersion = dv.max_version
            WHERE osav.statut = 'Validé' {$this->selectCond->in('osav.numeroOR',$numerosOR)}";

        return $this->retournerResult28($sql);
    }
}
