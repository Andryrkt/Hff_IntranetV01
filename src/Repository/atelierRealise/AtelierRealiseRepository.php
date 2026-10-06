<?php

namespace App\Repository\atelierRealise;

use Doctrine\ORM\EntityRepository;
use App\Entity\atelierRealise\AtelierRealise;

class AtelierRealiseRepository extends EntityRepository
{
    public function findWithAgenceAndServiceByCode(string $code): ?AtelierRealise
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.agence', 'agence')
            ->addSelect('agence')
            ->leftJoin('a.service', 'service')
            ->addSelect('service')
            ->where('a.codeAtelier = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
