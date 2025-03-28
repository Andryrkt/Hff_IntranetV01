<?php

namespace App\Repository\cde;

use Doctrine\ORM\EntityRepository;

class CdefnrSoumisAValidationRepository extends EntityRepository 
{
    public function findNumeroVersionMax(string $numCde)
    {
        $numeroVersionMax = $this->createQueryBuilder('cde')
            ->select('MAX(cde.numVersion)')
            ->where('cde.numCdeFournisseur = :numCdeFournisseur')
            ->setParameter('numCdeFournisseur', $numCde)
            ->getQuery()
            ->getSingleScalarResult(); 
    
        return $numeroVersionMax;
    }

    public function findStatut(string $numCde): ?string
    {
        try {
            return $this->createQueryBuilder('cde')
                ->select('cde.statut')
                ->where('cde.numCdeFournisseur = :numCdeFournisseur')
                ->andWhere('cde.numVersion = (
                    SELECT MAX(cde2.numVersion) 
                    FROM App\Entity\cde\CdefnrSoumisAValidation cde2 
                    WHERE cde2.numCdeFournisseur = :numCdeFournisseur
                )')
                ->setParameter('numCdeFournisseur', $numCde)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException|\Doctrine\ORM\NonUniqueResultException $e) {
            return null;
        }
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