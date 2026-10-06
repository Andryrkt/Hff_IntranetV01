<?php

namespace App\Repository\dit;

use App\Entity\dit\DitSearch;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;


class DitSearchRepository extends EntityRepository
{
    public function findSectionSupport1()
    {
        $result = $this->createQueryBuilder('d')
            ->select('DISTINCT d.sectionSupport1')
            ->where('d.sectionAffectee IS NOT NULL')
            ->andWhere('d.sectionAffectee != :sectionAffectee')
            ->setParameter('sectionAffectee', ' ')
            ->andWhere('d.sectionAffectee != :sectionAffecte')
            ->setParameter('sectionAffecte', 'Autres')
            ->getQuery()
            ->getScalarResult();
        return array_column($result, 'sectionSupport1');
    }

    public function findSectionSupport2()
    {
        $result = $this->createQueryBuilder('d')
            ->select('DISTINCT d.sectionSupport2')
            ->where('d.sectionAffectee IS NOT NULL')
            ->andWhere('d.sectionAffectee != :sectionAffectee')
            ->setParameter('sectionAffectee', ' ')
            ->andWhere('d.sectionAffectee != :sectionAffecte')
            ->setParameter('sectionAffecte', 'Autres')
            ->getQuery()
            ->getScalarResult();
        return array_column($result, 'sectionSupport2');
    }

    public function findSectionSupport3()
    {
        $result = $this->createQueryBuilder('d')
            ->select('DISTINCT d.sectionSupport3')
            ->where('d.sectionAffectee IS NOT NULL')
            ->andWhere('d.sectionAffectee != :sectionAffectee')
            ->setParameter('sectionAffectee', ' ')
            ->andWhere('d.sectionAffectee != :sectionAffecte')
            ->setParameter('sectionAffecte', 'Autres')
            ->getQuery()
            ->getScalarResult();
        return array_column($result, 'sectionSupport3');
    }

    public function findSectionAffectee()
    {
        $result = $this->createQueryBuilder('d')
            ->select('DISTINCT d.sectionAffectee')
            ->where('d.sectionAffectee IS NOT NULL')
            ->andWhere('d.sectionAffectee != :sectionAffectee')
            ->setParameter('sectionAffectee', ' ')
            ->andWhere('d.sectionAffectee != :sectionAffecte')
            ->setParameter('sectionAffecte', 'Autres')
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
