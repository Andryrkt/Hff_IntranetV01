<?php

namespace App\Repository;



use Doctrine\ORM\EntityRepository;


class DitRepository extends EntityRepository
{
    public function findPaginatedAndFiltered(int $page = 1, int $limit = 10, array $criteria = [])
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->leftJoin('d.typeDocument', 'td')
            ->leftJoin('d.idNiveauUrgence', 'nu')
            ->leftJoin('d.idStatutDemande', 's')
            ;

            $excludedStatuses = [9, 18, 22, 24, 26, 32, 33, 34, 35];
            $queryBuilder->andWhere($queryBuilder->expr()->notIn('s.id', ':excludedStatuses'))
                ->setParameter('excludedStatuses', $excludedStatuses);

            if (!empty($criteria['statut'])) {
                $queryBuilder->andWhere('s.description LIKE :statut')
                    ->setParameter('statut', '%' . $criteria['statut'] . '%');
            }

        if (!empty($criteria['typeDocument'])) {
            $queryBuilder->andWhere('td.description LIKE :typeDocument')
                ->setParameter('typeDocument', '%' . $criteria['typeDocument'] . '%');
        }

        if (!empty($criteria['niveauUrgence'])) {
            $queryBuilder->andWhere('nu.description LIKE :niveauUrgence')
                ->setParameter('niveauUrgence', '%' . $criteria['niveauUrgence'] . '%');
        }

        if (!empty($criteria['idMateriel'])) {
            $queryBuilder->andWhere('d.idMateriel = :idMateriel')
                ->setParameter('idMateriel',  $criteria['idMateriel'] );
        }

        if (!empty($criteria['internetExterne'])) {
            $queryBuilder->andWhere('d.internetExterne = :internetExterne')
                ->setParameter('internetExterne',  $criteria['internetExterne'] );
        }

        if (!empty($criteria['dateDebut'])) {
            $queryBuilder->andWhere('d.dateDemande >= :dateDebut')
                ->setParameter('dateDebut', $criteria['dateDebut']);
        }

        if (!empty($criteria['dateFin'])) {
            $queryBuilder->andWhere('d.dateDemande <= :dateFin')
                ->setParameter('dateFin', $criteria['dateFin']);
        }

        if (!empty($criteria['agServEmet'])) {
            $queryBuilder->andWhere('d.agenceServiceEmetteur = :agServEmet')
                ->setParameter('agServEmet',  $criteria['agServEmet'] );
        }

        if (!empty($criteria['agServDebit'])) {
            $queryBuilder->andWhere('d.agenceServiceDebiteur = :agServDebit')
                ->setParameter('agServDebit',  $criteria['agServDebit'] );
        }

        $queryBuilder->orderBy('d.dateDemande', 'DESC')
        ->addOrderBy('d.numeroDemandeIntervention', 'ASC');
        $queryBuilder->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ;

        
            // $sql = $queryBuilder->getQuery()->getSQL();
            // echo $sql;

        return $queryBuilder->getQuery()->getResult();
    }

    public function countFiltered(array $criteria = [])
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->leftJoin('d.typeDocument', 'td')
            ->leftJoin('d.idNiveauUrgence', 'nu')
            ->leftJoin('d.idStatutDemande', 's');

            $excludedStatuses = [9, 18, 22, 24, 26, 32, 33, 34, 35];
            $queryBuilder->andWhere($queryBuilder->expr()->notIn('s.id', ':excludedStatuses'))
                ->setParameter('excludedStatuses', $excludedStatuses);

            if (!empty($criteria['statut'])) {
                $queryBuilder->andWhere('s.description LIKE :statut')
                    ->setParameter('statut', '%' . $criteria['statut'] . '%');
            }

            if (!empty($criteria['typeDocument'])) {
                $queryBuilder->andWhere('td.description LIKE :typeDocument')
                    ->setParameter('typeDocument', '%' . $criteria['typeDocument'] . '%');
            }
    
            if (!empty($criteria['niveauUrgence'])) {
                $queryBuilder->andWhere('nu.description LIKE :niveauUrgence')
                    ->setParameter('niveauUrgence', '%' . $criteria['niveauUrgence'] . '%');
            }

            if (!empty($criteria['idMateriel'])) {
                $queryBuilder->andWhere('d.idMateriel = :idMateriel')
                    ->setParameter('idMateriel',  $criteria['idMateriel'] );
            }

            if (!empty($criteria['internetExterne'])) {
                $queryBuilder->andWhere('d.internetExterne = :internetExterne')
                    ->setParameter('internetExterne',  $criteria['internetExterne'] );
            }

        if (!empty($criteria['dateDebut'])) {
            $queryBuilder->andWhere('d.dateDemande >= :dateDebut')
                ->setParameter('dateDebut', $criteria['dateDebut']);
        }

        if (!empty($criteria['dateFin'])) {
            $queryBuilder->andWhere('d.dateDemande <= :dateFin')
                ->setParameter('dateFin', $criteria['dateFin']);
        }

        if (!empty($criteria['agServEmet'])) {
            $queryBuilder->andWhere('d.agenceServiceEmetteur = :agServEmet')
                ->setParameter('agServEmet',  $criteria['agServEmet'] );
        }
        
        if (!empty($criteria['agServDebit'])) {
            $queryBuilder->andWhere('d.agenceServiceDebiteur = :agServDebit')
                ->setParameter('agServDebit',  $criteria['agServDebit'] );
        }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function findAndFilteredExcel( array $criteria = [])
    {
        $queryBuilder = $this->createQueryBuilder('d')
        ->leftJoin('d.typeDocument', 'td')
        ->leftJoin('d.idNiveauUrgence', 'nu')
        ->leftJoin('d.idStatutDemande', 's')
            ;

            $excludedStatuses = [9, 18, 22, 24, 26, 32, 33, 34, 35];
            $queryBuilder->andWhere($queryBuilder->expr()->notIn('s.id', ':excludedStatuses'))
                ->setParameter('excludedStatuses', $excludedStatuses);

         
            if (!empty($criteria['statut'])) {
                $queryBuilder->andWhere('s.description LIKE :statut')
                    ->setParameter('statut', '%' . $criteria['statut'] . '%');
            }

            if (!empty($criteria['typeDocument'])) {
                $queryBuilder->andWhere('td.description LIKE :typeDocument')
                    ->setParameter('typeDocument', '%' . $criteria['typeDocument'] . '%');
            }
    
            if (!empty($criteria['niveauUrgence'])) {
                $queryBuilder->andWhere('nu.description LIKE :niveauUrgence')
                    ->setParameter('niveauUrgence', '%' . $criteria['niveauUrgence'] . '%');
            }

            if (!empty($criteria['idMateriel'])) {
                $queryBuilder->andWhere('d.idMateriel = :idMateriel')
                    ->setParameter('idMateriel',  $criteria['idMateriel'] );
            }

            if (!empty($criteria['internetExterne'])) {
                $queryBuilder->andWhere('d.internetExterne = :internetExterne')
                    ->setParameter('internetExterne',  $criteria['internetExterne'] );
            }

        if (!empty($criteria['dateDebut'])) {
            $queryBuilder->andWhere('d.dateDemande >= :dateDebut')
                ->setParameter('dateDebut', $criteria['dateDebut']);
        }

        if (!empty($criteria['dateFin'])) {
            $queryBuilder->andWhere('d.dateDemande <= :dateFin')
                ->setParameter('dateFin', $criteria['dateFin']);
        }

        if (!empty($criteria['agServEmet'])) {
            $queryBuilder->andWhere('d.agenceServiceEmetteur = :agServEmet')
                ->setParameter('agServEmet',  $criteria['agServEmet'] );
        }

        if (!empty($criteria['agServDebit'])) {
            $queryBuilder->andWhere('d.agenceServiceDebiteur = :agServDebit')
                ->setParameter('agServDebit',  $criteria['agServDebit'] );
        }

        $queryBuilder->orderBy('d.dateDemande', 'DESC')
        ->addOrderBy('d.numeroDemandeIntervention', 'ASC');
            

        return $queryBuilder->getQuery()->getResult();
    }

    public function findPaginatedAndFilteredListAnnuler(int $page = 1, int $limit = 10, array $criteria = [])
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->leftJoin('b.typeMouvement', 'tm')
            ->leftJoin('b.statutDemande', 's')
            ;

            $includedStatuses = [9, 18, 22, 24, 26, 32, 33, 34, 35];
            $queryBuilder->andWhere($queryBuilder->expr()->In('s.id', ':includedStatuses'))
                ->setParameter('includedStatuses', $includedStatuses);

            
                if (!empty($criteria['statut'])) {
                    $queryBuilder->andWhere('s.description LIKE :statut')
                        ->setParameter('statut', '%' . $criteria['statut'] . '%');
                }
    
                if (!empty($criteria['typeDocument'])) {
                    $queryBuilder->andWhere('td.description LIKE :typeDocument')
                        ->setParameter('typeDocument', '%' . $criteria['typeDocument'] . '%');
                }
        
                if (!empty($criteria['niveauUrgence'])) {
                    $queryBuilder->andWhere('nu.description LIKE :niveauUrgence')
                        ->setParameter('niveauUrgence', '%' . $criteria['niveauUrgence'] . '%');
                }
    
                if (!empty($criteria['idMateriel'])) {
                    $queryBuilder->andWhere('d.idMateriel = :idMateriel')
                        ->setParameter('idMateriel',  $criteria['idMateriel'] );
                }
    
                if (!empty($criteria['internetExterne'])) {
                    $queryBuilder->andWhere('d.internetExterne = :internetExterne')
                        ->setParameter('internetExterne',  $criteria['internetExterne'] );
                }

        if (!empty($criteria['dateDebut'])) {
            $queryBuilder->andWhere('b.dateDemande >= :dateDebut')
                ->setParameter('dateDebut', $criteria['dateDebut']);
        }

        if (!empty($criteria['dateFin'])) {
            $queryBuilder->andWhere('b.dateDemande <= :dateFin')
                ->setParameter('dateFin', $criteria['dateFin']);
        }

        if (!empty($criteria['agServEmet'])) {
            $queryBuilder->andWhere('d.agenceServiceEmetteur = :agServEmet')
                ->setParameter('agServEmet',  $criteria['agServEmet'] );
        }

        if (!empty($criteria['agServDebit'])) {
            $queryBuilder->andWhere('d.agenceServiceDebiteur = :agServDebit')
                ->setParameter('agServDebit',  $criteria['agServDebit'] );
        }

        $queryBuilder->orderBy('b.numBadm', 'DESC');
        $queryBuilder->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ;

        
            // $sql = $queryBuilder->getQuery()->getSQL();
            // echo $sql;

        return $queryBuilder->getQuery()->getResult();
    }

    public function countFilteredListAnnuller(array $criteria = [])
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->leftJoin('b.typeMouvement', 'tm')
            ->leftJoin('b.statutDemande', 's');

            $includedStatuses = [9, 18, 22, 24, 26, 32, 33, 34, 35];
            $queryBuilder->andWhere($queryBuilder->expr()->In('s.id', ':includedStatuses'))
                ->setParameter('includedStatuses', $includedStatuses);

           
                if (!empty($criteria['statut'])) {
                    $queryBuilder->andWhere('s.description LIKE :statut')
                        ->setParameter('statut', '%' . $criteria['statut'] . '%');
                }
    
                if (!empty($criteria['typeDocument'])) {
                    $queryBuilder->andWhere('td.description LIKE :typeDocument')
                        ->setParameter('typeDocument', '%' . $criteria['typeDocument'] . '%');
                }
        
                if (!empty($criteria['niveauUrgence'])) {
                    $queryBuilder->andWhere('nu.description LIKE :niveauUrgence')
                        ->setParameter('niveauUrgence', '%' . $criteria['niveauUrgence'] . '%');
                }
    
                if (!empty($criteria['idMateriel'])) {
                    $queryBuilder->andWhere('d.idMateriel = :idMateriel')
                        ->setParameter('idMateriel',  $criteria['idMateriel'] );
                }
    
                if (!empty($criteria['internetExterne'])) {
                    $queryBuilder->andWhere('d.internetExterne = :internetExterne')
                        ->setParameter('internetExterne',  $criteria['internetExterne'] );
                }

        if (!empty($criteria['dateDebut'])) {
            $queryBuilder->andWhere('b.dateDemande >= :dateDebut')
                ->setParameter('dateDebut', $criteria['dateDebut']);
        }

        if (!empty($criteria['dateFin'])) {
            $queryBuilder->andWhere('b.dateDemande <= :dateFin')
                ->setParameter('dateFin', $criteria['dateFin']);
        }

        if (!empty($criteria['agServEmet'])) {
            $queryBuilder->andWhere('d.agenceServiceEmetteur = :agServEmet')
                ->setParameter('agServEmet',  $criteria['agServEmet'] );
        }

        if (!empty($criteria['agServDebit'])) {
            $queryBuilder->andWhere('d.agenceServiceDebiteur = :agServDebit')
                ->setParameter('agServDebit',  $criteria['agServDebit'] );
        }
        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}