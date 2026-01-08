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


    public function getQteRefPu(string $numDit)
    {
        // Étape 1 : Récupérer le numeroVersion maximum
        $numeroVersionMax = $this->createQueryBuilder('dal')
            ->select('MAX(dal.numeroVersion)')
            ->where('dal.numeroDit = :numDit')
            ->setParameter('numDit', $numDit)
            ->getQuery()
            ->getSingleScalarResult();

        // Étape 2 : Récupérer la quantité, la référence et le prix unitaire
        return $this->createQueryBuilder('dal')
            ->select('dal.qteDem as quantite, dal.artRefp as reference, dal.prixUnitaire as montant')
            ->where('dal.numeroDit = :numDit')
            ->andWhere('dal.numeroVersion = :numVersion')
            ->setParameters([
                'numDit' => $numDit,
                'numVersion' => $numeroVersionMax
            ])
            ->orderBy('dal.artRefp', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getJoursDispo(?string $numeroDemandeAppro, string $reference)
    {
        // Étape 1 : Récupérer le numeroVersion maximum
        $numeroVersionMax = $this->createQueryBuilder('dal')
            ->select('MAX(dal.numeroVersion)')
            ->where('dal.numeroDemandeAppro = :numDa')
            ->setParameter('numDa', $numeroDemandeAppro)
            ->getQuery()
            ->getSingleScalarResult();

        if ($numeroVersionMax === null) {
            return 0; // ou une valeur par défaut, selon vos besoins
        }

        $nbrJour =  $this->createQueryBuilder('dal')
            ->select('dal.joursDispo')
            ->where('dal.numeroDemandeAppro = :numDa')
            ->andWhere('dal.numeroVersion = :numVersion')
            ->andWhere('dal.artRefp = :ref')
            ->setParameters([
                'numDa' => $numeroDemandeAppro,
                'numVersion' => $numeroVersionMax,
                'ref' => $reference
            ]);

        $result  = $nbrJour->getQuery()->getSingleScalarResult();

        return $result !== null ? (int) $result : 0;
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
