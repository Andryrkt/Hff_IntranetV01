<?php

namespace App\Repository\admin;

use App\Entity\admin\AgenceService;
use Doctrine\ORM\EntityRepository;

class AgenceServiceRepository extends EntityRepository
{
    public function findOneByCodeAgenceAndCodeService(
        string $codeAgence,
        string $codeService
    ): ?AgenceService {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.agence', 'a')
            ->innerJoin('t.service', 's')
            ->where('a.codeAgence = :codeAgence')
            ->andWhere('s.codeService = :codeService')
            ->setParameter('codeAgence', $codeAgence)
            ->setParameter('codeService', $codeService)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
