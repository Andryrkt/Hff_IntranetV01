<?php

namespace App\Model\da;

use App\Model\Model;
use App\Entity\da\DemandeAppro;
use App\Constants\da\StatutBcConstant;
use App\Constants\da\StatutDaConstant;
use App\Constants\da\StatutOrConstant;
use App\Constants\da\StatutActionConstant;

class DaAfficherModel extends Model
{
    /**
     * Récupère le statut fiable (dernière version de DaAfficher) + classe de couleur d'affichage d'une DA,
     * ainsi que l'acteur et l'action à faire correspondants.
     *
     * @return array{action:string,statutDa:string,classStatutDa:string}
     */
    public function getStatutEtActionAffichage(string $numDa, string $demandeur): array
    {
        $action = [];
        $statutDa = $typeDa = $statutOr = $statutBc = null;

        $sql = "SELECT DISTINCT
                    da.da_type_id AS type_da,
                    da.statut_dal AS statut_da,
                    da.statut_or  AS statut_or,
                    da.statut_cde AS statut_bc
                FROM da_afficher da
                JOIN (
                    SELECT numero_demande_appro, max(numero_version) AS max_version
                    FROM da_afficher
                    GROUP BY numero_demande_appro
                ) m  ON da.numero_demande_appro = m.numero_demande_appro
                    AND da.numero_version = m.max_version
                WHERE da.numero_demande_appro = '$numDa'";

        $result = $this->connexion->query($sql);

        $firstRow = $lastRow = $rowPasDansOR = null;

        while ($row = odbc_fetch_array($result)) {
            $row = $this->convertDataSqlServerToUTF8($row);
            $firstRow ??= $row;
            $lastRow = $row;

            if (($row['statut_bc'] ?? null) === StatutBcConstant::STATUT_PAS_DANS_OR) {
                $rowPasDansOR = $row;
            }

            $isCasSimple = (int) $row['type_da'] != DemandeAppro::TYPE_DA_AVEC_DIT
                || $row['statut_da'] !== StatutDaConstant::STATUT_VALIDE
                || $row['statut_or'] !== StatutOrConstant::STATUT_VALIDE;

            if ($isCasSimple) {
                $chosenRow = $row;
                break;
            }
        }

        $chosenRow ??= $rowPasDansOR ?? $lastRow;

        $typeDa   = isset($chosenRow['type_da']) ? (int) $chosenRow['type_da'] : null;
        $statutDa = $chosenRow['statut_da'];
        $statutOr = $chosenRow['statut_or'];
        $statutBc = $chosenRow['statut_bc'];

        $action = StatutActionConstant::getAction($statutDa, $typeDa, $demandeur, $statutOr, $statutBc);

        return [
            'action'        => $action["action"] ?: "-",
            'statutDa'      => $statutDa,
            'classStatutDa' => StatutDaConstant::getCssClassDa($statutDa),
        ];
    }

    /**
     * Récupère la timeline complète d'une DA par étape.
     * Pour chaque étape il retourne le statut de la DA, sa date de création, date de demande, 
     * numéro d'or, statut d'or, date de mise à jour du statut d'or et statut de commande.
     *
     * @return array<int,array{statutDal:?string,dateCreation:?\DateTimeInterface,dateDemande:?\DateTimeInterface,numeroOr:?string,statutOr:?string,dateMajStatutOr:?\DateTimeInterface,statutCde:?string}>
     */
    public function getDaLifecycleSteps(string $numDa): array
    {
        $sql = "SELECT DISTINCT da.statut_dal, da.date_creation, da.date_demande, da.numero_or, da.statut_or, da.date_maj_statut_or, da.statut_cde
                FROM da_afficher da
                WHERE da.numero_demande_appro='$numDa'
                ORDER BY da.date_creation";

        $stmt = $this->connexion->query($sql);

        $results = [];
        while ($row = odbc_fetch_array($stmt)) {
            $row = $this->convertDataSqlServerToUTF8($row);

            $results[] = [
                'statutDal'       => $row['statut_dal'],
                'dateCreation'    => $row['date_creation'] ? new \DateTime($row['date_creation']) : null,
                'dateDemande'     => $row['date_demande'] ? new \DateTime($row['date_demande']) : null,
                'numeroOr'        => $row['numero_or'],
                'statutOr'        => $row['statut_or'],
                'dateMajStatutOr' => $row['date_maj_statut_or'] ? new \DateTime($row['date_maj_statut_or']) : null,
                'statutCde'       => $row['statut_cde']
            ];
        }

        return $results;
    }

    /**
     * Récupère, pour chaque numéro de BC de la dernière version d'une DA, les étapes clés
     * du cycle de vie du BC (génération IPS, validation DW, envoi fournisseur Intranet, réception, livraison) en une seule requête groupée.
     *
     * @return array<string,array{dateCreationBc:?\DateTimeInterface,dateValidationBc:?\DateTimeInterface,dateEnvoiFournisseur:?\DateTimeInterface,dateReceptionArticle:?\DateTimeInterface,dateDerniereReception:?\DateTimeInterface}>
     */
    public function getBcLifecycleSteps(string $numDa, int $daTypeId): array
    {
        $sql = "--sql
        WITH max_version_daf AS (
            SELECT numero_demande_appro, MAX(numero_version) AS max_version
            FROM da_afficher
            WHERE numero_demande_appro = '$numDa'
            GROUP BY numero_demande_appro
        ),
        max_version_cde AS (
            SELECT numero_bca, MAX(numero_version) AS max_version
            FROM DW_BC_Appro
            WHERE numero_da = '$numDa'
            GROUP BY numero_bca
        )
        SELECT
            da.numero_cde             AS numero_cde,
            dba.date_validation       AS date_validation_bc,
            da.date_envoi_fournisseur AS date_envoi_fournisseur
        FROM da_afficher da
        JOIN max_version_daf mdaf
            ON da.numero_demande_appro = mdaf.numero_demande_appro
        AND da.numero_version = mdaf.max_version
        LEFT JOIN max_version_cde mcde
            ON da.numero_cde = mcde.numero_bca
        LEFT JOIN DW_BC_Appro dba
            ON dba.numero_da = '$numDa'
        AND dba.numero_bca = mcde.numero_bca
        AND dba.numero_version = mcde.max_version
        WHERE da.numero_demande_appro = '$numDa'
            AND da.numero_cde IS NOT NULL
            AND da.numero_cde != ''
            AND da.deleted = 0
        ORDER BY da.numero_cde ASC";

        $result = $this->connexion->query($sql);

        $bcLifecycleIPS = $this->getBcLifecycleIPS($numDa, $daTypeId);

        $donnees = [];

        while ($row = odbc_fetch_array($result)) {
            $numCde = $row['numero_cde'];
            $bcInfo = $bcLifecycleIPS[$numCde] ?? null;

            $donnees[$numCde] = [
                'dateCreationBc'        => $bcInfo['date_creation_bc_ips'] ?? null,
                'dateValidationBc'      => $row['date_validation_bc']     ? new \DateTime($row['date_validation_bc'])     : null,
                'dateEnvoiFournisseur'  => $row['date_envoi_fournisseur'] ? new \DateTime($row['date_envoi_fournisseur']) : null,
                'dateReceptionArticle'  => $bcInfo['premiere_reception'] ?? null,
                'dateDerniereReception' => $bcInfo['derniere_reception'] ?? null,
            ];
        }

        return $donnees;
    }

    /**
     * Récupère, pour chaque numéro de BC de la dernière version d'une DA, les étapes clés
     * du cycle de vie du BC dans IPS.
     *
     * @return array<string,array{date_creation_bc_ips:?\DateTimeInterface,premiere_reception:?\DateTimeInterface,derniere_reception:?\DateTimeInterface}>
     */
    private function getBcLifecycleIPS(string $numDa, int $daTypeId): array
    {
        $statement = "--sql
        WITH cde_ips AS (
            SELECT
            fcde_numcde AS num_bc,
            fcde_date AS date_creation_bc_ips
            FROM {$this->dbIps}:Informix.frn_cde
            WHERE fcde_cdeext = '$numDa'
        ),
        livraisons AS (
            SELECT
            fllf_numcde AS num_bc,
            MIN(fliv_datel) AS premiere_reception,
            -- MAX(fliv_datel) AS derniere_reception
            MAX(fliv_dateclot) AS derniere_reception
            FROM {$this->dbIps}:Informix.frn_llf
            INNER JOIN {$this->dbIps}:Informix.frn_liv
            ON  fliv_numliv = fllf_numliv
            AND fliv_soc = fllf_soc
            AND fliv_succ = fllf_succ
            GROUP BY fllf_numcde
        )
        SELECT
            c.num_bc,
            c.date_creation_bc_ips,
            l.premiere_reception,
            l.derniere_reception
        FROM cde_ips c
        LEFT JOIN livraisons l ON c.num_bc = l.num_bc";

        $result = $this->connect->executeQuery($statement);
        $data = $this->connect->fetchResults($result);

        $donnees = [];

        foreach ($data as $row) {
            $donnees[$row['num_bc']] = [
                'date_creation_bc_ips' => $row['date_creation_bc_ips'] ? new \DateTime($row['date_creation_bc_ips']) : null,
                'premiere_reception'   => $row['premiere_reception'] ? new \DateTime($row['premiere_reception'])   : null,
                'derniere_reception'   => $row['derniere_reception'] ? new \DateTime($row['derniere_reception'])   : null,
            ];
        }

        return $donnees;
    }
}
