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
}
