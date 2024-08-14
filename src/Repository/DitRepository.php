<?php

namespace App\Repository;

use App\Entity\DitSearch;
use Doctrine\ORM\EntityRepository;


class DitRepository extends EntityRepository
{
    public function findAllNumeroDit()
{
    $result = $this->createQueryBuilder('a')
        ->select('a.numeroDemandeIntervention')
        ->getQuery()
        ->getScalarResult();
        return array_column($result, 'numeroDemandeIntervention');
}

    /** MAGASIN  */
    public function findNumOr($criteria = [])
    {
        $queryBuilder = $this->createQueryBuilder('d');
        $queryBuilder
        ->select('d.numeroOR')
        ->Where('d.dateValidationOr IS NOT NULL')
        ->andWhere('d.dateValidationOr != :empty')
        ->setParameter('empty', '')
        ;

        if(!empty($criteria['niveauUrgence'])){
            $queryBuilder->andWhere('d.idNiveauUrgence = :idniveau')
            ->setParameter('idniveau', $criteria['niveauUrgence']->getId()) ;
        }

        $results = $queryBuilder->getQuery()->getArrayResult();

        // Extraire les resultats dans un tableau simple
        $numOr = array_column($results, 'numeroOR');
        
        return $numOr;
            
    }

    public function findNumDit($numOr)
    {
        $queryBuilder = $this->createQueryBuilder('d')
        ->leftJoin('d.idNiveauUrgence', 'nu')
        ;
        $queryBuilder
        ->select('d.numeroDemandeIntervention, nu.description')
        ->Where('d.numeroOR = :numOR')
        ->setParameter('numOR', $numOr )
        ;

      return $queryBuilder->getQuery()->getResult();

    }
    
    /** LISTE DIT */
    /**
     * FONCTION Pour récupérer les donnée filtrer
     *
     * @param integer $page
     * @param integer $limit
     * @param DitSearch $ditSearch
     * @param array $options
     * @return void
     */
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

        //filtre pour le statut        
        if (!empty($ditSearch->getStatut())) {
            $queryBuilder->andWhere('s.description LIKE :statut')
                ->setParameter('statut', '%' . $ditSearch->getStatut() . '%');
        }

        //filtre pour le type de document
        if (!empty($ditSearch->getTypeDocument())) {
            $queryBuilder->andWhere('td.description LIKE :typeDocument')
                ->setParameter('typeDocument', '%' . $ditSearch->getTypeDocument() . '%');
        }

        //filtre pour le niveau d'urgence
        if (!empty($ditSearch->getNiveauUrgence())) {
            $queryBuilder->andWhere('nu.description LIKE :niveauUrgence')
                ->setParameter('niveauUrgence', '%' . $ditSearch->getNiveauUrgence() . '%');
        }

        //filtre pour l'id materiel
        if (!empty($ditSearch->getIdMateriel())) {
            $queryBuilder->andWhere('d.idMateriel = :idMateriel')
                ->setParameter('idMateriel',  $ditSearch->getIdMateriel() );
        }

        //filtre sur l'interne ou externe
        if (!empty($ditSearch->getInternetExterne())) {
            $queryBuilder->andWhere('d.internetExterne = :internetExterne')
                ->setParameter('internetExterne',  $ditSearch->getInternetExterne() );
        }

        //filtre date debut
        if (!empty($ditSearch->getDateDebut())) {
            $queryBuilder->andWhere('d.dateDemande >= :dateDebut')
                ->setParameter('dateDebut', $ditSearch->getDateDebut());
        }

        //filtre date fin
        if (!empty($ditSearch->getDateFin())) {
            $queryBuilder->andWhere('d.dateDemande <= :dateFin')
                ->setParameter('dateFin', $ditSearch->getDateFin());
        }

        
        if ($options['boolean']) {
            //filtre selon l'agence emettteur
            if (!empty($ditSearch->getAgenceEmetteur())) {
                $queryBuilder->andWhere('d.agenceEmetteurId = :agEmet')
                ->setParameter('agEmet',  $ditSearch->getAgenceEmetteur()->getId());
            }
            //filtre selon le service emetteur
            if (!empty($ditSearch->getServiceEmetteur())) {
                $queryBuilder->andWhere('d.serviceEmetteurId = :agServEmet')
                ->setParameter('agServEmet', $ditSearch->getServiceEmetteur()->getId());
            }
        } else {
            //ceci est figer pour les utilisateur autre que l'administrateur
                $queryBuilder->andWhere('d.agenceServiceEmetteur = :agServEmet')
                ->setParameter('agServEmet',  $options['codeAgence'] . '-' . $options['codeService'] );
        }

        //filtre selon l'agence debiteur
        if (!empty($ditSearch->getAgenceDebiteur())) {
            $queryBuilder->andWhere('d.agenceDebiteurId = :agDebit')
                ->setParameter('agDebit',  $ditSearch->getAgenceDebiteur()->getId() );
        }

        //filtre selon le service debiteur
        if(!empty($ditSearch->getServiceDebiteur())) {
            $queryBuilder->andWhere('d.serviceDebiteurId = :serviceDebiteur')
            ->setParameter('serviceDebiteur', $ditSearch->getServiceDebiteur()->getId());
        }

        //filtrer selon le numero dit
        if(!empty($ditSearch->getNumDit())) {
        
            $queryBuilder->andWhere('d.numeroDemandeIntervention = :numDit')
            ->setParameter('numDit', $ditSearch->getNumDit());
        }

        //filtre selon le numero Or
        if(!empty($ditSearch->getNumOr()) && $ditSearch->getNumOr() !== 0) {
            $queryBuilder->andWhere('d.numeroOR = :numOr')
            ->setParameter('numOr', $ditSearch->getNumOr());
        }

         //filtre selon le numero Or
         if(!empty($ditSearch->getStatutOr())) {
            $queryBuilder->andWhere('d.statutOr LIKE :statutOr')
            ->setParameter('statutOr', '%' . $ditSearch->getStatutOr() . '%' );
        }

        //filtre selon le categorie de demande
        if(!empty($ditSearch->getServiceDebiteur())) {
            $queryBuilder->andWhere('d.categorieDemande = :categorieDemande')
            ->setParameter('categorieDemande', $ditSearch->getCategorie());
        }

        //filtre selon le categorie de demande
        if(!empty($ditSearch->getUtilisateur())) {
            $queryBuilder->andWhere('d.utilisateurDemandeur LIKE :utilisateur')
            ->setParameter('utilisateur', '%' . $ditSearch->getUtilisateur() . '%');
        }

        if($ditSearch->getDitRattacherOr()){
            $queryBuilder->andWhere("d.numeroOR <> ''");
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

    public function countFiltered(DitSearch $ditSearch,  array $options)
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
    
            if ($options['boolean']) {
                //filtre selon l'agence emettteur
                if (!empty($ditSearch->getAgenceEmetteur())) {
                    $queryBuilder->andWhere('d.agenceEmetteurId = :agServEmet')
                    ->setParameter('agServEmet',  $ditSearch->getAgenceEmetteur()->getId());
                }
                //filtre selon le service emetteur
                if (!empty($ditSearch->getServiceEmetteur())) {
                    $queryBuilder->andWhere('d.serviceEmetteurId = :agServEmet')
                    ->setParameter('agServEmet', $ditSearch->getServiceEmetteur()->getId());
                }
            } else {
                //ceci est figer pour les utilisateur autre que l'administrateur
                    $queryBuilder->andWhere('d.agenceServiceEmetteur = :agServEmet')
                    ->setParameter('agServEmet',  $options['codeAgence'] . '-' . $options['codeService'] );
            }
    
            //filtre selon l'agence debiteur
            if (!empty($ditSearch->getAgenceDebiteur())) {
                $queryBuilder->andWhere('d.agenceDebiteurId = :agServDebit')
                    ->setParameter('agServDebit',  $ditSearch->getAgenceDebiteur()->getId() );
            }
    
            //filtre selon le service debiteur
            if(!empty($ditSearch->getServiceDebiteur())) {
                $queryBuilder->andWhere('d.serviceDebiteurId = :serviceDebiteur')
                ->setParameter('serviceDebiteur', $ditSearch->getServiceDebiteur()->getId());
            }

             //filtrer selon le numero dit
        if(!empty($ditSearch->getNumDit())) {
            $queryBuilder->andWhere('d.numeroDemandeIntervention = :numDit')
            ->setParameter('numDit', $ditSearch->getNumDit());
        }

        //filtre selon le numero Or
        if(!empty($ditSearch->getNumOr()) && $ditSearch->getNumOr() !== 0) {
            $queryBuilder->andWhere('d.numeroOR = :numOr')
            ->setParameter('numOr', $ditSearch->getNumOr());
        }

         //filtre selon le numero Or
         if(!empty($ditSearch->getStatutOr())) {
            $queryBuilder->andWhere('d.statutOr LIKE :statutOr')
            ->setParameter('statutOr', '%' . $ditSearch->getStatutOr() . '%' );
        }

        //filtre selon le categorie de demande
        if(!empty($ditSearch->getServiceDebiteur())) {
            $queryBuilder->andWhere('d.categorieDemande = :categorieDemande')
            ->setParameter('categorieDemande', $ditSearch->getCategorie());
        }

        //filtre selon le categorie de demande
        if(!empty($ditSearch->getUtilisateur())) {
            $queryBuilder->andWhere('d.utilisateurDemandeur LIKE :utilisateur')
            ->setParameter('utilisateur', '%' . $ditSearch->getUtilisateur() . '%');
        }

        if($ditSearch->getDitRattacherOr()){
            $queryBuilder->andWhere("d.numeroOR <> ''");
        }

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function findAndFilteredExcel( DitSearch $ditSearch, array $options)
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
                //filtre selon l'agence emettteur
                if (!empty($ditSearch->getAgenceEmetteur())) {
                    $queryBuilder->andWhere('d.agenceEmetteurId = :agServEmet')
                    ->setParameter('agServEmet',  $ditSearch->getAgenceEmetteur()->getId());
                }
                //filtre selon le service emetteur
                if (!empty($ditSearch->getServiceEmetteur())) {
                    $queryBuilder->andWhere('d.serviceEmetteurId = :agServEmet')
                    ->setParameter('agServEmet', $ditSearch->getServiceEmetteur()->getId());
                }
            } else {
                //ceci est figer pour les utilisateur autre que l'administrateur
                    $queryBuilder->andWhere('d.agenceServiceEmetteur = :agServEmet')
                    ->setParameter('agServEmet',  $options['codeAgence'] . '-' . $options['codeService'] );
            }
    
            //filtre selon l'agence debiteur
            if (!empty($ditSearch->getAgenceDebiteur())) {
                $queryBuilder->andWhere('d.agenceDebiteurId = :agServDebit')
                    ->setParameter('agServDebit',  $ditSearch->getAgenceDebiteur()->getId() );
            }
    
            //filtre selon le service debiteur
            if(!empty($ditSearch->getServiceDebiteur())) {
                $queryBuilder->andWhere('d.serviceDebiteurId = :serviceDebiteur')
                ->setParameter('serviceDebiteur', $ditSearch->getServiceDebiteur()->getId());
            }

             //filtrer selon le numero dit
        if(!empty($ditSearch->getNumDit())) {
            $queryBuilder->andWhere('d.numeroDemandeIntervention = :numDit')
            ->setParameter('numDit', $ditSearch->getNumDit());
        }

        //filtre selon le numero Or
        if(!empty($ditSearch->getNumOr()) && $ditSearch->getNumOr() !== 0) {
            $queryBuilder->andWhere('d.numeroOR = :numOr')
            ->setParameter('numOr', $ditSearch->getNumOr());
        }

         //filtre selon le numero Or
         if(!empty($ditSearch->getStatutOr())) {
            $queryBuilder->andWhere('d.statutOr LIKE :statutOr')
            ->setParameter('statutOr', '%' . $ditSearch->getStatutOr() . '%' );
        }

        //filtre selon le categorie de demande
        if(!empty($ditSearch->getServiceDebiteur())) {
            $queryBuilder->andWhere('d.categorieDemande = :categorieDemande')
            ->setParameter('categorieDemande', $ditSearch->getCategorie());
        }

        //filtre selon le categorie de demande
        if(!empty($ditSearch->getUtilisateur())) {
            $queryBuilder->andWhere('d.utilisateurDemandeur LIKE :utilisateur')
            ->setParameter('utilisateur', '%' . $ditSearch->getUtilisateur() . '%');
        }

        if($ditSearch->getDitRattacherOr()){
            $queryBuilder->andWhere("d.numeroOR <> ''");
        }

        $queryBuilder->orderBy('d.dateDemande', 'DESC')
        ->addOrderBy('d.numeroDemandeIntervention', 'ASC');
            

        return $queryBuilder->getQuery()->getResult();
    }

}