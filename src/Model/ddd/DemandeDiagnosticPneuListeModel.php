<?php

namespace App\Model\ddd;

use App\Entity\ddd\DemandeDiagnosticPneu;
use App\Entity\ddd\DemandeDiagnosticPneuSearch;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class DemandeDiagnosticPneuListeModel
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getPaginatedList(DemandeDiagnosticPneuSearch $search, int $page, int $limit, int $agenceId, int $serviceId, bool $multisuccursale): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('d')
            ->from(DemandeDiagnosticPneu::class, 'd');

        // Application des critères de recherche
        if ($search->getNumeroDemande()) {
            $qb->andWhere('d.numeroDemande LIKE :numeroDemande')
                ->setParameter('numeroDemande', '%' . $search->getNumeroDemande() . '%');
        }
        if ($search->getDemandeur()) {
            $qb->andWhere('d.demandeur LIKE :demandeur')
                ->setParameter('demandeur', '%' . $search->getDemandeur() . '%');
        }
        if ($search->getIdChantier()) {
            $qb->andWhere('d.chantier = :chantierId')
                ->setParameter('chantierId', $search->getIdChantier());
        }
        if ($search->getStatut()) {
            $qb->andWhere('d.statut = :statut')
                ->setParameter('statut', $search->getStatut());
        }
        if ($search->getDateCreationDebut()) {
            $qb->andWhere('d.dateCreation >= :debut')
                ->setParameter('debut', $search->getDateCreationDebut());
        }
        if ($search->getDateCreationFin()) {
            $qb->andWhere('d.dateCreation <= :fin')
                ->setParameter('fin', $search->getDateCreationFin());
        }
        if ($search->getNumeroParcMateriel()) {
            $qb->andWhere('d.numeroParcMateriel LIKE :numParc')
                ->setParameter('numParc', '%' . $search->getNumeroParcMateriel() . '%');
        }
        if ($search->getDateDepartChantierDebut()) {
            $qb->andWhere('d.dateDepartChantier >= :dateDepartDebut')
                ->setParameter('dateDepartDebut', $search->getDateDepartChantierDebut());
        }

        if ($search->getDateDepartChantierFin()) {
            $qb->andWhere('d.dateDepartChantier <= :dateDepartFin')
                ->setParameter('dateDepartFin', $search->getDateDepartChantierFin());
        }
        if ($search->getMotifs()) {
            foreach ($search->getMotifs() as $index => $motif) {
                $param = 'motif_' . $index;

                $qb->andWhere(
                    "d.motifs LIKE :$param"
                );

                $qb->setParameter(
                    $param,
                    '%' . $motif . '%'
                );
            }
        }
        // Gestion des permissions (ex: restreindre par agence/service si pas multisuccursale)
        if (!$multisuccursale) {
        }

        // Pagination
        $qb->orderBy('d.dateCreation', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginator = new Paginator($qb);
        $totalItems = count($paginator);

        return [
            'data' => iterator_to_array($paginator),
            'currentPage' => $page,
            'lastPage' => (int) ceil($totalItems / $limit),
            'totalItems' => $totalItems,
        ];
    }
}
