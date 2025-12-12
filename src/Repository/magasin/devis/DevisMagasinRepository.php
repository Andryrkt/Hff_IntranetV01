<?php

namespace App\Repository\magasin\devis;

use App\Repository\Interfaces\LatestSumOfLinesRepositoryInterface;
use App\Repository\Interfaces\LatestSumOfMontantRepositoryInterface;
use App\Repository\Interfaces\StatusRepositoryInterface;
use Doctrine\ORM\EntityRepository;

class DevisMagasinRepository extends EntityRepository implements StatusRepositoryInterface, LatestSumOfLinesRepositoryInterface, LatestSumOfMontantRepositoryInterface
{
    public function getNumeroVersionMax(string $numDevis)
    {
        $numeroVersionMax = $this->createQueryBuilder('dsv')
            ->select('MAX(dsv.numeroVersion)')
            ->where('dsv.numeroDevis = :numDevis')
            ->setParameter('numDevis', $numDevis)
            ->getQuery()
            ->getSingleScalarResult();

        return (int)$numeroVersionMax;
    }

    public function findLatestStatusByIdentifier(string $identifier): ?string
    {
        $result = $this->createQueryBuilder('d')
            ->select('d.statutDw')
            ->where('d.numeroDevis = :identifier')
            ->setParameter('identifier', $identifier)
            ->orderBy('d.numeroVersion', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getScalarResult();

        return $result[0]['statutDw'] ?? null;
    }

    public function findLatestSumOfLinesByIdentifier(string $identifier): ?int
    {
        $result = $this->createQueryBuilder('d')
            ->select('d.sommeNumeroLignes')
            ->where('d.numeroDevis = :identifier')
            ->setParameter('identifier', $identifier)
            ->orderBy('d.numeroVersion', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getScalarResult();

        $sum = $result[0]['sommeNumeroLignes'] ?? null;

        return $sum !== null ? (int)$sum : null;
    }

    public function findLatestSumOfMontantByIdentifier(string $identifier): ?float
    {
        $result = $this->createQueryBuilder('d')
            ->select('d.montantDevis')
            ->where('d.numeroDevis = :identifier')
            ->setParameter('identifier', $identifier)
            ->orderBy('d.numeroVersion', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getScalarResult();

        $sum = $result[0]['montantDevis'] ?? null;

        return $sum !== null ? (float)$sum : null;
    }

    // récupération des statuts DW
    public function getStatutsDw(): array
    {
        $result = $this->createQueryBuilder('d')
            ->select('DISTINCT d.statutDw')
            ->getQuery()
            ->getScalarResult();

        return array_column($result, 'statutDw');
    }

    // récupération des statuts BC
    public function getStatutsBc(): array
    {
        $result = $this->createQueryBuilder('d')
            ->select('DISTINCT d.statutBc')
            ->where('d.statutBc IS NOT NULL')
            ->andWhere('d.statutBc != :emptyString')
            ->setParameter('emptyString', '')
            ->getQuery()
            ->getScalarResult();

        return array_column($result, 'statutBc');
    }

    public function getStatutDwEtStatutBc(string $numeroDevis): ?array
    {
        $result = $this->createQueryBuilder('d')
            ->select('d.statutDw, d.statutBc')
            ->where('d.numeroDevis = :numeroDevis')
            ->setParameter('numeroDevis', $numeroDevis)
            ->orderBy('d.numeroVersion', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getScalarResult();

        return $result[0] ?? null;
    }

    public function getFileNameMigration(string $numeroDevis)
    {
        return  $this->createQueryBuilder('d')
            ->select('d.nomFichier')
            ->where('d.numeroDevis = :numeroDevis')
            ->setParameter('numeroDevis', $numeroDevis)
            ->orderBy('d.numeroVersion', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getNumeroDevisMigration()
    {
        $resultat = $this->createQueryBuilder('d')
            ->select('DISTINCT d.numeroDevis as numeroDevis')
            ->getQuery()
            ->getScalarResult();

        return array_column($resultat, 'numeroDevis');
    }
}
