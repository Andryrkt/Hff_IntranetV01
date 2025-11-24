<?php

namespace App\Repository\da;

use App\Controller\Controller;
use App\Entity\admin\utilisateur\Role;
use App\Entity\admin\utilisateur\User;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use Doctrine\ORM\EntityRepository;

class DemandeApproRepository extends EntityRepository
{
    public function findDaData(User $user, array $criteria = [], int $idAgenceUser, bool $estAppro, bool $estAtelier, bool $estAdmin)
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

        if (empty(array_filter($criteria, fn($v) => !is_null($v)))) {
            // Par défaut, on n'affiche pas les demandes terminées
            $qb->andWhere("da.statutDal != :statut")
                ->setParameter('statut', DemandeAppro::STATUT_TERMINER);
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

        $this->FiltredSelonDate($qb, $criteria);

        $this->FiltredSelonAgenceService($qb, $criteria, $user, $idAgenceUser, $estAppro, $estAtelier, $estAdmin);

        return $qb->getQuery()->getResult();
    }

    private function FiltredSelonAgenceService($qb, array $criteria, User $user, int $idAgenceUser, bool $estAppro, bool $estAtelier, bool $estAdmin)
    {
        if (!$estAtelier && !$estAppro && !$estAdmin) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        'da.agenceDebiteur IN (:agenceAutoriserIds)',
                        'da.agenceEmetteur = :codeAgence'
                    )
                )
                ->setParameter('agenceAutoriserIds', $user->getAgenceAutoriserIds(), \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
                ->setParameter('codeAgence', $idAgenceUser)
                ->andWhere(
                    $qb->expr()->orX(
                        'da.serviceDebiteur IN (:serviceAutoriserIds)',
                        'da.serviceEmetteur IN (:serviceAutoriserIds)'
                    )
                )
                ->setParameter('serviceAutoriserIds', $user->getServiceAutoriserIds(), \Doctrine\DBAL\Connection::PARAM_INT_ARRAY);
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
        if (isset($criteria['agenceDebiteur'])) {
            $qb->andWhere("da.agenceDebiteur = :agenceDebiteur")
                ->setParameter('agenceDebiteur', $criteria['agenceDebiteur']->getId());
        }

        // Filtre sur le service destinataire
        if (isset($criteria['serviceDebiteur'])) {
            $qb->andWhere("da.serviceDebiteur = :serviceDebiteur")
                ->setParameter('serviceDebiteur', $criteria['serviceDebiteur']->getId());
        }
    }

    private function FiltredSelonDate($qb, array $criteria)
    {
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
        // Sous-requête pour trouver le numéro de version max des DAL pour ce numéro de DA
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
            ->where('da.numeroDemandeAppro = :numero')
            ->andWhere("dal.numeroVersion = ($subQuery)")
            ->andWhere("dal.deleted = 0")
            ->setParameter('numero', $numeroDemandeAppro)
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
        return $this->createQueryBuilder('da')
            ->select('da.numeroDemandeDit')
            ->where('da.numeroDemandeAppro = :numDa')
            ->setParameter('numDa', $numDa)
            ->getQuery()
            ->getOneOrNullResult()
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
                ->getSingleColumnResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
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
