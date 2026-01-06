<?php

namespace App\Repository\da;

use Doctrine\ORM\EntityRepository;

class DaArticleReapproRepository extends EntityRepository
{
    public function getArticlesList(string $codeAgence, string $codeService)
    {
        try {
            $qb = $this->createQueryBuilder('d')
                ->select('d.artDesi')
                ->where('d.codeAgence = :codeAgence')
                ->andWhere('d.codeService = :codeService')
                ->setParameter('codeAgence', $codeAgence)
                ->setParameter('codeService', $codeService);

            return $qb->getQuery()->getSingleColumnResult();
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }
}
