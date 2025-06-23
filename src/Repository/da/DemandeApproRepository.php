<?php

namespace App\Repository\da;

use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use Doctrine\ORM\EntityRepository;

class DemandeApproRepository extends EntityRepository
{
    public function findDaData(array $criteria = [])
    {
        $qb = $this->createQueryBuilder('da')
            ->select('da')
            ->orderBy('da.id', 'DESC');

        // Filtre sur le numero DIT
        if (isset($criteria['numDit'])) {
            $qb->andWhere("da.numeroDemandeDit LIKE :numDit")
                ->setParameter('numDit', '%' . $criteria['numDit'] . '%');
        }

        //filtre sur le numéro de DA
        if (isset($criteria['numDa'])) {
            $qb->andWhere("da.numeroDemandeAppro LIKE :numDa")
                ->setParameter('numDa', '%' . $criteria['numDa'] . '%');
        }

        //filtre sur le demandeur
        if (isset($criteria['demandeur'])) {
            $qb->andWhere("da.demandeur LIKE :demandeur")
                ->setParameter('demandeur', '%' . $criteria['demandeur'] . '%');
        }

        //filtre sur le statut de DA
        if (isset($criteria['statutDA'])) {
            $qb->andWhere("da.statutDal =:statut")
                ->setParameter('statut', $criteria['statutDA']);
        }

        //Filtre sur l'id matériel
        if (isset($criteria['idMateriel'])) {
            $qb->andWhere("da.idMateriel = :idMat")
                ->setParameter('idMat', (int)$criteria['idMateriel']);
        }

        // Filtre sur la date de création
        if (isset($criteria['dateDebutCreation'])) {
            $qb->andWhere("da.dateCreation >= :dateDebut")
                ->setParameter('dateDebut', $criteria['dateDebutCreation']);
        }
        if (isset($criteria['dateFinCreation'])) {
            $qb->andWhere("da.dateCreation <= :dateFin")
                ->setParameter('dateFin', $criteria['dateFinCreation']);
        }

        //filtre sur la date  de fin souhaitée
        if (isset($criteria['dateDebutfinSouhaite'])) {
            $qb->andWhere("da.dateFinSouhaite >= :dateDebut")
                ->setParameter('dateDebut', $criteria['dateDebutfinSouhaite']);
        }
        if (isset($criteria['dateFinFinSouhaite'])) {
            $qb->andWhere("da.dateFinSouhaite <= :dateFin")
                ->setParameter('dateFin', $criteria['dateFinFinSouhaite']);
        }

        // Filtre sur l'agence Emetteur
        if (isset($criteria['agenceEmetteur'])) {
            $qb->andWhere("da.agenceEmetteur = :agenceEmetteur")
                ->setParameter('agenceEmetteur', $criteria['agenceEmetteur']->getId());
        }

        // Filtre sur le service Emetteur
        if (isset($criteria['serviceEmetteur'])) {
            $qb->andWhere("da.serviceEmetteur = :serviceEmetteur")
                ->setParameter('serviceEmetteur', $criteria['serviceEmetteur']->getId());
        }

        //Filtre sur l'agence destinataire
        if (isset($criteria['agenceDestinataire'])) {
            $qb->andWhere("da.agenceDestinataire = :agenceDestinataire")
                ->setParameter('agenceDestinataire', $criteria['agenceDestinataire']->getId());
        }

        // Filtre sur le service destinataire
        if (isset($criteria['serviceDestinataire'])) {
            $qb->andWhere("da.serviceDestinataire = :serviceDestinataire")
                ->setParameter('serviceDestinataire', $criteria['serviceDestinataire']->getId());
        }

        return $qb->getQuery()->getResult();
    }

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

    public function getDistinctColumn($column)
    {
        return $this->createQueryBuilder('da')
            ->select("DISTINCT da.$column")
            ->getQuery()
            ->getResult();
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
            ->where('da.statutDal = :statut')
            ->setParameter('statut', DemandeAppro::STATUT_VALIDE)
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
        return $this->createQueryBuilder('da')
            ->select('da.numeroDemandeDit')
            ->where('da.numeroDemandeAppro = :numDa')
            ->setParameter('numDa', $numDa)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function getNumDa($numDit)
    {
        try {
            $numDa =  $this->createQueryBuilder('da')
                ->select('da.numeroDemandeAppro')
                ->where('da.numeroDemandeDit = :numDit')
                ->setParameter('numDit', $numDit)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            $numDa = null; // ou une valeur par défaut
        }
        return $numDa;
    }
}
