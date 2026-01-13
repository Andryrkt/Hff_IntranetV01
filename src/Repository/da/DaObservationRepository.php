<?php

namespace App\Repository\da;

use Doctrine\ORM\EntityRepository;

class DaObservationRepository extends EntityRepository
{
    /**
     * @return array<int, array{numeroDemandeAppro: string, fileNames: array}>
     */
    public function findAttachmentsByNumeroDA(string $numDa): array
    {
        return $this->createQueryBuilder('do')
            ->select('do.numDa, do.fileNames')
            ->where('do.numDa = :numDa')
            ->setParameter('numDa', $numDa)
            ->getQuery()
            ->getArrayResult();
    }
}
