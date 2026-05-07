<?php

namespace App\Repository\dw;

use Doctrine\ORM\EntityRepository;

class DwCommandeRepository extends EntityRepository
{
    public function findNumCdeDw(): array
    {
        return $this->createQueryBuilder('c')
            ->select('DISTINCT c.numeroCde as numcde')
            ->where('c.path IS NOT NULL')
            ->orderBy('c.numeroCde', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function findPathByNumeroCde(string $numeroCde): array
    {
        // Sous-requête pour la date max
        $subQuery = $this->createQueryBuilder('c2')
            ->select('MAX(c2.dateCreation)')
            ->where('c2.numeroCde = :numeroCde');

        // Requête principale
        return $this->createQueryBuilder('c')
            ->select('DISTINCT c.path, c.numeroCde')
            ->where('c.numeroCde = :numeroCde')
            ->andWhere('c.dateCreation = (' . $subQuery->getDQL() . ')')
            ->setParameter('numeroCde', $numeroCde)
            ->getQuery()
            ->getResult();
    }
}
