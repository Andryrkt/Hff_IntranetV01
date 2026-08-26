<?php

namespace App\Model\ddd;

use App\Dto\ddd\DemandeDiagnosticPneuDto;
use App\Entity\ddd\DemandeDiagnosticPneu;
use App\Entity\ddd\DemandeDiagnosticPneuSearch;
use App\Entity\dit\DemandeIntervention;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
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
            ->select('d, di')
            ->from(DemandeDiagnosticPneu::class, 'd')
            ->leftJoin(
                DemandeIntervention::class,
                'di',
                'WITH',
                'd.numeroDit = di.numeroDemandeIntervention'
            );


        $this->applyFilters($qb, $search, $agenceId, $serviceId, $multisuccursale);

        // Pagination
        $qb->orderBy('d.dateCreation', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);


        $paginator = new Paginator($qb);
        $totalItems = count($paginator);

        $data = [];
        $dataDiag = null;

        foreach ($paginator as $item) {

            if ($item instanceof DemandeIntervention) {
                $data[] = DemandeDiagnosticPneuDto::fromEntities($dataDiag, $item);
                $dataDiag = null;
            }
            if ($item instanceof DemandeDiagnosticPneu) {
                if ($dataDiag != null) {
                    $data[] = DemandeDiagnosticPneuDto::fromEntities($dataDiag, null);
                }
                $dataDiag = $item;
            }
        }
        if ($dataDiag) {
            $data[] = DemandeDiagnosticPneuDto::fromEntities($dataDiag, null);
        }
        $statusCounts = $this->getStatusCounts($search, $agenceId, $serviceId, $multisuccursale);
        return [
            'data' => $data,
            'currentPage' => $page,
            'lastPage' => (int) ceil($totalItems / $limit),
            'totalItems' => $totalItems,
            "statusCounts" => $statusCounts
        ];
    }

    /**
     * Returns the count of DemandeDiagnosticPneu per status, filtered by the search criteria.
     *
     * @param DemandeDiagnosticPneuSearch $search
     * @return array<string, int>  e.g. ['a traiter atelier' => 5, 'traitee atelier' => 12]
     */
    public function getStatusCounts(
        DemandeDiagnosticPneuSearch $search,
        int $agenceId,
        int $serviceId,
        bool $multisuccursale
    ): array {
        $qb = $this->em->createQueryBuilder()
            ->select('d.statut, COUNT(d.id) as count')
            ->from(DemandeDiagnosticPneu::class, 'd')
            ->groupBy('d.statut');

        // Apply all filters (excluding pagination/ordering)
        $this->applyFilters($qb, $search, $agenceId, $serviceId, $multisuccursale);

        $results = $qb->getQuery()->getResult();

        $statusCounts = [];
        foreach ($results as $row) {
            $statusCounts[$row['statut']] = (int) $row['count'];
        }

        return $statusCounts;
    }

    /**
     * Private helper to apply all search filters to a QueryBuilder.
     *
     * @param QueryBuilder $qb
     * @param DemandeDiagnosticPneuSearch $search
     * @param int $agenceId
     * @param int $serviceId
     * @param bool $multisuccursale  // not used yet, but you can add logic
     */
    private function applyFilters(
        QueryBuilder $qb,
        DemandeDiagnosticPneuSearch $search,
        int $agenceId,
        int $serviceId,
        bool $multisuccursale
    ): void {
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
        if ($search->getLivraison()) {
            $qb->andWhere('d.livraison >= :livraison')
                ->setParameter('livraison', $search->getLivraison());
        }
        if ($search->getDateDepartChantierFin()) {
            $qb->andWhere('d.dateDepartChantier <= :dateDepartFin')
                ->setParameter('dateDepartFin', $search->getDateDepartChantierFin());
        }
        if ($search->getMotifs()) {
            $orConditions = [];
            foreach ($search->getMotifs() as $index => $motif) {
                $param = 'motif_' . $index;
                $escaped = str_replace('"', '\\"', $motif);
                $orConditions[] = "d.motifs LIKE :$param";
                $qb->setParameter($param, '%"' . $escaped . '"%');
            }
            if ($orConditions) {
                $qb->andWhere(implode(' OR ', $orConditions));
            }
        }
        if ($search->getNumeroDit()) {
            $qb->andWhere('d.numeroDit LIKE :numeroDit')
                ->setParameter('numeroDit', '%' . $search->getNumeroDit() . '%');
        }
        if ($search->getNumeroOr()) {
            $qb->andWhere('d.numeroOr LIKE :numeroOr')
                ->setParameter('numeroOr', '%' . $search->getNumeroOr() . '%');
        }

        // --- If $multisuccursale is false, add a filter on agence/service? ---
        // Example (adjust according to your entity relations):
        // if (!$multisuccursale) {
        //     $qb->andWhere('d.agence = :agenceId')
        //        ->setParameter('agenceId', $agenceId);
        // }
    }
}
