<?php

namespace App\Model\magasin\devis;

use App\Model\Model;


class DevisNegModel extends Model
{
    public function getDevisNeg($criteria, $vignette, $codeAgenceAutoriserString, $adminMutli, $numDeviAExclure, $page = 1, $limit = 50)
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

    -- Pour statut_relance_1
    ,CASE
        WHEN rl.date_relance1 IS NOT NULL
            THEN TO_CHAR(rl.date_relance1, '%d/%m/%Y')
        WHEN dneg.statut_bc = 'En attente bc'
            AND rl.nb_relances = 0
            AND rl.delai_jours >= 7
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

LEFT JOIN (
    SELECT
        numero_devis
        ,MAX(CASE WHEN numero_relance = 1 THEN date_de_relance ELSE NULL END) AS date_relance1
        ,MAX(CASE WHEN numero_relance = 2 THEN date_de_relance ELSE NULL END) AS date_relance2
        ,MAX(CASE WHEN numero_relance = 3 THEN date_de_relance ELSE NULL END) AS date_relance3
        ,COUNT(*) AS nb_relances
        ,MAX(date_de_relance) AS derniere_relance
        ,(TODAY - DATE(MAX(date_de_relance))) AS delai_jours
    FROM ir_prod108:Informix.pointage_relance
    GROUP BY numero_devis
) rl ON rl.numero_devis = nent.nent_numcde

WHERE nent.nent_natop    = 'DEV'
    AND nent.nent_soc      = 'HF'
    AND nent.nent_servcrt  <> 'ASS'
    AND nent.nent_numcli   NOT BETWEEN 1990000 AND 1999999
    AND nent.nent_numcli   <> 1990000
    AND nent.nent_numcde   NOT IN (19407989,19407991,19408971,19410383,19409906,19409996)
    AND nent.nent_datecde  >= MDY(9, 1, 2025)
    AND nent.nent_succ <> '60'";

            $whereClauses = [];

            if (!empty($numDeviAExclure)) {
                $whereClauses[] = " nent.nent_numcde NOT IN ($numDeviAExclure) ";
            }

            // Filtre par agences autorisées
            if (!$adminMutli && !empty($codeAgenceAutoriserString)) {
                $whereClauses[] = " nent.nent_succ IN ($codeAgenceAutoriserString) ";
            }

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
                            (TRIM(dneg.statut_bc) LIKE 'En attente bc' AND rl.nb_relances = 0 AND rl.delai_jours >= 7 AND (dneg.stop_progression_global = 0 OR dneg.stop_progression_global IS NULL)) OR
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
}
