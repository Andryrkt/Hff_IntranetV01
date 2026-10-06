<?php

namespace App\Repository\dw;

use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;

class DwBcClientRepository extends EntityRepository
{
    public function getPath(string $numeroCde): ?string
    {
        // Étape 1 : Récupérer le numeroVersion maximum
        $numeroVersionMax = $this->createQueryBuilder('d')
            ->select('MAX(d.numeroVersion)')
            ->where('d.numeroBc = :numeroBc')
            ->setParameter('numeroBc', $numeroCde)
            ->getQuery()
            ->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);

        if ($numeroVersionMax === null) {
            return null; // ou une valeur par défaut, selon vos besoins
        }

        return  $this->createQueryBuilder('d')
            ->select('d.path')
            ->where('d.numeroBc = :numeroBc')
            ->andWhere('d.numeroVersion = :numeroVersion')
            ->setParameters([
                'numeroBc' => $numeroCde,
                'numeroVersion' => $numeroVersionMax
            ])
            ->getQuery()
            ->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);
    }
}
