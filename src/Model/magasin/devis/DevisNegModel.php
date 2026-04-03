<?php

namespace App\Model\magasin\devis;

use App\Model\Model;
use App\Service\GlobalVariablesService;


class DevisNegModel extends Model
{
    public function getDevisNeg($criteria, $codeAgenceAutoriserString, $numDeviAExclure, $codeSociete, $page = 1, $limit = 100)
    {
        $this->connect->connect();
        $skip = ($page - 1) * $limit;

        try {

            $statement = "SELECT SKIP $skip FIRST $limit
                nent.nent_datecde                                           AS date_cde_brute
                ,dneg.statut_dw                                             AS statut_dw
                ,dneg.statut_bc                                             AS statut_bc
                ,nent.nent_numcde                                           AS numero_devis
                ,TO_CHAR(nent.nent_datecde, '%d/%m/%Y')                     AS date_creation
                ,nent.nent_succ || ' - ' || nent_servcrt                    AS emetteur
                ,nent.nent_numcli || ' - ' || nent_nomcli                   AS client
                ,TRIM(nent.nent_refcde)                                     AS reference_client
                ,nent.nent_cdeht                                            AS montant_devis
                ,TO_CHAR(dneg.date_envoye_devis_client, '%d/%m/%Y')         AS date_envoye_devis_au_client
                ,dneg.stop_progression_global                               AS stop_progression_global
                ,dneg.motif_stop_global                                     AS motif_stop_global

                -- Pour statut_relance_1
                ,CASE
                    WHEN rl.date_relance1 IS NOT NULL
                        THEN TO_CHAR(rl.date_relance1, '%d/%m/%Y')
                    WHEN dneg.statut_bc = 'En attente bc'
                        AND NVL(rl.nb_relances, 0) = 0
                        AND dneg.date_envoye_devis_client IS NOT NULL
                        AND (TODAY - DATE(dneg.date_envoye_devis_client)) >= 7
                        AND (dneg.stop_progression_global = 0 OR dneg.stop_progression_global IS NULL)
                        THEN 'A relancer'
                    ELSE NULL
                END AS statut_relance_1
                -- Pour statut_relance_2
                ,CASE
                    WHEN rl.date_relance2 IS NOT NULL
                        THEN TO_CHAR(rl.date_relance2, '%d/%m/%Y')
                    WHEN dneg.statut_bc = 'En attente bc'
                        AND rl.nb_relances = 1
                        AND rl.delai_jours >= 7
                        AND (dneg.stop_progression_global = 0 OR dneg.stop_progression_global IS NULL)
                        THEN 'A relancer'
                    ELSE NULL
                END AS statut_relance_2

                -- Pour statut_relance_3
                ,CASE
                    WHEN rl.date_relance3 IS NOT NULL
                        THEN TO_CHAR(rl.date_relance3, '%d/%m/%Y')
                    WHEN dneg.statut_bc = 'En attente bc'
                        AND rl.nb_relances = 2
                        AND rl.delai_jours >= 7
                        AND (dneg.stop_progression_global = 0 OR dneg.stop_progression_global IS NULL)
                        THEN 'A relancer'
                    ELSE NULL
                END AS statut_relance_3

                ,nent.nent_posl                                             AS position_ips
                ,TRIM(ausr.ausr_nom)                                        AS utilisateur_createur_devis
                ,dneg.utilisateur                                           AS soumis_par
                ,nent.nent_devise                                           AS devise
                ,(SELECT MAX(nlig_constp) FROM ips_hffprod:informix.neg_lig WHERE nlig_numcde = nent.nent_numcde) AS constructeur

            FROM ips_hffprod:informix.neg_ent nent

            LEFT JOIN ips_hffprod:informix.agr_usr ausr
                ON ausr.ausr_num = nent.nent_usr
                AND ausr.ausr_soc = nent.nent_soc

            LEFT JOIN ir_prod108:Informix.devis_soumis_a_validation_neg dneg
                ON dneg.numero_devis = nent.nent_numcde
                AND dneg.numero_version = (SELECT MAX(numero_version) FROM ir_prod108:Informix.devis_soumis_a_validation_neg WHERE numero_devis = nent.nent_numcde)

            LEFT JOIN (
                SELECT
                    numero_devis as num_dev
                    ,MAX(CASE WHEN numero_relance = 1 THEN date_de_relance ELSE NULL END) AS date_relance1
                    ,MAX(CASE WHEN numero_relance = 2 THEN date_de_relance ELSE NULL END) AS date_relance2
                    ,MAX(CASE WHEN numero_relance = 3 THEN date_de_relance ELSE NULL END) AS date_relance3
                    ,COUNT(*) AS nb_relances
                    ,MAX(date_de_relance) AS derniere_relance
                    ,(TODAY - DATE(MAX(date_de_relance))) AS delai_jours
                FROM ir_prod108:Informix.pointage_relance
                GROUP BY 1
            ) rl ON rl.num_dev = nent.nent_numcde

            WHERE nent.nent_natop    = 'DEV'
                --AND nent.nent_soc      = 'HF'
                AND nent.nent_servcrt  <> 'ASS'
                AND nent.nent_numcli   NOT BETWEEN 1990000 AND 1999999
                AND nent.nent_numcli   <> 1990000
                AND nent.nent_numcde   NOT IN (19407989,19407991,19408971,19410383,19409906,19409996)
                AND nent.nent_datecde  >= MDY(9, 1, 2025)
                AND nent.nent_succ <> '60'
                AND nent.nent_soc = '$codeSociete'
                AND EXISTS (
                                SELECT 1 FROM ips_hffprod:informix.neg_lig nl
                                WHERE nl.nlig_numcde = nent.nent_numcde
                                AND nl.nlig_constp IN ('AGR','ATC','AUS','CAT','CGM','CMX','DNL','DYN','GRO','HYS','JDR','KIT','MAN','MNT','OLY','OOM','PAR','PDV','PER','PUB','REM','SHM','TBI','THO')
                            )
                ";

            // if (empty($criteria['statutDw']) && empty($criteria['statutBc']) && empty($criteria['filterRelance'])) {
            //     $statement .= " AND (dneg.statut_dw in ('A envoyer client', 'A soumettre') or  dneg.statut_dw is null) ";
            // }

            $whereClauses = [];

            if (!empty($numDeviAExclure)) {
                $whereClauses[] = " nent.nent_numcde NOT IN ($numDeviAExclure) ";
            }

            // Filtre par agences autorisées
            // if (!empty($codeAgenceAutoriserString)) {
            //     $whereClauses[] = " nent.nent_succ IN ($codeAgenceAutoriserString) ";
            // }

            if (array_key_exists('statutIps', $criteria) && ($criteria['statutIps'] == 'RE' || $criteria['statutIps'] == 'TR')) {
                $whereClauses[] = " nent.nent_posl in ('--','AC','DE', 'RE', 'TR')";
            } else {
                $whereClauses[] = " nent.nent_posl in ('--','AC','DE', 'TR')";
            }

            // Application des filtres dynamiques
            $this->filtre($whereClauses, $criteria);

            if (!empty($whereClauses)) {
                $statement .= " AND " . implode(" AND ", $whereClauses);
            }

            $statement .= " ORDER BY date_cde_brute DESC";

            $result = $this->connect->executeQuery($statement);
            $rows = $this->connect->fetchResults($result);

            return $rows;
        } finally {
            $this->connect->close();
        }
    }

    /**
     * Cette Methode permet de récupérer les données à exporter 
     * pour une fichier Excel
     * 
     * @param array $criteria
     * @param string $codeAgenceAutoriserString
     * @param string $numDeviAExclure
     * @param string $codeSociete
     * 
     * @return array
     */
    public function getDevisNegExportExcel($criteria, $codeAgenceAutoriserString, $numDeviAExclure, $codeSociete)
    {
        $this->connect->connect();

        try {

            $statement = "SELECT 
    nent.nent_datecde                                           AS date_cde_brute
    ,dneg.statut_dw                                             AS statut_dw
    ,dneg.statut_bc                                             AS statut_bc
    ,nent.nent_numcde                                           AS numero_devis
    ,TO_CHAR(nent.nent_datecde, '%d/%m/%Y')                     AS date_creation
    ,nent.nent_succ || ' - ' || nent_servcrt                    AS emetteur
    ,nent.nent_numcli || ' - ' || nent_nomcli                   AS client
    ,TRIM(nent.nent_refcde)                                     AS reference_client
    ,nent.nent_cdeht                                            AS montant_devis
    ,TO_CHAR(dneg.date_envoye_devis_client, '%d/%m/%Y')         AS date_envoye_devis_au_client

    -- Pour statut_relance_1
    ,CASE
        WHEN rl.date_relance1 IS NOT NULL
            THEN TO_CHAR(rl.date_relance1, '%d/%m/%Y')
        WHEN dneg.statut_bc = 'En attente bc'
            AND NVL(rl.nb_relances, 0) = 0
            AND dneg.date_envoye_devis_client IS NOT NULL
            AND (TODAY - DATE(dneg.date_envoye_devis_client)) >= 7
            AND (dneg.stop_progression_global = 0 OR dneg.stop_progression_global IS NULL)
            THEN 'A relancer'
        ELSE NULL
    END AS statut_relance_1

    -- Pour statut_relance_2
    ,CASE
        WHEN rl.date_relance2 IS NOT NULL
            THEN TO_CHAR(rl.date_relance2, '%d/%m/%Y')
        WHEN dneg.statut_bc = 'En attente bc'
            AND rl.nb_relances = 1
            AND rl.delai_jours >= 7
            AND (dneg.stop_progression_global = 0 OR dneg.stop_progression_global IS NULL)
            THEN 'A relancer'
        WHEN dneg.statut_bc = 'En attente bc'
            AND rl.nb_relances = 1
            AND rl.delai_jours < 7
            THEN NULL
        WHEN dneg.statut_bc = 'En attente bc'
            AND dneg.stop_progression_global = 1
            THEN NULL
        ELSE NVL(TO_CHAR(rl.date_relance2, '%d/%m/%Y'), TO_CHAR(rl.derniere_relance, '%d/%m/%Y'))
    END AS statut_relance_2

    -- Pour statut_relance_3
    ,CASE
        WHEN rl.date_relance3 IS NOT NULL
            THEN TO_CHAR(rl.date_relance3, '%d/%m/%Y')
        WHEN dneg.statut_bc = 'En attente bc'
            AND rl.nb_relances = 2
            AND rl.delai_jours >= 7
            AND (dneg.stop_progression_global = 0 OR dneg.stop_progression_global IS NULL)
            THEN 'A relancer'
        WHEN dneg.statut_bc = 'En attente bc'
            AND (rl.nb_relances < 2 OR (rl.nb_relances = 2 AND rl.delai_jours < 7))
            THEN NULL
        WHEN dneg.statut_bc = 'En attente bc'
            AND dneg.stop_progression_global = 1
            THEN NULL
        ELSE TO_CHAR(rl.derniere_relance, '%d/%m/%Y')
    END AS statut_relance_3

    ,nent.nent_posl                                             AS position_ips
    ,TRIM(ausr.ausr_nom)                                        AS utilisateur_createur_devis
    ,dneg.utilisateur                                           AS soumis_par
    ,nent.nent_devise                                           AS devise
    ,(SELECT MAX(nlig_constp) FROM ips_hffprod:informix.neg_lig WHERE nlig_numcde = nent.nent_numcde) AS constructeur

FROM ips_hffprod:informix.neg_ent nent

LEFT JOIN ips_hffprod:informix.agr_usr ausr
    ON ausr.ausr_num = nent.nent_usr
    AND ausr.ausr_soc = nent.nent_soc

LEFT JOIN ir_prod108:Informix.devis_soumis_a_validation_neg dneg
    ON dneg.numero_devis = nent.nent_numcde
    AND dneg.code_societe = nent.nent_soc
    AND dneg.numero_version = (SELECT MAX(numero_version) FROM ir_prod108:Informix.devis_soumis_a_validation_neg WHERE numero_devis = nent.nent_numcde AND code_societe = nent.nent_soc)

LEFT JOIN (
    SELECT
        numero_devis
        ,code_societe
        ,MAX(CASE WHEN numero_relance = 1 THEN date_de_relance ELSE NULL END) AS date_relance1
        ,MAX(CASE WHEN numero_relance = 2 THEN date_de_relance ELSE NULL END) AS date_relance2
        ,MAX(CASE WHEN numero_relance = 3 THEN date_de_relance ELSE NULL END) AS date_relance3
        ,COUNT(*) AS nb_relances
        ,MAX(date_de_relance) AS derniere_relance
        ,(TODAY - DATE(MAX(date_de_relance))) AS delai_jours
    FROM ir_prod108:Informix.pointage_relance
    GROUP BY 1,2
) rl ON rl.numero_devis = nent.nent_numcde AND rl.code_societe = nent.nent_soc
WHERE nent.nent_natop    = 'DEV'
    --AND nent.nent_soc      = 'HF'
    AND nent.nent_servcrt  <> 'ASS'
    AND nent.nent_numcli   NOT BETWEEN 1990000 AND 1999999
    AND nent.nent_numcli   <> 1990000
    AND nent.nent_numcde   NOT IN (19407989,19407991,19408971,19410383,19409906,19409996)
    AND nent.nent_datecde  >= MDY(9, 1, 2025)
    AND nent.nent_succ <> '60'
    AND nent.nent_soc = '$codeSociete'
    AND EXISTS (
                    SELECT 1 FROM ips_hffprod:informix.neg_lig nl
                    WHERE nl.nlig_numcde = nent.nent_numcde
                    AND nl.nlig_constp IN ('AGR','ATC','AUS','CAT','CGM','CMX','DNL','DYN','GRO','HYS','JDR','KIT','MAN','MNT','OLY','OOM','PAR','PDV','PER','PUB','REM','SHM','TBI','THO')
                )
    ";

            if (empty($criteria['statutDw']) && empty($criteria['statutBc']) && empty($criteria['filterRelance'])) {
                $statement .= " AND (dneg.statut_dw in ('A envoyer client', 'A soumettre') or  dneg.statut_dw is null) ";
            }

            $whereClauses = [];

            if (!empty($numDeviAExclure)) {
                $whereClauses[] = " nent.nent_numcde NOT IN ($numDeviAExclure) ";
            }

            // Filtre par agences autorisées
            // if (!empty($codeAgenceAutoriserString)) {
            //     $whereClauses[] = " nent.nent_succ IN ($codeAgenceAutoriserString) ";
            // }

            if (array_key_exists('statutIps', $criteria) && ($criteria['statutIps'] == 'RE' || $criteria['statutIps'] == 'TR')) {
                $whereClauses[] = " nent.nent_posl in ('--','AC','DE', 'RE', 'TR')";
            } else {
                $whereClauses[] = " nent.nent_posl in ('--','AC','DE', 'TR')";
            }

            // Application des filtres dynamiques
            $this->filtre($whereClauses, $criteria);

            if (!empty($whereClauses)) {
                $statement .= " AND " . implode(" AND ", $whereClauses);
            }

            $statement .= " ORDER BY date_cde_brute DESC";

            $result = $this->connect->executeQuery($statement);
            $rows = $this->connect->fetchResults($result);

            return $rows;
        } finally {
            $this->connect->close();
        }
    }


    private function filtre(&$whereClauses, $criteria)
    {
        // Filtre par numéro de devis
        if (!empty($criteria['numeroDevis'])) {
            $whereClauses[] = " CAST(nent.nent_numcde AS VARCHAR(20)) LIKE '%" . $criteria['numeroDevis'] . "%' ";
        }

        // Filtre par code client (nom ou numéro)
        if (!empty($criteria['codeClient'])) {
            $whereClauses[] = " (CAST(nent.nent_numcli AS VARCHAR(20)) LIKE '%" . $criteria['codeClient'] . "%' OR nent.nent_nomcli LIKE '%" . $criteria['codeClient'] . "%') ";
        }

        // Filtre par opérateur (soumis par)
        if (!empty($criteria['Operateur'])) {
            $whereClauses[] = " dneg.utilisateur LIKE '%" . $criteria['Operateur'] . "%' ";
        }

        // Filtre par numéro de PO
        if (!empty($criteria['numeroPO'])) {
            $whereClauses[] = " TRIM(nent.nent_refcde) LIKE '%" . $criteria['numeroPO'] . "%' ";
        }

        // Filtre par utilisateur créateur
        if (!empty($criteria['CreePar'])) {
            $whereClauses[] = " ausr.ausr_nom LIKE '%" . $criteria['CreePar'] . "%' ";
        }

        // Filtre par statut DW
        if (!empty($criteria['statutDw'])) {
            $s = $criteria['statutDw'];
            $const = \App\Constants\Magasin\Devis\StatutDevisNegContant::class;

            if ($s === $const::A_TRAITER) {
                $whereClauses[] = " (TRIM(dneg.statut_dw) LIKE 'A %traiter' OR TRIM(dneg.statut_dw) IS NULL) ";
            } elseif ($s === $const::PRIX_A_CONFIRMER) {
                $whereClauses[] = " TRIM(dneg.statut_dw) LIKE 'Prix % confirmer' ";
            } elseif ($s === $const::PRIX_VALIDER_TANA) {
                $whereClauses[] = " TRIM(dneg.statut_dw) LIKE 'Prix valid% - devis % envoyer au client' ";
            } elseif ($s === $const::PRIX_VALIDER_AGENCE) {
                $whereClauses[] = " TRIM(dneg.statut_dw) LIKE 'Prix valid% - devis % soumettre' ";
            } elseif ($s === $const::PRIX_MODIFIER_TANA) {
                $whereClauses[] = " TRIM(dneg.statut_dw) LIKE 'Prix modifi% - devis % envoyer au client' ";
            } elseif ($s === $const::PRIX_MODIFIER_AGENCE) {
                $whereClauses[] = " TRIM(dneg.statut_dw) LIKE 'Prix modifi% - devis % soumettre' ";
            } elseif ($s === $const::DEMANDE_REFUSE_PAR_PM) {
                $whereClauses[] = " TRIM(dneg.statut_dw) LIKE 'Demande refus%e par le PM' ";
            } elseif ($s === $const::A_VALIDER_CHEF_AGENCE) {
                $whereClauses[] = " TRIM(dneg.statut_dw) LIKE 'A valider chef d%agence' ";
            } elseif ($s === $const::VALIDE_AGENCE) {
                $whereClauses[] = " TRIM(dneg.statut_dw) LIKE 'Valid% - % envoyer au client' ";
            } elseif ($s === $const::ENVOYER_CLIENT) {
                $whereClauses[] = " TRIM(dneg.statut_dw) LIKE 'Envoy% au client' ";
            } elseif ($s === $const::CLOTURER_A_MODIFIER) {
                $whereClauses[] = " TRIM(dneg.statut_dw) LIKE 'Clotur% - A modifier' ";
            } else {
                $whereClauses[] = " TRIM(dneg.statut_dw) = '" . $s . "' ";
            }
        }

        // Filtre par statut BC
        if (!empty($criteria['statutBc'])) {
            $bc = $criteria['statutBc'];
            if ($bc === \App\Constants\Magasin\Devis\StatutBcNegConstant::SOUMIS_VALIDATION) {
                $whereClauses[] = " TRIM(dneg.statut_bc) LIKE 'Soumis % validation' ";
            } elseif ($bc === \App\Constants\Magasin\Devis\StatutBcNegConstant::VALIDER) {
                $whereClauses[] = " TRIM(dneg.statut_bc) LIKE 'Valid%' ";
            } elseif ($bc === \App\Constants\Magasin\Devis\StatutBcNegConstant::EN_ATTENTE_BC) {
                $whereClauses[] = " TRIM(dneg.statut_bc) LIKE 'En attente bc' ";
            } else {
                $whereClauses[] = " TRIM(dneg.statut_bc) = '" . $bc . "' ";
            }
        }

        // Filtre par statut IPS (Position IPS) - on l'ajoute seulement s'il n'a pas déjà été traité dans getDevisNeg
        if (!empty($criteria['statutIps']) && !in_array($criteria['statutIps'], ['RE', 'TR'])) {
            $whereClauses[] = " TRIM(nent.nent_posl) = '" . $criteria['statutIps'] . "' ";
        }

        // Filtre par agence émetteur
        if (!empty($criteria['emetteur']['agence']) && method_exists($criteria['emetteur']['agence'], 'getCodeAgence')) {
            $agenceCode = $criteria['emetteur']['agence']->getCodeAgence();
            $whereClauses[] = " nent.nent_succ = '" . $agenceCode . "' ";
        }

        // Filtre par service émetteur
        if (!empty($criteria['emetteur']['service']) && method_exists($criteria['emetteur']['service'], 'getCodeService')) {
            $serviceCode = $criteria['emetteur']['service']->getCodeService();
            $whereClauses[] = " nent.nent_servcrt = '" . $serviceCode . "' ";
        }

        // Filtre par date de création (Plage de dates)
        if (!empty($criteria['dateCreation'])) {
            if (!empty($criteria['dateCreation']['debut']) && $criteria['dateCreation']['debut'] instanceof \DateTime) {
                $d = $criteria['dateCreation']['debut'];
                $whereClauses[] = " DATE(nent.nent_datecde) >= MDY(" . $d->format('n') . "," . $d->format('j') . "," . $d->format('Y') . ") ";
            }
            if (!empty($criteria['dateCreation']['fin']) && $criteria['dateCreation']['fin'] instanceof \DateTime) {
                $f = $criteria['dateCreation']['fin'];
                $whereClauses[] = " DATE(nent.nent_datecde) <= MDY(" . $f->format('n') . "," . $f->format('j') . "," . $f->format('Y') . ") ";
            }
        }

        // Filtre par statut de relance
        if (!empty($criteria['filterRelance'])) {
            $filter = $criteria['filterRelance'];
            switch ($filter) {
                case 'A_RELANCER':
                    $whereClauses[] = " (
                            (TRIM(dneg.statut_bc) LIKE 'En attente bc' AND NVL(rl.nb_relances, 0) = 0 AND dneg.date_envoye_devis_client IS NOT NULL AND (TODAY - DATE(dneg.date_envoye_devis_client)) >= 7 AND (dneg.stop_progression_global = 0 OR dneg.stop_progression_global IS NULL)) OR
                            (TRIM(dneg.statut_bc) LIKE 'En attente bc' AND rl.nb_relances = 1 AND rl.delai_jours >= 7 AND (dneg.stop_progression_global = 0 OR dneg.stop_progression_global IS NULL)) OR
                            (TRIM(dneg.statut_bc) LIKE 'En attente bc' AND rl.nb_relances = 2 AND rl.delai_jours >= 7 AND (dneg.stop_progression_global = 0 OR dneg.stop_progression_global IS NULL))
                        )";
                    break;
                case '3_RELANCES_OK':
                    $whereClauses[] = " (rl.date_relance3 IS NOT NULL AND (dneg.stop_progression_global = 0 OR dneg.stop_progression_global IS NULL)) ";
                    break;
                case '3_RELANCES_STOP':
                    $whereClauses[] = " (rl.date_relance3 IS NOT NULL AND dneg.stop_progression_global = 1) ";
                    break;
                case 'STOP_AVANT_R1':
                    $whereClauses[] = " (dneg.stop_progression_global = 1 AND rl.date_relance1 IS NULL) ";
                    break;
                case 'STOP_R1':
                    $whereClauses[] = " (dneg.stop_progression_global = 1 AND rl.date_relance1 IS NOT NULL AND rl.date_relance2 IS NULL) ";
                    break;
                case 'STOP_R2':
                    $whereClauses[] = " (dneg.stop_progression_global = 1 AND rl.date_relance2 IS NOT NULL AND rl.date_relance3 IS NULL) ";
                    break;
                case 'R1_EN_COURS':
                    $whereClauses[] = " (rl.date_relance1 IS NOT NULL AND rl.date_relance2 IS NULL) ";
                    break;
                case 'R2_EN_COURS':
                    $whereClauses[] = " (rl.date_relance2 IS NOT NULL AND rl.date_relance3 IS NULL) ";
                    break;
                case 'R3_EN_COURS':
                    $whereClauses[] = " (rl.date_relance3 IS NOT NULL) ";
                    break;
            }
        }
    }

    public function getNumeroDevisExclure()
    {
        $sql = " SELECT distinct Numero_Devis_ERP as numDevis
                from GCOT_Devis
                WHERE (CASE WHEN Numero_Devis_ERP NOT LIKE '%[^0-9]%' AND Numero_Devis_ERP <> '' THEN CAST(Numero_Devis_ERP AS DECIMAL(38, 0)) ELSE 0 END) >= 19000000
                ";

        $statement = $this->connexion04Gcot->query($sql);
        $data = [];
        while ($List = odbc_fetch_array($statement)) {
            $data[] = $List;
        }

        return array_column($data, 'numDevis');
    }

    public function stopRelance(string $numeroDevis, ?string $motif = null, string $utilisateur = 'inconnu'): bool
    {
        $this->connect->connect();
        try {
            // On récupère l'état actuel pour savoir si on stoppe ou si on réactive
            $sqlCheck = "SELECT FIRST 1 stop_progression_global 
                        FROM ir_prod108:Informix.devis_soumis_a_validation_neg dneg
                        WHERE dneg.numero_devis = '$numeroDevis' 
                        ORDER BY dneg.numero_version DESC";

            $resultCheck = $this->connect->executeQuery($sqlCheck);
            $row = $this->connect->fetchScalarResults($resultCheck);

            $currentState = $row ? (int)$row['stop_progression_global'] : 0;
            $newState = ($currentState === 1) ? 0 : 1;

            $utilisateurSql = str_replace("'", "''", $utilisateur);

            if ($newState === 1) {
                // On stoppe
                $motifStop = $motif ? $this->convertirEnUtf8(str_replace("'", "''", $motif)) : "";
                $sql = "UPDATE ir_prod108:Informix.devis_soumis_a_validation_neg
                        SET stop_progression_global = 1, 
                            date_stop_global = CURRENT,
                            motif_stop_global = '$motifStop',
                            utilisateur_stop = '$utilisateurSql',
                            date_reprise_manuel = NULL,
                            utilisateur_reprise = NULL
                        WHERE numero_devis = '$numeroDevis' 
                        AND numero_version = (SELECT MAX(numero_version) FROM ir_prod108:Informix.devis_soumis_a_validation_neg WHERE numero_devis = '$numeroDevis')";
            } else {
                // On réactive : on efface le motif et on note l'utilisateur qui réactive
                $sql = "UPDATE ir_prod108:Informix.devis_soumis_a_validation_neg
                        SET stop_progression_global = 0, 
                            motif_stop_global = NULL,
                            date_reprise_manuel = CURRENT,
                            utilisateur_reprise = '$utilisateurSql'
                        WHERE numero_devis = '$numeroDevis' 
                        AND numero_version = (SELECT MAX(numero_version) FROM ir_prod108:Informix.devis_soumis_a_validation_neg WHERE numero_devis = '$numeroDevis')";
            }

            $this->connect->executeQuery($sql);
            return true;
        } catch (\Exception $e) {
            return false;
        } finally {
            $this->connect->close();
        }
    }

    public function getStatutRelance(string $numeroDevis, string $codeSociete): ?array
    {
        $this->connect->connect();
        try {
            $statement = "SELECT FIRST 1
                        CASE 
                            WHEN rs.date_relance_1 IS NOT NULL THEN TO_CHAR(rs.date_relance_1, '%d/%m/%Y')
                            WHEN rs.statut_bc = 'En attente bc' AND rs.nb_relances = 0 AND rs.delai_jours >= 7
                                 AND (rs.stop_progression_global = 0 OR rs.stop_progression_global IS NULL)
                            THEN 'A relancer'
                            ELSE NULL
                        END AS statut_relance_1,

                        CASE 
                            WHEN rs.date_relance_2 IS NOT NULL THEN TO_CHAR(rs.date_relance_2, '%d/%m/%Y')
                            WHEN rs.statut_bc = 'En attente bc' AND rs.nb_relances = 1 AND rs.delai_jours >= 7
                                 AND (rs.stop_progression_global = 0 OR rs.stop_progression_global IS NULL)
                            THEN 'A relancer'
                            ELSE NULL
                        END AS statut_relance_2,

                        CASE 
                            WHEN rs.date_relance_3 IS NOT NULL THEN TO_CHAR(rs.date_relance_3, '%d/%m/%Y')
                            WHEN rs.statut_bc = 'En attente bc' AND rs.nb_relances = 2 AND rs.delai_jours >= 7
                                 AND (rs.stop_progression_global = 0 OR rs.stop_progression_global IS NULL)
                            THEN 'A relancer'
                            ELSE NULL
                        END AS statut_relance_3
                    FROM (
                        SELECT 
                            nent.nent_numcde,
                            dneg.date_envoye_devis_client,
                            dneg.statut_bc,
                            dneg.numero_version,
                            dneg.stop_progression_global,
                            COUNT(pr.numero_devis) AS nb_relances,
                            MAX(pr.date_de_relance) AS derniere_relance,
                            MAX(CASE WHEN pr.numero_relance = 1 THEN pr.date_de_relance END) AS date_relance_1,
                            MAX(CASE WHEN pr.numero_relance = 2 THEN pr.date_de_relance END) AS date_relance_2,
                            MAX(CASE WHEN pr.numero_relance = 3 THEN pr.date_de_relance END) AS date_relance_3,
                            CASE 
                                WHEN COUNT(pr.numero_devis) = 0 THEN (TODAY - DATE(NVL(dneg.date_envoye_devis_client, nent.nent_datecde)))
                                ELSE (TODAY - DATE(MAX(pr.date_de_relance)))
                            END AS delai_jours
                        FROM ips_hffprod:informix.neg_ent nent
                        LEFT JOIN ir_prod108:Informix.devis_soumis_a_validation_neg dneg ON dneg.numero_devis = nent.nent_numcde
                        LEFT JOIN ir_prod108:Informix.pointage_relance pr ON pr.numero_devis = nent.nent_numcde
                        WHERE nent.nent_numcde = '$numeroDevis' AND nent.nent_soc = '$codeSociete'
                        GROUP BY nent.nent_numcde, dneg.date_envoye_devis_client, dneg.statut_bc, dneg.numero_version, dneg.stop_progression_global, nent.nent_datecde
                    ) rs
                    ORDER BY rs.numero_version DESC";

            $result = $this->connect->executeQuery($statement);
            return $this->connect->fetchScalarResults($result);
        } catch (\Exception $e) {
            return [];
        } finally {
            $this->connect->close();
        }
    }
}
