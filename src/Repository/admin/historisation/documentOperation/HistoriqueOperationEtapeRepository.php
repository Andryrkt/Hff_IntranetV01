<?php

namespace App\Repository\admin\historisation\documentOperation;

use App\Entity\admin\historisation\documentOperation\HistoriqueOperationEtape;
use App\Service\historiqueOperation\suivi\SuiviOperationService;
use Doctrine\ORM\EntityRepository;

class HistoriqueOperationEtapeRepository extends EntityRepository
{
    /**
     * Recherche les opérations suivies, les plus récentes en premier, avec leurs étapes dans l'ordre.
     *
     * @param string|null $recherche       n° de document ou utilisateur
     * @param string|null $typeOperation  une des constantes SuiviOperationService::TYPE_*
     * @param bool        $problemeSeulement ne garder que les opérations avec une étape en échec, interrompue ou jamais terminée
     * @param int|null    $idHistorique    opération rattachée à cette ligne de l'historique des opérations
     * @param bool        $nonLuSeulement  ne garder que les opérations en problème pas encore marquées comme lues
     *
     * @return array<int, array{idSuivi: string, statut: string, etapes: HistoriqueOperationEtape[]}>
     */
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

    public function rechercherSuivis(?string $recherche, ?string $typeOperation, bool $problemeSeulement, ?\DateTimeInterface $dateDebut, ?\DateTimeInterface $dateFin, ?int $idHistorique = null, bool $nonLuSeulement = false, int $limit = 100): array
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
        if (in_array(SuiviOperationService::STATUT_INTERROMPU, $statuts, true) || in_array(SuiviOperationService::STATUT_EN_COURS, $statuts, true)) {
            return SuiviOperationService::STATUT_INTERROMPU;
        }
        if (in_array(SuiviOperationService::STATUT_ALERTE, $statuts, true)) {
            return SuiviOperationService::STATUT_ALERTE;
        }

        return SuiviOperationService::STATUT_SUCCES;
    }
}
