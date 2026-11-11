<?php

namespace App\Repository\admin\historisation\documentOperation;

use App\Entity\admin\historisation\documentOperation\HistoriqueOperationEtape;
use App\Service\historiqueOperation\suivi\SuiviOperationService;
use Doctrine\ORM\EntityRepository;

class HistoriqueOperationEtapeRepository extends EntityRepository
{
    // au-delà, une étape restée EN_COURS est considérée comme interrompue
    private const DELAI_EN_COURS_MINUTES = 10;

    /**
     * Recherche les opérations suivies, les plus récentes en premier, avec leurs étapes dans l'ordre.
     *
     * @param string|null $recherche       n° de document ou utilisateur
     * @param string|null $typeOperation  une des constantes SuiviOperationService::TYPE_*
     * @param bool        $problemeSeulement ne garder que les opérations avec une étape en échec, interrompue ou jamais terminée
     * @param int|null    $idHistorique    opération rattachée à cette ligne de l'historique des opérations
     * @param bool        $nonLuSeulement  ne garder que les opérations en problème pas encore marquées comme lues
     * @param int|null    $idTache         opérations rattachées à cette tâche de correction
     *
     * @return array<int, array{idSuivi: string, statut: string, etapes: HistoriqueOperationEtape[]}>
     */
    /**
     * Étapes d'une opération complète, par type : celles de la dernière opération entièrement réussie du type.
     * Sert à afficher en gris les étapes non faites d'une opération arrêtée en cours de route.
     * Les copies DocuWare (une ligne par fichier, libellé variable) sont exclues.
     *
     * shortcut: modèle cherché sur les 90 derniers jours (table volumineuse), à élargir si des types rares n'ont pas de modèle
     *
     * @param string[] $types
     * @return array<string, string[]> libellés des étapes dans l'ordre, par type d'opération
     */
    public function etapesReference(array $types): array
    {
        $types = array_values(array_unique(array_filter($types)));
        if (empty($types)) {
            return [];
        }

        $lignes = $this->getEntityManager()->getConnection()->fetchAllAssociative(
            "SELECT e.type_operation, e.etape
            FROM historique_operation_etape e
            JOIN (
                SELECT type_operation, id_suivi,
                       ROW_NUMBER() OVER (PARTITION BY type_operation ORDER BY MIN(date_debut) DESC) AS rang
                FROM historique_operation_etape
                WHERE type_operation IN (?) AND date_debut >= DATEADD(DAY, -90, GETDATE())
                GROUP BY type_operation, id_suivi
                HAVING SUM(CASE WHEN statut <> ? THEN 1 ELSE 0 END) = 0
            ) modele ON modele.id_suivi = e.id_suivi AND modele.rang = 1
            WHERE e.etape NOT LIKE 'Copie DocuWare :%'
            ORDER BY e.type_operation, e.ordre",
            [$types, SuiviOperationService::STATUT_SUCCES],
            [\Doctrine\DBAL\ArrayParameterType::STRING, \PDO::PARAM_STR]
        );

        $reference = [];
        foreach ($lignes as $ligne) {
            $reference[$ligne['type_operation']][] = $ligne['etape'];
        }

        return $reference;
    }

    /**
     * Modifications tracées par trigger SQL Server (table audit_modification_bdd, voir sql/historisation/audit_demande_intervention.sql) :
     * elles couvrent aussi les écritures faites hors de l'intranet (moulinette, SSMS...), les plus récentes en premier.
     *
     * @param string|null $recherche n° DIT, n° DA, n° OR (actuel de la DIT / de la DA ou ancienne / nouvelle valeur) ou valeur modifiée
     *
     * @return array<int, array<string, mixed>>|null null si la table d'audit n'existe pas sur cette base
     */
    public function rechercherModificationsBdd(?string $recherche, ?\DateTimeInterface $dateDebut, ?\DateTimeInterface $dateFin, int $limit = 100): ?array
    {
        $connexion = $this->getEntityManager()->getConnection();
        if (!$connexion->fetchOne("SELECT OBJECT_ID('dbo.audit_modification_bdd', 'U')")) {
            return null;
        }

        $conditions = [];
        $params = [];
        if ($recherche !== null && trim($recherche) !== '') {
            $conditions[] = '(a.reference LIKE :recherche OR di.numero_or LIKE :recherche OR da.numero_or LIKE :recherche OR da.numero_demande_dit LIKE :recherche'
                . ' OR a.ancienne_valeur LIKE :recherche OR a.nouvelle_valeur LIKE :recherche)';
            $params['recherche'] = '%' . trim($recherche) . '%';
        }
        if ($dateDebut) {
            $conditions[] = 'a.date_modification >= :dateDebut';
            $params['dateDebut'] = $dateDebut->format('Y-m-d 00:00:00');
        }
        if ($dateFin) {
            $conditions[] = 'a.date_modification <= :dateFin';
            $params['dateFin'] = $dateFin->format('Y-m-d 23:59:59');
        }

        // libellé des statuts DIT, pour afficher "AFFECTEE SECTION" plutôt que 51
        $sql = sprintf(
            "SELECT TOP %d a.date_modification, a.nom_table, a.reference, a.colonne, a.ancienne_valeur, a.nouvelle_valeur,
                    a.login_sql, a.poste, a.programme, sa.Description AS ancien_libelle, sn.Description AS nouveau_libelle,
                    da.art_refp, da.numero_version
             FROM audit_modification_bdd a
                LEFT JOIN demande_intervention di ON a.nom_table = 'demande_intervention' AND di.id = a.id_ligne
                LEFT JOIN da_afficher da ON a.nom_table = 'da_afficher' AND da.id = a.id_ligne
                LEFT JOIN Statut_demande sa ON a.colonne = 'id_statut_demande' AND CAST(sa.ID_Statut_Demande AS NVARCHAR(255)) = a.ancienne_valeur
                LEFT JOIN Statut_demande sn ON a.colonne = 'id_statut_demande' AND CAST(sn.ID_Statut_Demande AS NVARCHAR(255)) = a.nouvelle_valeur
             %s
             ORDER BY a.date_modification DESC, a.id DESC",
            $limit,
            $conditions ? 'WHERE ' . implode(' AND ', $conditions) : ''
        );

        return $connexion->fetchAllAssociative($sql, $params);
    }

    /**
     * Évènements du watcher sur les dossiers DocuWare (null si la table n'existe pas sur cette base)
     */
    public function rechercherSurveillanceDocuware(?string $recherche, ?\DateTimeInterface $dateDebut, ?\DateTimeInterface $dateFin, int $limit = 100): ?array
    {
        $connexion = $this->getEntityManager()->getConnection();
        if (!$connexion->fetchOne("SELECT OBJECT_ID('dbo.surveillance_docuware', 'U')")) {
            return null;
        }

        $conditions = [];
        $params = [];
        if ($recherche !== null && trim($recherche) !== '') {
            $conditions[] = '(s.nom_fichier LIKE :recherche OR s.sous_dossier LIKE :recherche)';
            $params['recherche'] = '%' . trim($recherche) . '%';
        }
        if ($dateDebut) {
            $conditions[] = 's.date_evt >= :dateDebut';
            $params['dateDebut'] = $dateDebut->format('Y-m-d 00:00:00');
        }
        if ($dateFin) {
            $conditions[] = 's.date_evt <= :dateFin';
            $params['dateFin'] = $dateFin->format('Y-m-d 23:59:59');
        }

        // un fichier "Created" puis "Deleted" au même chemin = récupéré par DocuWare ; sinon il attend encore dans le dossier
        $sql = sprintf(
            "SELECT TOP %d s.id, s.date_evt, s.source, s.evenement, s.sous_dossier, s.nom_fichier, s.chemin, s.ancien_chemin, s.taille, s.message,
                    CASE WHEN s.evenement = 'Created' AND EXISTS (
                        SELECT 1 FROM surveillance_docuware d WHERE d.chemin = s.chemin AND d.evenement = 'Deleted' AND d.date_evt >= s.date_evt
                    ) THEN 1 ELSE 0 END AS recupere
             FROM surveillance_docuware s
             %s
             ORDER BY s.date_evt DESC, s.id DESC",
            $limit,
            $conditions ? 'WHERE ' . implode(' AND ', $conditions) : ''
        );

        return $connexion->fetchAllAssociative($sql, $params);
    }

    /**
     * Statistiques de l'onglet "Tableau de bord" sur la période : une opération = un id_suivi,
     * son résultat est celui de sa pire étape (ECHEC/INTERROMPU > ALERTE > EN_COURS > SUCCES)
     *
     * @return array{kpi: array, parJour: array, parType: array, etapesEchec: array, parUtilisateur: array}
     */
    public function tableauDeBord(\DateTimeInterface $debut, \DateTimeInterface $fin, bool $sansControles = true): array
    {
        $connexion = $this->getEntityManager()->getConnection();
        $params    = [$debut->format('Y-m-d 00:00:00'), $fin->format('Y-m-d 23:59:59')];
        // blocages métier (règles de gestion refusées) : ce ne sont pas des erreurs techniques
        $controles = "etape IN (N'Contrôles', N'Contrôles de blocage') AND statut IN ('ECHEC', 'INTERROMPU')";
        $sansOperationsBloquees = $sansControles ? "HAVING SUM(CASE WHEN $controles THEN 1 ELSE 0 END) = 0" : '';
        $sansEtapesControle = $sansControles ? "AND NOT ($controles)" : '';
        $operations = "WITH ops AS (
                SELECT id_suivi, MIN(type_operation) AS type_operation, MIN(utilisateur) AS utilisateur,
                       MIN(date_debut) AS debut,
                       DATEDIFF(MILLISECOND, MIN(date_debut), MAX(COALESCE(date_fin, date_debut))) AS duree_ms,
                       CASE
                           WHEN SUM(CASE WHEN statut IN ('ECHEC', 'INTERROMPU') THEN 1 ELSE 0 END) > 0 THEN 'ECHEC'
                           WHEN SUM(CASE WHEN statut = 'ALERTE' THEN 1 ELSE 0 END) > 0 THEN 'ALERTE'
                           WHEN SUM(CASE WHEN statut = 'EN_COURS' THEN 1 ELSE 0 END) > 0 THEN 'EN_COURS'
                           ELSE 'SUCCES'
                       END AS resultat
                FROM historique_operation_etape
                WHERE date_debut BETWEEN ? AND ?
                GROUP BY id_suivi
                $sansOperationsBloquees
            ) ";
        $compteurs = "COUNT(*) AS total,
                SUM(CASE WHEN resultat = 'SUCCES' THEN 1 ELSE 0 END) AS succes,
                SUM(CASE WHEN resultat = 'ALERTE' THEN 1 ELSE 0 END) AS alertes,
                SUM(CASE WHEN resultat = 'ECHEC' THEN 1 ELSE 0 END) AS echecs,
                AVG(CAST(duree_ms AS BIGINT)) AS duree_moyenne,
                MAX(duree_ms) AS duree_max";

        return [
            'kpi' => $connexion->fetchAssociative($operations . "SELECT $compteurs FROM ops", $params) ?: [],
            'parJour' => $connexion->fetchAllAssociative(
                $operations . "SELECT CONVERT(VARCHAR(10), debut, 23) AS jour, $compteurs FROM ops GROUP BY CONVERT(VARCHAR(10), debut, 23) ORDER BY jour",
                $params
            ),
            'parType' => $connexion->fetchAllAssociative(
                $operations . "SELECT type_operation, $compteurs FROM ops GROUP BY type_operation ORDER BY total DESC",
                $params
            ),
            'parUtilisateur' => $connexion->fetchAllAssociative(
                $operations . "SELECT TOP 10 COALESCE(utilisateur, '-') AS utilisateur, $compteurs FROM ops GROUP BY utilisateur ORDER BY total DESC",
                $params
            ),
            // étapes qui cassent le plus souvent
            'etapesEchec' => $connexion->fetchAllAssociative(
                "SELECT TOP 10 type_operation, etape, COUNT(*) AS nombre, MAX(date_debut) AS derniere
                 FROM historique_operation_etape
                 WHERE date_debut BETWEEN ? AND ? AND statut IN ('ECHEC', 'INTERROMPU') $sansEtapesControle
                 GROUP BY type_operation, etape
                 ORDER BY nombre DESC",
                $params
            ),
        ];
    }

    public function rechercherSuivis(?string $recherche, ?string $typeOperation, bool $problemeSeulement, ?\DateTimeInterface $dateDebut, ?\DateTimeInterface $dateFin, ?int $idHistorique = null, bool $nonLuSeulement = false, int $limit = 100, ?int $idTache = null): array
    {
        $qb = $this->createQueryBuilder('e')
            ->select('e.idSuivi, MIN(e.dateDebut) AS HIDDEN debut')
            ->groupBy('e.idSuivi')
            ->orderBy('debut', 'DESC')
            ->setMaxResults($limit);

        if ($recherche !== null && trim($recherche) !== '') {
            $qb->andWhere('e.numeroDocument LIKE :recherche OR e.utilisateur LIKE :recherche')
                ->setParameter('recherche', '%' . trim($recherche) . '%');
        }
        if ($typeOperation) {
            $qb->andWhere('e.typeOperation = :type')->setParameter('type', $typeOperation);
        }
        if ($idHistorique) {
            $qb->andWhere('e.idHistoriqueOperation = :idHistorique')->setParameter('idHistorique', $idHistorique);
        }
        if ($idTache) {
            $qb->andWhere('e.idTache = :idTache')->setParameter('idTache', $idTache);
        }
        if ($dateDebut) {
            $qb->andWhere('e.dateDebut >= :dateDebut')->setParameter('dateDebut', $dateDebut->format('Y-m-d 00:00:00'));
        }
        if ($dateFin) {
            $qb->andWhere('e.dateDebut <= :dateFin')->setParameter('dateFin', $dateFin->format('Y-m-d 23:59:59'));
        }
        // une opération non lue n'a de sens que si elle est en problème
        if ($problemeSeulement || $nonLuSeulement) {
            $qb->having('SUM(CASE WHEN e.statut <> :succes THEN 1 ELSE 0 END) > 0')
                ->setParameter('succes', SuiviOperationService::STATUT_SUCCES);
        }
        if ($nonLuSeulement) {
            $qb->andWhere('e.estLu = :nonLu')->setParameter('nonLu', false);
        }

        $idsSuivi = array_column($qb->getQuery()->getScalarResult(), 'idSuivi');
        if (empty($idsSuivi)) {
            return [];
        }

        /** @var HistoriqueOperationEtape[] $etapes */
        $etapes = $this->createQueryBuilder('e')
            ->where('e.idSuivi IN (:ids)')
            ->setParameter('ids', $idsSuivi)
            ->orderBy('e.ordre', 'ASC')
            ->getQuery()
            ->getResult();

        // regroupement par opération, dans l'ordre de la recherche
        $suivis = array_fill_keys($idsSuivi, []);
        foreach ($etapes as $etape) {
            $suivis[$etape->getIdSuivi()][] = $etape;
        }

        $resultat = [];
        foreach ($suivis as $idSuivi => $etapesSuivi) {
            $resultat[] = [
                'idSuivi' => $idSuivi,
                'statut'  => $this->statutGlobal($etapesSuivi),
                'etapes'  => $etapesSuivi,
            ];
        }

        return $resultat;
    }

    /**
     * Marque toutes les étapes d'une opération comme lues (ou non lues)
     *
     * @return int nombre d'étapes mises à jour
     */
    public function marquerLu(string $idSuivi, bool $lu, ?string $utilisateur): int
    {
        return $this->createQueryBuilder('e')
            ->update()
            ->set('e.estLu', ':lu')
            ->set('e.luPar', ':luPar')
            ->set('e.dateLecture', ':dateLecture')
            ->where('e.idSuivi = :idSuivi')
            ->setParameter('lu', $lu)
            ->setParameter('luPar', $lu ? $utilisateur : null)
            ->setParameter('dateLecture', $lu ? new \DateTime() : null)
            ->setParameter('idSuivi', $idSuivi)
            ->getQuery()
            ->execute();
    }

    /**
     * ECHEC si une étape a échoué, INTERROMPU si une étape ne s'est pas terminée,
     * ALERTE si un contrôle signale une anomalie, sinon SUCCES
     *
     * @param HistoriqueOperationEtape[] $etapes
     */
    private function statutGlobal(array $etapes): string
    {
        $statuts = array_map(function (HistoriqueOperationEtape $etape) {
            return $etape->getStatut();
        }, $etapes);

        if (in_array(SuiviOperationService::STATUT_ECHEC, $statuts, true)) {
            return SuiviOperationService::STATUT_ECHEC;
        }
        if (in_array(SuiviOperationService::STATUT_INTERROMPU, $statuts, true)) {
            return SuiviOperationService::STATUT_INTERROMPU;
        }
        // une étape non terminée est encore en cours tant qu'elle est récente, sinon le script s'est arrêté sans la fermer
        // dates enregistrées en heure de Madagascar (SuiviOperationService::formaterDate) : comparaison en texte, indépendante du fuseau PHP
        $limite = (new \DateTime('-' . self::DELAI_EN_COURS_MINUTES . ' minutes', new \DateTimeZone('Indian/Antananarivo')))->format('Y-m-d H:i:s');
        foreach ($etapes as $etape) {
            if ($etape->getStatut() === SuiviOperationService::STATUT_EN_COURS) {
                return $etape->getDateDebut()->format('Y-m-d H:i:s') > $limite
                    ? SuiviOperationService::STATUT_EN_COURS
                    : SuiviOperationService::STATUT_INTERROMPU;
            }
        }
        if (in_array(SuiviOperationService::STATUT_ALERTE, $statuts, true)) {
            return SuiviOperationService::STATUT_ALERTE;
        }

        return SuiviOperationService::STATUT_SUCCES;
    }
}
