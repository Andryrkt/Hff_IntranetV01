<?php

namespace App\Repository\da;

use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;

class DaSoumissionFacBlRepository extends EntityRepository
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
            return null; // ou une valeur par défaut, selon vos besoins
        }

        // Étape 2 : Récupérer le statut correspondant
        $statut = $this->createQueryBuilder('dabc')
            ->select('dabc.statut')
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
}
