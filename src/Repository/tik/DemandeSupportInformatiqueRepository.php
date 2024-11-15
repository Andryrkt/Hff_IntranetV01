<?php

namespace App\Repository\tik;

use App\Entity\tik\TikSearch;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

class DemandeSupportInformatiqueRepository extends EntityRepository
{
    public function findPaginatedAndFiltered(int $page = 1, int $limit = 10, TikSearch $ditSearch = null)
    {
        $queryBuilder = $this->createQueryBuilder('tki')
            // ->leftJoin('tki.niveauUrgence', 'nu')
            ;

        
            $queryBuilder->orderBy('tki.dateCreation', 'DESC');
    
            $queryBuilder->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ;
    
            $paginator = new DoctrinePaginator($queryBuilder->getQuery());

            $totalItems = count($paginator);
            $lastPage = ceil($totalItems / $limit);
            // $sql = $queryBuilder->getQuery()->getSQL();
            // echo $sql;
    
            //return $queryBuilder->getQuery()->getResult();
            return [
                'data' => iterator_to_array($paginator->getIterator()), // Convertir en tableau si nécessaire
                'totalItems' => $totalItems,
                'currentPage' => $page,
                'lastPage' => $lastPage,
            ];
    }
}

?>