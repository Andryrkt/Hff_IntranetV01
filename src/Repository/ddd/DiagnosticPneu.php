<?php

namespace App\Repository\ddd;

use App\Entity\ddd\DemandeDiagnosticPneu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DemandeDiagnosticPneu>
 *
 * @method DemandeDiagnosticPneu|null find($id, $lockMode = null, $lockVersion = null)
 * @method DemandeDiagnosticPneu|null findOneBy(array $criteria, array $orderBy = null)
 * @method DemandeDiagnosticPneu[]    findAll()
 * @method DemandeDiagnosticPneu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DemandeDiagnosticPneuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemandeDiagnosticPneu::class);
    }

    public function save(DemandeDiagnosticPneu $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DemandeDiagnosticPneu $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByNumeroDemande(string $numeroDemande): ?DemandeDiagnosticPneu
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.numeroDemande = :numero')
            ->setParameter('numero', $numeroDemande)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return DemandeDiagnosticPneu[]
     */
    public function findByStatut(string $statut): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('d.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return DemandeDiagnosticPneu[]
     */
    public function findRecent(int $limit = 20): array
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.dateCreation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
