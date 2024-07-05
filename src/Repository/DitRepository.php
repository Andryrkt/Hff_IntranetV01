<?php

namespace App\Repository;

use App\Entity\DitSearch;
use Doctrine\ORM\EntityRepository;


class DitRepository extends EntityRepository
{
    public function findPaginatedAndFiltered(int $page = 1, int $limit = 10, DitSearch $ditSearch, array $options)
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->leftJoin('d.typeDocument', 'td')
            ->leftJoin('d.idNiveauUrgence', 'nu')
            ->leftJoin('d.idStatutDemande', 's')
            ;

            $excludedStatuses = [9, 18, 22, 24, 26, 32, 33, 34, 35];
            $queryBuilder->andWhere($queryBuilder->expr()->notIn('s.id', ':excludedStatuses'))
                ->setParameter('excludedStatuses', $excludedStatuses);

            if (!empty($ditSearch->getStatut())) {
                $queryBuilder->andWhere('s.description LIKE :statut')
                    ->setParameter('statut', '%' . $ditSearch->getStatut() . '%');
            }

        if (!empty($ditSearch->getTypeDocument())) {
            $queryBuilder->andWhere('td.description LIKE :typeDocument')
                ->setParameter('typeDocument', '%' . $ditSearch->getTypeDocument() . '%');
        }

        if (!empty($ditSearch->getNiveauUrgence())) {
            $queryBuilder->andWhere('nu.description LIKE :niveauUrgence')
                ->setParameter('niveauUrgence', '%' . $ditSearch->getNiveauUrgence() . '%');
        }

        if (!empty($ditSearch->getIdMateriel())) {
            $queryBuilder->andWhere('d.idMateriel = :idMateriel')
                ->setParameter('idMateriel',  $ditSearch->getIdMateriel() );
        }

        if (!empty($ditSearch->getInternetExterne())) {
            $queryBuilder->andWhere('d.internetExterne = :internetExterne')
                ->setParameter('internetExterne',  $ditSearch->getInternetExterne() );
        }

        if (!empty($ditSearch->getDateDebut())) {
            $queryBuilder->andWhere('d.dateDemande >= :dateDebut')
                ->setParameter('dateDebut', $ditSearch->getDateDebut());
        }

        if (!empty($ditSearch->getDateFin())) {
            $queryBuilder->andWhere('d.dateDemande <= :dateFin')
                ->setParameter('dateFin', $ditSearch->getDateFin());
        }

        if ($options['boolean']) {
            if (!empty($ditSearch->getAgenceEmetteur())) {
                $queryBuilder->andWhere('d.agenceServiceEmetteur = :agServEmet')
                ->setParameter('agServEmet',  $ditSearch->getAgenceEmetteur()->getCodeAgence() . '-' . $ditSearch->getServiceEmetteur()->getCodeService() );
            }
        } else {
                $queryBuilder->andWhere('d.agenceServiceEmetteur = :agServEmet')
                ->setParameter('agServEmet',  $options['codeAgence'] . '-' . $options['codeService'] );
        }

        if (!empty($ditSearch->getAgenceDebiteur())) {
            $queryBuilder->andWhere('d.agenceServiceDebiteur = :agServDebit')
                ->setParameter('agServDebit',  $ditSearch->getAgenceDebiteur()->getCodeAgence() . '-' . $ditSearch->getServiceDebiteur()->getCodeService() );
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

    public function countFiltered(DitSearch $ditSearch)
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->leftJoin('d.typeDocument', 'td')
            ->leftJoin('d.idNiveauUrgence', 'nu')
            ->leftJoin('d.idStatutDemande', 's');

            $excludedStatuses = [9, 18, 22, 24, 26, 32, 33, 34, 35];
            $queryBuilder->andWhere($queryBuilder->expr()->notIn('s.id', ':excludedStatuses'))
                ->setParameter('excludedStatuses', $excludedStatuses);

                if (!empty($ditSearch->getStatut())) {
                    $queryBuilder->andWhere('s.description LIKE :statut')
                        ->setParameter('statut', '%' . $ditSearch->getStatut() . '%');
                }
    
            if (!empty($ditSearch->getTypeDocument())) {
                $queryBuilder->andWhere('td.description LIKE :typeDocument')
                    ->setParameter('typeDocument', '%' . $ditSearch->getTypeDocument() . '%');
            }
    
            if (!empty($ditSearch->getNiveauUrgence())) {
                $queryBuilder->andWhere('nu.description LIKE :niveauUrgence')
                    ->setParameter('niveauUrgence', '%' . $ditSearch->getNiveauUrgence() . '%');
            }
    
            if (!empty($ditSearch->getIdMateriel())) {
                $queryBuilder->andWhere('d.idMateriel = :idMateriel')
                    ->setParameter('idMateriel',  $ditSearch->getIdMateriel() );
            }
    
            if (!empty($ditSearch->getInternetExterne())) {
                $queryBuilder->andWhere('d.internetExterne = :internetExterne')
                    ->setParameter('internetExterne',  $ditSearch->getInternetExterne() );
            }
    
            if (!empty($ditSearch->getDateDebut())) {
                $queryBuilder->andWhere('d.dateDemande >= :dateDebut')
                    ->setParameter('dateDebut', $ditSearch->getDateDebut());
            }
    
            if (!empty($ditSearch->getDateFin())) {
                $queryBuilder->andWhere('d.dateDemande <= :dateFin')
                    ->setParameter('dateFin', $ditSearch->getDateFin());
            }
    
            if (!empty($ditSearch->getAgenceEmetteur())) {
                $queryBuilder->andWhere('d.agenceServiceEmetteur = :agServEmet')
                    ->setParameter('agServEmet',  $ditSearch->getAgenceEmetteur()->getCodeAgence() . '-' . $ditSearch->getServiceEmetteur()->getCodeService() );
            }
    
            if (!empty($ditSearch->getAgenceDebiteur())) {
                $queryBuilder->andWhere('d.agenceServiceDebiteur = :agServDebit')
                    ->setParameter('agServDebit',  $ditSearch->getAgenceDebiteur()->getCodeAgence() . '-' . $ditSearch->getServiceDebiteur()->getCodeService() );
            }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function findAndFilteredExcel( DitSearch $ditSearch)
    {
        $queryBuilder = $this->createQueryBuilder('d')
        ->leftJoin('d.typeDocument', 'td')
        ->leftJoin('d.idNiveauUrgence', 'nu')
        ->leftJoin('d.idStatutDemande', 's')
            ;

            $excludedStatuses = [9, 18, 22, 24, 26, 32, 33, 34, 35];
            $queryBuilder->andWhere($queryBuilder->expr()->notIn('s.id', ':excludedStatuses'))
                ->setParameter('excludedStatuses', $excludedStatuses);

         
                if (!empty($ditSearch->getStatut())) {
                    $queryBuilder->andWhere('s.description LIKE :statut')
                        ->setParameter('statut', '%' . $ditSearch->getStatut() . '%');
                }
    
            if (!empty($ditSearch->getTypeDocument())) {
                $queryBuilder->andWhere('td.description LIKE :typeDocument')
                    ->setParameter('typeDocument', '%' . $ditSearch->getTypeDocument() . '%');
            }
    
            if (!empty($ditSearch->getNiveauUrgence())) {
                $queryBuilder->andWhere('nu.description LIKE :niveauUrgence')
                    ->setParameter('niveauUrgence', '%' . $ditSearch->getNiveauUrgence() . '%');
            }
    
            if (!empty($ditSearch->getIdMateriel())) {
                $queryBuilder->andWhere('d.idMateriel = :idMateriel')
                    ->setParameter('idMateriel',  $ditSearch->getIdMateriel() );
            }
    
            if (!empty($ditSearch->getInternetExterne())) {
                $queryBuilder->andWhere('d.internetExterne = :internetExterne')
                    ->setParameter('internetExterne',  $ditSearch->getInternetExterne() );
            }
    
            if (!empty($ditSearch->getDateDebut())) {
                $queryBuilder->andWhere('d.dateDemande >= :dateDebut')
                    ->setParameter('dateDebut', $ditSearch->getDateDebut());
            }
    
            if (!empty($ditSearch->getDateFin())) {
                $queryBuilder->andWhere('d.dateDemande <= :dateFin')
                    ->setParameter('dateFin', $ditSearch->getDateFin());
            }
    
            if (!empty($ditSearch->getAgenceEmetteur())) {
                $queryBuilder->andWhere('d.agenceServiceEmetteur = :agServEmet')
                    ->setParameter('agServEmet',  $ditSearch->getAgenceEmetteur()->getCodeAgence() . '-' . $ditSearch->getServiceEmetteur()->getCodeService() );
            }
    
            if (!empty($ditSearch->getAgenceDebiteur())) {
                $queryBuilder->andWhere('d.agenceServiceDebiteur = :agServDebit')
                    ->setParameter('agServDebit',  $ditSearch->getAgenceDebiteur()->getCodeAgence() . '-' . $ditSearch->getServiceDebiteur()->getCodeService() );
            }

        $queryBuilder->orderBy('d.dateDemande', 'DESC')
        ->addOrderBy('d.numeroDemandeIntervention', 'ASC');
            

        return $queryBuilder->getQuery()->getResult();
    }

    public function findPaginatedAndFilteredListAnnuler(int $page = 1, int $limit = 10, DitSearch $ditSearch)
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->leftJoin('b.typeMouvement', 'tm')
            ->leftJoin('b.statutDemande', 's')
            ;

            $includedStatuses = [9, 18, 22, 24, 26, 32, 33, 34, 35];
            $queryBuilder->andWhere($queryBuilder->expr()->In('s.id', ':includedStatuses'))
                ->setParameter('includedStatuses', $includedStatuses);

            
                if (!empty($ditSearch->getStatut())) {
                    $queryBuilder->andWhere('s.description LIKE :statut')
                        ->setParameter('statut', '%' . $ditSearch->getStatut() . '%');
                }
    
            if (!empty($ditSearch->getTypeDocument())) {
                $queryBuilder->andWhere('td.description LIKE :typeDocument')
                    ->setParameter('typeDocument', '%' . $ditSearch->getTypeDocument() . '%');
            }
    
            if (!empty($ditSearch->getNiveauUrgence())) {
                $queryBuilder->andWhere('nu.description LIKE :niveauUrgence')
                    ->setParameter('niveauUrgence', '%' . $ditSearch->getNiveauUrgence() . '%');
            }
    
            if (!empty($ditSearch->getIdMateriel())) {
                $queryBuilder->andWhere('d.idMateriel = :idMateriel')
                    ->setParameter('idMateriel',  $ditSearch->getIdMateriel() );
            }
    
            if (!empty($ditSearch->getInternetExterne())) {
                $queryBuilder->andWhere('d.internetExterne = :internetExterne')
                    ->setParameter('internetExterne',  $ditSearch->getInternetExterne() );
            }
    
            if (!empty($ditSearch->getDateDebut())) {
                $queryBuilder->andWhere('d.dateDemande >= :dateDebut')
                    ->setParameter('dateDebut', $ditSearch->getDateDebut());
            }
    
            if (!empty($ditSearch->getDateFin())) {
                $queryBuilder->andWhere('d.dateDemande <= :dateFin')
                    ->setParameter('dateFin', $ditSearch->getDateFin());
            }
    
            if (!empty($ditSearch->getAgenceEmetteur())) {
                $queryBuilder->andWhere('d.agenceServiceEmetteur = :agServEmet')
                    ->setParameter('agServEmet',  $ditSearch->getAgenceEmetteur()->getCodeAgence() . '-' . $ditSearch->getServiceEmetteur()->getCodeService() );
            }
    
            if (!empty($ditSearch->getAgenceDebiteur())) {
                $queryBuilder->andWhere('d.agenceServiceDebiteur = :agServDebit')
                    ->setParameter('agServDebit',  $ditSearch->getAgenceDebiteur()->getCodeAgence() . '-' . $ditSearch->getServiceDebiteur()->getCodeService() );
            }

        $queryBuilder->orderBy('b.numBadm', 'DESC');
        $queryBuilder->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ;

        
            // $sql = $queryBuilder->getQuery()->getSQL();
            // echo $sql;

        return $queryBuilder->getQuery()->getResult();
    }

    public function countFilteredListAnnuller(DitSearch $ditSearch)
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->leftJoin('b.typeMouvement', 'tm')
            ->leftJoin('b.statutDemande', 's');

            $includedStatuses = [9, 18, 22, 24, 26, 32, 33, 34, 35];
            $queryBuilder->andWhere($queryBuilder->expr()->In('s.id', ':includedStatuses'))
                ->setParameter('includedStatuses', $includedStatuses);

           
                if (!empty($ditSearch->getStatut())) {
                    $queryBuilder->andWhere('s.description LIKE :statut')
                        ->setParameter('statut', '%' . $ditSearch->getStatut() . '%');
                }
    
            if (!empty($ditSearch->getTypeDocument())) {
                $queryBuilder->andWhere('td.description LIKE :typeDocument')
                    ->setParameter('typeDocument', '%' . $ditSearch->getTypeDocument() . '%');
            }
    
            if (!empty($ditSearch->getNiveauUrgence())) {
                $queryBuilder->andWhere('nu.description LIKE :niveauUrgence')
                    ->setParameter('niveauUrgence', '%' . $ditSearch->getNiveauUrgence() . '%');
            }
    
            if (!empty($ditSearch->getIdMateriel())) {
                $queryBuilder->andWhere('d.idMateriel = :idMateriel')
                    ->setParameter('idMateriel',  $ditSearch->getIdMateriel() );
            }
    
            if (!empty($ditSearch->getInternetExterne())) {
                $queryBuilder->andWhere('d.internetExterne = :internetExterne')
                    ->setParameter('internetExterne',  $ditSearch->getInternetExterne() );
            }
    
            if (!empty($ditSearch->getDateDebut())) {
                $queryBuilder->andWhere('d.dateDemande >= :dateDebut')
                    ->setParameter('dateDebut', $ditSearch->getDateDebut());
            }
    
            if (!empty($ditSearch->getDateFin())) {
                $queryBuilder->andWhere('d.dateDemande <= :dateFin')
                    ->setParameter('dateFin', $ditSearch->getDateFin());
            }
    
            if (!empty($ditSearch->getAgenceEmetteur())) {
                $queryBuilder->andWhere('d.agenceServiceEmetteur = :agServEmet')
                    ->setParameter('agServEmet',  $ditSearch->getAgenceEmetteur()->getCodeAgence() . '-' . $ditSearch->getServiceEmetteur()->getCodeService() );
            }
    
            if (!empty($ditSearch->getAgenceDebiteur())) {
                $queryBuilder->andWhere('d.agenceServiceDebiteur = :agServDebit')
                    ->setParameter('agServDebit',  $ditSearch->getAgenceDebiteur()->getCodeAgence() . '-' . $ditSearch->getServiceDebiteur()->getCodeService() );
            }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}