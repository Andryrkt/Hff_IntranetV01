<?php

namespace App\Repository\admin\historisation\pageConsultation;

use App\Entity\admin\historisation\pageConsultation\PageConsultationSearch;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

class UserLoggerRepository extends EntityRepository
{
    public function findPaginatedAndFiltered(int $page = 1, int $limit = 10, ?PageConsultationSearch $pageConsultationSearch = null)
    {
        $queryBuilder = $this->createQueryBuilder('ul');

        $this->dateFinDebut($queryBuilder, $pageConsultationSearch);
        $this->conditionSaisieLibre($queryBuilder, $pageConsultationSearch);

        $queryBuilder
            ->orderBy('ul.dateConsultation', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
        ;

        $paginator = new DoctrinePaginator($queryBuilder->getQuery());

        $totalItems = count($paginator);
        $lastPage = ceil($totalItems / $limit);

        return [
            'data'        => iterator_to_array($paginator->getIterator()), // Convertir en tableau si nécessaire
            'totalItems'  => $totalItems,
            'currentPage' => $page,
            'lastPage'    => $lastPage,
        ];
    }

    private function dateFinDebut($queryBuilder, PageConsultationSearch $pageConsultationSearch)
    {
        //filtre date debut
        if (!empty($pageConsultationSearch->getDateDebut())) {
            $queryBuilder
                ->andWhere('ul.dateConsultation >= :dateDebut')
                ->setParameter('dateDebut', $pageConsultationSearch->getDateDebut())
            ;
        }

        //filtre date fin
        if (!empty($pageConsultationSearch->getDateFin())) {
            $queryBuilder
                ->andWhere('ul.dateConsultation <= :dateFin')
                ->setParameter('dateFin', $pageConsultationSearch->getDateFin())
            ;
        }
    }

    private function conditionSaisieLibre($queryBuilder, PageConsultationSearch $pageConsultationSearch)
    {
        //filtre utilisateur
        if (!empty($pageConsultationSearch->getUtilisateur())) {
            $queryBuilder
                ->andWhere('ul.utilisateur LIKE :user')
                ->setParameter('user', '%' . $pageConsultationSearch->getUtilisateur() . '%')
            ;
        }

        //filtre nom de page
        if (!empty($pageConsultationSearch->getNomPage())) {
            $queryBuilder
                ->andWhere('ul.nom_page LIKE :pagename')
                ->setParameter('pagename', '%' . $pageConsultationSearch->getNomPage() . '%')
            ;
        }

        //filtre nom de machine
        if (!empty($pageConsultationSearch->getMachineUser())) {
            $queryBuilder
                ->andWhere('ul.machineUser LIKE :machine')
                ->setParameter('machine', '%' . $pageConsultationSearch->getMachineUser() . '%')
            ;
        }
    }

    /**
     * Consultations de la période, les plus récentes en premier
     *
     * @param array{recherche: string, utilisateur: string, page: string} $filtres
     * @param int $limite valeur contrôlée par l'appelant (injectée dans le TOP)
     *
     * @return array{total: int, lignes: array}
     */
    public function rechercher(array $filtres, \DateTimeInterface $debut, \DateTimeInterface $fin, int $limite): array
    {
        $conditions = ['date_heure_consultation BETWEEN ? AND ?'];
        $params     = [$debut->format('Y-m-d 00:00:00'), $fin->format('Y-m-d 23:59:59')];
        if ($filtres['utilisateur'] !== '') {
            $conditions[] = 'utilisateur = ?';
            $params[]     = $filtres['utilisateur'];
        }
        if ($filtres['page'] !== '') {
            $conditions[] = 'nom_page = ?';
            $params[]     = $filtres['page'];
        }
        if ($filtres['recherche'] !== '') {
            $conditions[] = '(utilisateur LIKE ? OR nom_page LIKE ? OR machine_utilisateur LIKE ? OR params LIKE ?)';
            array_push($params, ...array_fill(0, 4, '%' . $filtres['recherche'] . '%'));
        }
        $where     = implode(' AND ', $conditions);
        $connexion = $this->getEntityManager()->getConnection();

        $lignes = $connexion->fetchAllAssociative(
            'SELECT TOP (' . (int) $limite . ') id, utilisateur, nom_page, params, date_heure_consultation AS date_consultation, machine_utilisateur
             FROM Log_utilisateur WHERE ' . $where . ' ORDER BY date_heure_consultation DESC, id DESC',
            $params
        );
        foreach ($lignes as &$ligne) {
            $ligne['params'] = $ligne['params'] ? (json_decode($ligne['params'], true) ?: []) : [];
        }

        return [
            'total'  => (int) $connexion->fetchOne('SELECT COUNT(*) FROM Log_utilisateur WHERE ' . $where, $params),
            'lignes' => $lignes,
        ];
    }

    /**
     * Statistiques de la période : indicateurs, par jour, par heure, pages et utilisateurs les plus actifs
     */
    public function tableauDeBord(\DateTimeInterface $debut, \DateTimeInterface $fin): array
    {
        $connexion = $this->getEntityManager()->getConnection();
        $params    = [$debut->format('Y-m-d 00:00:00'), $fin->format('Y-m-d 23:59:59')];
        $periode   = 'FROM Log_utilisateur WHERE date_heure_consultation BETWEEN ? AND ?';

        return [
            'kpi' => $connexion->fetchAssociative(
                "SELECT COUNT(*) AS total, COUNT(DISTINCT utilisateur) AS utilisateurs, COUNT(DISTINCT nom_page) AS pages,
                        COUNT(DISTINCT machine_utilisateur) AS machines, COUNT(DISTINCT CONVERT(VARCHAR(10), date_heure_consultation, 23)) AS jours
                 $periode",
                $params
            ) ?: [],
            'parJour' => $connexion->fetchAllAssociative(
                "SELECT CONVERT(VARCHAR(10), date_heure_consultation, 23) AS jour, COUNT(*) AS total, COUNT(DISTINCT utilisateur) AS utilisateurs
                 $periode GROUP BY CONVERT(VARCHAR(10), date_heure_consultation, 23) ORDER BY jour",
                $params
            ),
            'parHeure' => $connexion->fetchAllKeyValue(
                "SELECT DATEPART(HOUR, date_heure_consultation) AS heure, COUNT(*) AS total
                 $periode GROUP BY DATEPART(HOUR, date_heure_consultation) ORDER BY heure",
                $params
            ),
            'parPage' => $connexion->fetchAllAssociative(
                "SELECT TOP 15 nom_page, COUNT(*) AS total, COUNT(DISTINCT utilisateur) AS utilisateurs, MAX(date_heure_consultation) AS derniere
                 $periode GROUP BY nom_page ORDER BY total DESC",
                $params
            ),
            'parUtilisateur' => $connexion->fetchAllAssociative(
                "SELECT TOP 15 utilisateur, COUNT(*) AS total, COUNT(DISTINCT nom_page) AS pages, MAX(date_heure_consultation) AS derniere
                 $periode GROUP BY utilisateur ORDER BY total DESC",
                $params
            ),
        ];
    }

    /**
     * Utilisateurs ayant consulté une page dans les $minutes dernières minutes (= durée de la session), avec leur dernière page
     * shortcut: activité récente, pas les sessions réellement ouvertes (non stockées en base)
     */
    public function connectes(int $minutes = 15): array
    {
        return $this->getEntityManager()->getConnection()->fetchAllAssociative(
            "SELECT utilisateur, id_utilisateur, nom_page, date_heure_consultation AS derniere, machine_utilisateur, nb
             FROM (
                SELECT utilisateur, id_utilisateur, nom_page, date_heure_consultation, machine_utilisateur,
                       ROW_NUMBER() OVER (PARTITION BY utilisateur ORDER BY date_heure_consultation DESC, id DESC) AS rang,
                       COUNT(*) OVER (PARTITION BY utilisateur) AS nb
                FROM Log_utilisateur
                WHERE date_heure_consultation >= DATEADD(MINUTE, -" . (int) $minutes . ", GETDATE())
             ) d
             WHERE rang = 1
             ORDER BY derniere DESC"
        );
    }

    /**
     * Noms des pages déjà consultées (filtre de la liste)
     *
     * @return string[]
     */
    public function nomsPages(): array
    {
        return $this->getEntityManager()->getConnection()->fetchFirstColumn(
            'SELECT DISTINCT nom_page FROM Log_utilisateur ORDER BY nom_page'
        );
    }
}
