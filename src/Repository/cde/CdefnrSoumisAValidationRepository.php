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
        } catch (\Doctrine\ORM\NoResultException | \Doctrine\ORM\NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * Methode qui recupère 
     *
     * @param string $numeroFournisseur
     * @return void
     */
    public function findNumCommandeValideNonAnnuler(string $numeroFournisseur, int $typeId, array $excludedCommands = [])
    {
        $qb = $this->createQueryBuilder('cfr');

        // Sous-requête pour récupérer la version maximale pour chaque numero_commande_fournisseur
        $subQuery = $this->createQueryBuilder('sub')
            ->select('MAX(sub.numVersion)')
            ->where('sub.numCdeFournisseur = cfr.numCdeFournisseur')
            ->andWhere('sub.codeFournisseur = :numFrn')
            ->getDQL();

        $qb->select('cfr.numCdeFournisseur')
            ->where('cfr.codeFournisseur = :numFrn')
            ->andWhere('cfr.numVersion = (' . $subQuery . ')')
            ->andWhere('cfr.statut = :statut')
            ->andWhere('cfr.estFacture = :fac')
            ->setParameter('numFrn', $numeroFournisseur)
            ->setParameter('statut', 'Validé')
            ->setParameter('fac', $typeId == 2 ? 1 : 0);

        if (!empty($excludedCommands)) {
            $qb->andWhere($qb->expr()->notIn('cfr.numCdeFournisseur', ':excluded'))
                ->setParameter('excluded', $excludedCommands);
        }

        return $qb->getQuery()->getSingleColumnResult();
    }

    public function bcExists(?string $numCde): bool
    {
        $qb = $this->createQueryBuilder('cfr');
        $qb->select('1')
            ->where('cfr.numCdeFournisseur = :numCde')
            ->setParameter('numCde', $numCde)
            ->setMaxResults(1);

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
            return $result !== null;
        } catch (\Exception $e) {
            return false;
        }
    }
}
