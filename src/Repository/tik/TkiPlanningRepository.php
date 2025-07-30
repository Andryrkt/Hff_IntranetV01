<?php

namespace App\Repository\tik;

use Doctrine\ORM\EntityRepository;

class TkiPlanningRepository extends EntityRepository
{
    public function findByFilter(array $tab)
    {
        $queryBuilder = $this->createQueryBuilder('p');

        $this->innerJoinTable($queryBuilder, $tab);
        $this->conditionDemandeur($queryBuilder, $tab['demandeur']);
        $this->conditionIntervenant($queryBuilder, $tab['nomIntervenant']);

        return $queryBuilder
            ->getQuery()
            ->getResult()
        ;
    }

    private function innerJoinTable($queryBuilder, $tab)
    {
        if ($tab['demandeur'] !== '' || $tab['nomIntervenant'] !== null) {
            $queryBuilder
                ->innerJoin('p.demandeSupportInfo', 'd');
        }
    }

    private function conditionDemandeur($queryBuilder, $demandeur)
    {
        if ($demandeur !== '') {
            $queryBuilder
                ->andWhere('d.utilisateurDemandeur LIKE :demandeur')
                ->setParameter('demandeur', '%' . $demandeur . '%')
            ;
        }
    }

    private function conditionIntervenant($queryBuilder, $intervenantId)
    {
        if ($intervenantId !== null) {
            $queryBuilder
                ->innerJoin('d.intervenant', 'u')
                ->andWhere('u.id = :userId')
                ->setParameter('userId', $intervenantId)
            ;
        }
    }
}
