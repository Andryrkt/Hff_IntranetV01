<?php

namespace App\Repository\da;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

class DemandeApproLRRepository extends EntityRepository
{
    public function getDalrByPageAndRow(string $numDap, string $line, string $row)
    {
        return $this->createQueryBuilder('dalr')
            ->select('dalr')
            ->where('dalr.numeroDemandeAppro =:numDap')
            ->setParameter('numDap', $numDap)
            ->andWhere('dalr.numeroLigne =:line')
            ->setParameter('line', $line)
            ->andWhere('dalr.numLigneTableau =:row')
            ->setParameter('row', $row)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return array<int, array{numeroDemandeAppro: string, fileNames: string}>
     */
    public function findAttachmentsByNumeroDit(string $numDit): array
    {
        return $this->createQueryBuilder('dalr')
            ->select('dalr.numeroDemandeAppro, dalr.fileNames')
            ->where('dalr.numeroDemandeDit = :numDit')
            ->setParameter('numDit', $numDit)
            ->getQuery()
            ->getArrayResult();
    }
}
