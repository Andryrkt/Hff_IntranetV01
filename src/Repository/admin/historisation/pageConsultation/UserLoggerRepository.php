<?php

namespace App\Repository\admin\historisation\pageConsultation;

use App\Entity\admin\historisation\pageConsultation\PageConsultationSearch;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

class UserLoggerRepository extends EntityRepository
{
    public function findPaginatedAndFiltered(int $page = 1, int $limit = 10, PageConsultationSearch $pageConsultationSearch = null)
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
}
