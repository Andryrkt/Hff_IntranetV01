<?php

namespace App\Repository\da;

use Doctrine\ORM\EntityRepository;

class DaNotificationFacBlRepository extends EntityRepository
{
    /**
     * Liste des notifications, les plus récentes en premier
     *
     * @param bool $anomalieSeulement ne retourner que les notifications en anomalie
     */
    public function findDernieres(bool $anomalieSeulement = false, int $limit = 200): array
    {
        $qb = $this->createQueryBuilder('n')
            ->orderBy('n.dateCreation', 'DESC')
            ->setMaxResults($limit);

        if ($anomalieSeulement) {
            $qb->andWhere('n.estAnomalie = 1');
        }

        return $qb->getQuery()->getResult();
    }

    public function countNonLues(): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.estLu = 0')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
