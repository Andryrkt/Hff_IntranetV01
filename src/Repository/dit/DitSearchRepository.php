<?php

namespace App\Repository\dit;

use App\Entity\dit\DitSearch;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;


class DitRepository extends EntityRepository
{
    public function findSectionSupport1()
    {
        $result = $this->createQueryBuilder('d')
            ->select('DISTINCT d.sectionSupport1')
            ->getQuery()
            ->getScalarResult();
        return array_column($result, 'sectionSupport1');
    }

    public function findSectionSupport2()
    {
        $result = $this->createQueryBuilder('d')
            ->select('DISTINCT d.sectionSupport2')
            ->getQuery()
            ->getScalarResult();
        return array_column($result, 'sectionSupport2');
    }

    public function findSectionSupport3()
    {
        $result = $this->createQueryBuilder('d')
            ->select('DISTINCT d.sectionSupport3')
            ->getQuery()
            ->getScalarResult();
        return array_column($result, 'sectionSupport3');
    }

    public function findSectionAffectee()
    {
        $result = $this->createQueryBuilder('d')
            ->select('DISTINCT d.sectionAffectee')
            ->getQuery()
            ->getScalarResult();
        return array_column($result, 'sectionAffectee');
    }

    public function findStatutOr()
    {
        $result = $this->createQueryBuilder('d')
            ->select('DISTINCT d.statutOr')
            ->where('d.statutOr IS NOT NULL')
            ->getQuery()
            ->getScalarResult();
        return array_column($result, 'statutOr');
    }
}
