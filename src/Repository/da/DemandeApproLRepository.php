<?php

namespace App\Repository\da;

use Doctrine\ORM\EntityRepository;

class DemandeApproLRepository extends EntityRepository
{
    public function getNumeroVersionMax(string $numeroDemandeAppro)
    {
        $numeroVersionMax = $this->createQueryBuilder('dal')
            ->select('MAX(dal.numeroVersion)')
            ->where('dal.numeroDemandeAppro = :numDa')
            ->setParameter('numDa', $numeroDemandeAppro)
            ->getQuery()
            ->getSingleScalarResult();

        return $numeroVersionMax;
    }

    /**
     * @return array<int, array{numeroDemandeAppro: string, fileNames: array}>
     */
    public function findAttachmentsByNumeroDit(string $numDit): array
    {
        return $this->createQueryBuilder('dal')
            ->select('dal.numeroDemandeAppro, dal.fileNames')
            ->where('dal.numeroDit = :numDit')
            ->setParameter('numDit', $numDit)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * @return array<int, array{numeroDemandeAppro: string, fileNames: array}>
     */
    public function findAttachmentsByNumeroDA(string $numDa): array
    {
        return $this->createQueryBuilder('dal')
            ->select('dal.numeroDemandeAppro, dal.fileNames')
            ->where('dal.numeroDemandeAppro = :numDa')
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
