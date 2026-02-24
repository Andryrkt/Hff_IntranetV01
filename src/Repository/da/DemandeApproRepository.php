<?php

namespace App\Repository\da;

use App\Entity\admin\utilisateur\User;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;

class DemandeApproRepository extends EntityRepository
{
    public function getStatut($numDit)
    {
        $result = $this->createQueryBuilder('da')
            ->select('DISTINCT da.statutDal')
            ->where('da.numeroDemandeDit = :numDit')
            ->setParameter('numDit', $numDit)
            ->getQuery()
            ->getOneOrNullResult();;

        return $result ? $result['statutDal'] : null;
    }

    public function getStatutDa(string $numDa)
    {
        $result = $this->createQueryBuilder('da')
            ->select('DISTINCT da.statutDal')
            ->where('da.numeroDemandeAppro = :numDa')
            ->setParameter('numDa', $numDa)
            ->getQuery()
            ->getOneOrNullResult();;

        return $result ? $result['statutDal'] : null;
    }

    public function getDistinctColumn($column)
    {
        return $this->createQueryBuilder('da')
            ->select("DISTINCT da.$column")
            ->getQuery()
            ->getResult();
    }

    public function findAvecDernieresDALetLRParNumero(string $numeroDemandeAppro): ?DemandeAppro
    {
        return $this->createQueryBuilder('da')
            ->leftJoin('da.DAL', 'dal')
            ->addSelect('dal')
            ->leftJoin('dal.demandeApproLR', 'dalr')
            ->addSelect('dalr')
            ->where('da.numeroDemandeAppro = :numero')
            ->andWhere('dal.deleted = 0')
            ->orderBy('dal.numeroVersion', 'DESC') // Dernière version en premier
            ->setParameter('numero', $numeroDemandeAppro)
            ->setMaxResults(1) // On en garde qu’une seule
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAvecDernieresDALetLR($id): ?DemandeAppro
    {
        // Sous-requête pour trouver le numéro de version max des DAL pour cette DA
        $subQuery = $this->createQueryBuilder('dax')
            ->select('MAX(dax2.numeroVersion)')
            ->from(DemandeApproL::class, 'dax2')
            ->where('dax2.numeroDemandeAppro = da.numeroDemandeAppro')
            ->getDQL();

        return $this->createQueryBuilder('da')
            ->leftJoin('da.DAL', 'dal')
            ->addSelect('dal')
            ->leftJoin('dal.demandeApproLR', 'dalr')
            ->addSelect('dalr')
            ->where('da.id = :id')
            // On filtre pour ne garder que les DAL avec le numéro de version max
            ->andWhere("dal.numeroVersion = ($subQuery)")
            ->andWhere("dal.deleted = 0")
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getNumDit()
    {
        return $this->createQueryBuilder('da')
            ->select('da.numeroDemandeDit')
            ->where('da.statutDal IN (:statuts)')
            ->setParameter('statuts', [DemandeAppro::STATUT_VALIDE, DemandeAppro::STATUT_TERMINER])
            ->getQuery()
            ->getSingleColumnResult()
        ;
    }

    public function getAllNumDit()
    {
        return $this->createQueryBuilder('da')
            ->select('da.numeroDemandeDit')
            ->getQuery()
            ->getSingleColumnResult()
        ;
    }

    public function getNumDitDa(string $numDa)
    {
        try {
            $numDit = $this->createQueryBuilder('da')
                ->select('da.numeroDemandeDit')
                ->where('da.numeroDemandeAppro = :numDa')
                ->setParameter('numDa', $numDa)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
            $numDit = null; // ou une valeur par défaut
        }
        return $numDit;
    }

    public function getNumDa($numDit)
    {
        try {
            $numDa =  $this->createQueryBuilder('da')
                ->select('da.numeroDemandeAppro')
                ->where('da.numeroDemandeDit = :numDit')
                ->setParameter('numDit', $numDit)
                ->getQuery()
                ->getSingleColumnResult();
        } catch (NoResultException $e) {
            $numDa = null; // ou une valeur par défaut
        }
        return $numDa;
    }

    public function findAllNumDaValide(string $numDit)
    {
        return $this->createQueryBuilder('da')
            ->select('da.numeroDemandeAppro')
            ->where('da.numeroDemandeDit = :numDit')
            ->andWhere('da.statutDal = :statutValide')
            ->setParameter('numDit', $numDit)
            ->setParameter('statutValide', DemandeAppro::STATUT_VALIDE)
            ->getQuery()
            ->getSingleColumnResult();
    }
}
