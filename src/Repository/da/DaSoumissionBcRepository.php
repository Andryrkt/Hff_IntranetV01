<?php

namespace App\Repository\da;

use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;

class DaSoumissionBcRepository extends EntityRepository
{

    public function getNumeroVersionMax(string $numeroCde): ?int
    {
        $result = $this->createQueryBuilder('dabc')
            ->select('MAX(dabc.numeroVersion)')
            ->where('dabc.numeroCde = :numCde')
            ->setParameter('numCde', $numeroCde)
            ->getQuery()
            ->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);

        return $result !== null ? (int) $result : null;
    }

    public function getStatut(?string $numCde): ?string
    {
        // Étape 1 : Récupérer le numeroVersion maximum
        $numeroVersionMax = $this->createQueryBuilder('dabc')
            ->select('MAX(dabc.numeroVersion)')
            ->where('dabc.numeroCde = :numCde')
            ->setParameter('numCde', $numCde)
            ->getQuery()
            ->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);

        if ($numeroVersionMax === null) {
            return ''; // ou une valeur par défaut, selon vos besoins
        }

        // Étape 2 : Récupérer le statut correspondant
        $statut = $this->createQueryBuilder('dabc')
            ->select('DISTINCT dabc.statut')
            ->where('dabc.numeroCde = :numCde')
            ->andWhere('dabc.numeroVersion = :numVersion')
            ->setParameters([
                'numCde' => $numCde,
                'numVersion' => $numeroVersionMax
            ])
            ->getQuery()
            ->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);

        return $statut;
    }

    public function bcExists(?string $numCde): bool
    {
        $qb = $this->createQueryBuilder('dabc');
        $qb->select('1')
            ->where('dabc.numeroCde = :numCde')
            ->setParameter('numCde', $numCde)
            ->setMaxResults(1);

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
            return $result !== null;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getMontantBc(?string $numCde): ?float
    {
        $result = $this->createQueryBuilder('dabc')
            ->select('dabc.montantBc')
            ->where('dabc.numeroCde = :numCde')
            ->setParameter('numCde', $numCde)
            ->orderBy('dabc.numeroVersion', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);

        return $result !== null ? (float) $result : null;
    }
}
