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
     * @return array<int, array{numeroDemandeAppro: string, fileNames: array}>
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

    /**
     * @return array<int, array{numeroDemandeAppro: string, fileNames: array}>
     */
    public function findAttachmentsByNumeroDA(string $numDa): array
    {
        return $this->createQueryBuilder('dalr')
            ->select('dalr.numeroDemandeAppro, dalr.fileNames')
            ->where('dalr.numeroDemandeAppro = :numDa')
            ->setParameter('numDa', $numDa)
            ->getQuery()
            ->getArrayResult();
    }

    public function deleteByNumDaAndLineNumbers(string $numDa, array $lines): void
    {
        if (!$numDa || !$lines) return; // rien à faire

        try {
            $this->createQueryBuilder('d')
                ->delete()
                ->where('d.numeroDemandeAppro =:numDa')
                ->andWhere('d.numeroLigne IN (:lines)')
                ->setParameter('lines', $lines)
                ->setParameter('numDa', $numDa)
                ->getQuery()
                ->execute();
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }
}
