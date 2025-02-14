<?php

namespace App\Repository\cde;

use Doctrine\ORM\EntityRepository;

class CdefnrSoumisAValidationRepository extends EntityRepository 
{
    public function findNumeroVersionMax($numCde)
    {
        $numeroVersionMax = $this->createQueryBuilder('cfr')
            ->select('MAX(cfr.numVersion)')
            ->where('cfr.numCdeFournisseur = :numCdeFournisseur')
            ->setParameter('numCdeFournisseur', $numCde)
            ->getQuery()
            ->getSingleScalarResult(); 
    
        return $numeroVersionMax;
    }

    /**
     * Methode qui recupère 
     *
     * @param string $numeroFournisseur
     * @return void
     */
    public function findNumCommandeValideNonAnnuler(string $numeroFournisseur)
    {
        $qb = $this->createQueryBuilder('cfr');

        // Sous-requête pour récupérer la version maximale pour chaque numero_commande_fournisseur
        $subQuery = $this->createQueryBuilder('sub')
            ->select('MAX(sub.numVersion)')
            ->where('sub.numCdeFournisseur = cfr.numCdeFournisseur')
            ->andWhere('sub.codeFournisseur = :numFrn')
            ->getDQL();

        return $qb->select('cfr.numCdeFournisseur')
            ->where('cfr.codeFournisseur = :numFrn')
            ->andWhere('cfr.numVersion = (' . $subQuery . ')')
            ->andWhere('cfr.statut = :statut')
            ->setParameters([
                'numFrn' => $numeroFournisseur,
                'statut' => 'Validé',
            ])
            ->getQuery()
            ->getSingleColumnResult();
    }

}