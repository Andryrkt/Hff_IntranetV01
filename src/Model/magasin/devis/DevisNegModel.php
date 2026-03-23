<?php

namespace App\Model\magasin\devis;

use App\Model\Model;
use App\Service\GlobalVariablesService;

class DevisNegModel extends Model
{
    public function getDevisNeg($criteria, $vignette, $codeAgenceAutoriserString, $adminMutli, $numDeviAExclure, $page = 1, $limit = 50)
    {
        $this->connect->connect();
        $skip = ($page - 1) * $limit;

        try {

            $statement = " SELECT SKIP $skip FIRST $limit
                    DISTINCT
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

                    ,CASE
                        WHEN pr1.date_de_relance IS NOT NULL
                            THEN TO_CHAR(pr1.date_de_relance, '%d/%m/%Y')
                        WHEN dneg.statut_bc = 'En attente bc'
                            AND rl.nb_relances = 0
                            AND rl.delai_jours >= 7
                            AND (dneg.stop_progression_global = 0 OR dneg.stop_progression_global IS NULL)
                            AND NVL(pr1.stop_progression_niveau, 0) = 0
                            THEN 'A relancer'
                        ELSE NULL
                    END                                                         AS statut_relance_1

                    ,CASE
                        WHEN pr2.date_de_relance IS NOT NULL
                            THEN TO_CHAR(pr2.date_de_relance, '%d/%m/%Y')
                        WHEN dneg.statut_bc = 'En attente bc'
                            AND rl.nb_relances = 1
                            AND rl.delai_jours >= 7
                            AND (dneg.stop_progression_global = 0 OR dneg.stop_progression_global IS NULL)
                            AND NVL(pr2.stop_progression_niveau, 0) = 0
                            THEN 'A relancer'
                        WHEN dneg.statut_bc = 'En attente bc'
                            AND rl.nb_relances = 1
                            AND rl.delai_jours < 7
                            THEN NULL
                        WHEN dneg.statut_bc = 'En attente bc'
                            AND dneg.stop_progression_global = 1
                            THEN NULL
                        ELSE NVL(TO_CHAR(pr2.date_de_relance, '%d/%m/%Y'), TO_CHAR(rl.derniere_relance, '%d/%m/%Y'))
                    END                                                         AS statut_relance_2

                    ,CASE
                        WHEN pr3.date_de_relance IS NOT NULL
                            THEN TO_CHAR(pr3.date_de_relance, '%d/%m/%Y')
                        WHEN dneg.statut_bc = 'En attente bc'
                            AND rl.nb_relances = 2
                            AND rl.delai_jours >= 7
                            AND (dneg.stop_progression_global = 0 OR dneg.stop_progression_global IS NULL)
                            AND NVL(pr3.stop_progression_niveau, 0) = 0
                            THEN 'A relancer'
                        WHEN dneg.statut_bc = 'En attente bc'
                            AND (rl.nb_relances < 2 OR (rl.nb_relances = 2 AND rl.delai_jours < 7))
                            THEN NULL
                        WHEN dneg.statut_bc = 'En attente bc'
                            AND dneg.stop_progression_global = 1
                            THEN NULL
                        ELSE TO_CHAR(rl.derniere_relance, '%d/%m/%Y')
                    END                                                         AS statut_relance_3

                    ,nent.nent_posl                                             AS position_ips
                    ,TRIM(ausr.ausr_nom)                                        AS utilisateur_createur_devis
                    ,dneg.utilisateur                                           AS soumis_par
                    -- qui n'est pas dans la liste mais utile après
                    ,nent.nent_devise                                           AS devise
                    ,nlig.nlig_constp                                           AS constructeur

                FROM ips_hffprod:informix.neg_ent nent

                LEFT JOIN ips_hffprod:informix.neg_lig nlig
                    ON nlig.nlig_numcde = nent.nent_numcde

                LEFT JOIN ips_hffprod:informix.agr_usr ausr
                    ON ausr.ausr_num = nent.nent_usr
                    AND ausr.ausr_soc = nent.nent_soc

                LEFT JOIN ir_prod108:Informix.devis_soumis_a_validation_neg dneg
                    ON dneg.numero_devis = nent.nent_numcde

                LEFT JOIN (
                    SELECT
                        dsavn.numero_devis
                        ,COUNT(pr.numero_devis)                                 AS nb_relances
                        ,MAX(pr.date_de_relance)                                AS derniere_relance
                        ,CASE
                            WHEN COUNT(pr.numero_devis) = 0
                            THEN (TODAY - DATE(dsavn.date_envoye_devis_client))
                            ELSE (TODAY - DATE(MAX(pr.date_de_relance)))
                        END                                                    AS delai_jours
                    FROM ir_prod108:Informix.devis_soumis_a_validation_neg dsavn
                    LEFT JOIN ir_prod108:Informix.pointage_relance pr
                        ON pr.numero_devis = dsavn.numero_devis
                    GROUP BY dsavn.numero_devis, dsavn.date_envoye_devis_client
                ) rl ON rl.numero_devis = nent.nent_numcde

                LEFT JOIN ir_prod108:Informix.pointage_relance pr1
                    ON pr1.numero_devis = nent.nent_numcde
                    AND pr1.numero_relance = 1

                LEFT JOIN ir_prod108:Informix.pointage_relance pr2
                    ON pr2.numero_devis = nent.nent_numcde
                    AND pr2.numero_relance = 2

                LEFT JOIN ir_prod108:Informix.pointage_relance pr3
                    ON pr3.numero_devis = nent.nent_numcde
                    AND pr3.numero_relance = 3

                WHERE nent.nent_natop    = 'DEV'
                AND nent.nent_soc      = 'HF'
                AND nent.nent_servcrt  <> 'ASS'
                AND (CAST(nent.nent_numcli AS VARCHAR(20)) NOT LIKE '199%'
                    AND nent.nent_numcli NOT IN ('1990000'))
                AND nent.nent_numcde   NOT IN ('19407989','19407991','19408971','19410383','19409906','19409996')
                AND nent.nent_datecde  >= MDY(9, 1, 2025)
                AND nlig.nlig_constp   <> 'Nmc'
                AND nent_numcde not in ($numDeviAExclure)
                --AND nent.nent_numcde =19413210
            ";

            if (array_key_exists('statutIps', $criteria) && ($criteria['statutIps'] == 'RE' || $criteria['statutIps'] == 'TR')) {
                $statement .= " AND nent.nent_posl in ('--','AC','DE', 'RE', 'TR')";
            } else {
                $statement .= " AND nent.nent_posl in ('--','AC','DE', 'TR')";
            }

            if ($vignette === 'pneumatique' && !$adminMutli) {
                // entrer par le vignette POL - agence pneumatique
                $piecesPneumatique = GlobalVariablesService::get('pneumatique');
                $statement .= " AND nlig.nlig_constp IN ($piecesPneumatique) AND nent_succ in ($codeAgenceAutoriserString) ";
            } else {
                // entrer par le vignette MAGASIN - agence tana et autres agence
                $piecesMagasin = GlobalVariablesService::get('pieces_magasin');
                $statement .= " AND nlig.nlig_constp IN ($piecesMagasin) AND nent.nent_succ <> '60' ";
            }

            $statement .= " ORDER BY date_cde_brute DESC";

            // Ajout des filtres dynamiques
            $whereClauses = [];

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

                if ($s === \App\Constants\Magasin\Devis\StatutDevisNegContant::A_TRAITER) {
                    $whereClauses[] = " (TRIM(dneg.statut_dw) LIKE 'A %traiter' OR TRIM(dneg.statut_dw) IS NULL) ";
                } elseif ($s === \App\Constants\Magasin\Devis\StatutDevisNegContant::PRIX_A_CONFIRMER) {
                    $whereClauses[] = " TRIM(dneg.statut_dw) LIKE 'Prix % confirmer' ";
                } elseif ($s === \App\Constants\Magasin\Devis\StatutDevisNegContant::PRIX_VALIDER_TANA) {
                    $whereClauses[] = " TRIM(dneg.statut_dw) LIKE 'Prix valid% - devis % envoyer au client' ";
                } elseif ($s === \App\Constants\Magasin\Devis\StatutDevisNegContant::PRIX_VALIDER_AGENCE) {
                    $whereClauses[] = " TRIM(dneg.statut_dw) LIKE 'Prix valid% - devis % soumettre' ";
                } elseif ($s === \App\Constants\Magasin\Devis\StatutDevisNegContant::PRIX_MODIFIER_TANA) {
                    $whereClauses[] = " TRIM(dneg.statut_dw) LIKE 'Prix modifi% - devis % envoyer au client' ";
                } elseif ($s === \App\Constants\Magasin\Devis\StatutDevisNegContant::PRIX_MODIFIER_AGENCE) {
                    $whereClauses[] = " TRIM(dneg.statut_dw) LIKE 'Prix modifi% - devis % soumettre' ";
                } elseif ($s === \App\Constants\Magasin\Devis\StatutDevisNegContant::DEMANDE_REFUSE_PAR_PM) {
                    $whereClauses[] = " TRIM(dneg.statut_dw) LIKE 'Demande refus%e par le PM' ";
                } elseif ($s === \App\Constants\Magasin\Devis\StatutDevisNegContant::A_VALIDER_CHEF_AGENCE) {
                    $whereClauses[] = " TRIM(dneg.statut_dw) LIKE 'A valider chef d%agence' ";
                } elseif ($s === \App\Constants\Magasin\Devis\StatutDevisNegContant::VALIDE_AGENCE) {
                    $whereClauses[] = " TRIM(dneg.statut_dw) LIKE 'Valid% - % envoyer au client' ";
                } elseif ($s === \App\Constants\Magasin\Devis\StatutDevisNegContant::ENVOYER_CLIENT) {
                    $whereClauses[] = " TRIM(dneg.statut_dw) LIKE 'Envoy% au client' ";
                } elseif ($s === \App\Constants\Magasin\Devis\StatutDevisNegContant::CLOTURER_A_MODIFIER) {
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

            // Filtre par statut IPS (Position IPS)
            if (!empty($criteria['statutIps'])) {
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
                // Ces conditions sont basées sur la logique de matchesCriteria adaptée au SQL
                switch ($filter) {
                    case 'A_RELANCER':
                        $whereClauses[] = " (
                            (TRIM(dneg.statut_bc) LIKE 'En attente bc' AND rl.nb_relances = 0 AND rl.delai_jours >= 7 AND (dneg.stop_progression_global = 0 OR dneg.stop_progression_global IS NULL) AND NVL(pr1.stop_progression_niveau, 0) = 0) OR
                            (TRIM(dneg.statut_bc) LIKE 'En attente bc' AND rl.nb_relances = 1 AND rl.delai_jours >= 7 AND (dneg.stop_progression_global = 0 OR dneg.stop_progression_global IS NULL) AND NVL(pr2.stop_progression_niveau, 0) = 0) OR
                            (TRIM(dneg.statut_bc) LIKE 'En attente bc' AND rl.nb_relances = 2 AND rl.delai_jours >= 7 AND (dneg.stop_progression_global = 0 OR dneg.stop_progression_global IS NULL) AND NVL(pr3.stop_progression_niveau, 0) = 0)
                        )";
                        break;
                    case '3_RELANCES_OK':
                        $whereClauses[] = " (pr3.date_de_relance IS NOT NULL AND (dneg.stop_progression_global = 0 OR dneg.stop_progression_global IS NULL)) ";
                        break;
                    case '3_RELANCES_STOP':
                        $whereClauses[] = " (pr3.date_de_relance IS NOT NULL AND dneg.stop_progression_global = 1) ";
                        break;
                    case 'STOP_AVANT_R1':
                        $whereClauses[] = " (dneg.stop_progression_global = 1 AND pr1.date_de_relance IS NULL) ";
                        break;
                    case 'STOP_R1':
                        $whereClauses[] = " (dneg.stop_progression_global = 1 AND pr1.date_de_relance IS NOT NULL AND pr2.date_de_relance IS NULL) ";
                        break;
                    case 'STOP_R2':
                        $whereClauses[] = " (dneg.stop_progression_global = 1 AND pr2.date_de_relance IS NOT NULL AND pr3.date_de_relance IS NULL) ";
                        break;
                    case 'R1_EN_COURS':
                        $whereClauses[] = " (pr1.date_de_relance IS NOT NULL AND pr2.date_de_relance IS NULL) ";
                        break;
                    case 'R2_EN_COURS':
                        $whereClauses[] = " (pr2.date_de_relance IS NOT NULL AND pr3.date_de_relance IS NULL) ";
                        break;
                    case 'R3_EN_COURS':
                        $whereClauses[] = " (pr3.date_de_relance IS NOT NULL) ";
                        break;
                }
            }

            if (!empty($whereClauses)) {
                // On normalise les espaces pour str_replace
                $statement = preg_replace('/\s+ORDER BY/', ' ORDER BY', $statement);
                $statement = str_replace(" ORDER BY", " AND " . implode(" AND ", $whereClauses) . " ORDER BY", $statement);
            }

            $result = $this->connect->executeQuery($statement);
            $rows = $this->connect->fetchResults($result);

            return $rows;
        } finally {
            $this->connect->close();
        }
    }

    public function getNumeroDevisExclure()
    {
        $sql = " SELECT distinct Numero_Devis_ERP as numDevis
                from GCOT_Devis
                ";

        $statement = $this->connexion04Gcot->query($sql);
        $data = [];
        while ($List = odbc_fetch_array($statement)) {
            $data[] = $List;
        }

        return array_column($data, 'numDevis');
    }
}
