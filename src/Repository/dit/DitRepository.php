<?php

namespace App\Repository\dit;

use App\Entity\dit\DitSearch;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;


class DitRepository extends EntityRepository
{
    public function findAgSevDebiteur($numdit)
    {
        $numeroVersionMax = $this->createQueryBuilder('d')
            ->select('d.agenceServiceDebiteur')
            ->where('d.numeroDemandeIntervention = :numdit')
            ->setParameter('numdit', $numdit)
            ->getQuery()
            ->getSingleScalarResult(); 
    
        return $numeroVersionMax;
    }

    /** DIT SEARCH DEBUT  */
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

    /** DIT SEARCH FIN */

    public function findSectionSupport($id)
    {
        $sectionSupport = $this->createQueryBuilder('d')
            ->select('d.sectionAffectee, d.sectionSupport1, d.sectionSupport2, d.sectionSupport3')
            ->where('d.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getScalarResult();

        // Retourne toutes les sections sous forme d'un tableau
        return $sectionSupport;
    }


    /** recuperation de nombre de pièce jointe */
    public function findNbrPj($numDit)
    {
        $nombrePiecesJointes = $this->createQueryBuilder('d')
            ->select(
                "(CASE WHEN d.pieceJoint01 IS NOT NULL AND d.pieceJoint01 != '' THEN 1 ELSE 0 END + 
                CASE WHEN d.pieceJoint02 IS NOT NULL AND d.pieceJoint02 != '' THEN 1 ELSE 0 END + 
                CASE WHEN d.pieceJoint03 IS NOT NULL AND d.pieceJoint03 != '' THEN 1 ELSE 0 END) AS nombrePiecesJointes"
            )
            ->where('d.numeroDemandeIntervention = :numDit')
            ->setParameter('numDit', $numDit)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $nombrePiecesJointes;
    }


   

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

            $excludedStatuses = [9, 18, 22, 24, 26, 32, 33, 34, 35, 52];
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
            $queryBuilder->andWhere('d.statutOr = :statutOr')
            ->setParameter('statutOr',  $ditSearch->getStatutOr());
        }

        //filtre selon le categorie de demande
        if(!empty($ditSearch->getCategorie())) {
            $queryBuilder->andWhere('d.categorieDemande = :categorieDemande')
            ->setParameter('categorieDemande', $ditSearch->getCategorie());
        }

        //filtre selon le categorie de demande
        if(!empty($ditSearch->getUtilisateur())) {
            $queryBuilder->andWhere('d.utilisateurDemandeur LIKE :utilisateur')
            ->setParameter('utilisateur', '%' . $ditSearch->getUtilisateur() . '%');
        }

        if($ditSearch->getDitSansOr()){
            $queryBuilder->andWhere("d.numeroOR = ''");
        }

         //filtre selon le section affectée
         $sectionAffectee = $ditSearch->getSectionAffectee();
    if (!empty($sectionAffectee)) {
        $groupes = ['Chef section', 'Chef de section', 'Responsable section'];
        $orX = $queryBuilder->expr()->orX();

        foreach ($groupes as $groupe) {
            $phraseConstruite = $groupe. $sectionAffectee;
            $orX->add($queryBuilder->expr()->eq('d.sectionAffectee', ':sectionAffectee_' . md5($phraseConstruite)));
            $queryBuilder->setParameter('sectionAffectee_' . md5($phraseConstruite), $phraseConstruite);
        }

        $queryBuilder->andWhere($orX);
    }

        //filtre selon le section support 1
        $sectionSupport1 = $ditSearch->getSectionSupport1();
        if (!empty($sectionSupport1)) {
            $groupes = ['Chef section', 'Chef de section', 'Responsable section'];
            $orX = $queryBuilder->expr()->orX();
    
            foreach ($groupes as $groupe) {
                $phraseConstruite = $groupe. $sectionSupport1;
                $orX->add($queryBuilder->expr()->eq('d.sectionSupport1', ':sectionSupport1_' . md5($phraseConstruite)));
                $queryBuilder->setParameter('sectionSupport1_' . md5($phraseConstruite), $phraseConstruite);
            }
    
            $queryBuilder->andWhere($orX);
        }

         //filtre selon le section support 2
        $sectionSupport2 = $ditSearch->getSectionSupport2();
        if (!empty($sectionSupport2)) {
            $groupes = ['Chef section', 'Chef de section', 'Responsable section'];
            $orX = $queryBuilder->expr()->orX();
            
            foreach ($groupes as $groupe) {
                $phraseConstruite = $groupe. $sectionSupport2;
                $orX->add($queryBuilder->expr()->eq('d.sectionSupport2', ':sectionSupport2_' . md5($phraseConstruite)));
                $queryBuilder->setParameter('sectionSupport2_' . md5($phraseConstruite), $phraseConstruite);
            }
            
            $queryBuilder->andWhere($orX);
        }

          //filtre selon le section support 3
        $sectionSupport3 = $ditSearch->getSectionSupport1();
        if (!empty($sectionSupport3)) {
            $groupes = ['Chef section', 'Chef de section', 'Responsable section'];
            $orX = $queryBuilder->expr()->orX();
    
            foreach ($groupes as $groupe) {
                $phraseConstruite = $groupe. $sectionSupport3;
                $orX->add($queryBuilder->expr()->eq('d.sectionSupport3', ':sectionSupport3_' . md5($phraseConstruite)));
                $queryBuilder->setParameter('sectionSupport3_' . md5($phraseConstruite), $phraseConstruite);
            }
    
            $queryBuilder->andWhere($orX);
        }
        


        $queryBuilder->orderBy('d.dateDemande', 'DESC')
        ->addOrderBy('d.numeroDemandeIntervention', 'ASC');

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

    public function findAndFilteredExcel( DitSearch $ditSearch, array $options)
    {
        $queryBuilder = $this->createQueryBuilder('d')
        ->leftJoin('d.typeDocument', 'td')
        ->leftJoin('d.idNiveauUrgence', 'nu')
        ->leftJoin('d.idStatutDemande', 's')
            ;

            $excludedStatuses = [9, 18, 22, 24, 26, 32, 33, 34, 35, 52];
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
            $queryBuilder->andWhere('d.statutOr = :statutOr')
            ->setParameter('statutOr',  $ditSearch->getStatutOr());
        }

        //filtre selon le categorie de demande
        if(!empty($ditSearch->getCategorie())) {
            $queryBuilder->andWhere('d.categorieDemande = :categorieDemande')
            ->setParameter('categorieDemande', $ditSearch->getCategorie());
        }

        //filtre selon le categorie de demande
        if(!empty($ditSearch->getUtilisateur())) {
            $queryBuilder->andWhere('d.utilisateurDemandeur LIKE :utilisateur')
            ->setParameter('utilisateur', '%' . $ditSearch->getUtilisateur() . '%');
        }

        if($ditSearch->getDitSansOr()){
            $queryBuilder->andWhere("d.numeroOR = ''");
        }

         //filtre selon le section affectée
         $sectionAffectee = $ditSearch->getSectionAffectee();
         if (!empty($sectionAffectee)) {
             $groupes = ['Chef section', 'Chef de section', 'Responsable section']; // Les groupes de mots disponibles
             $resultatsSectionAffectee = [];
     
             foreach ($groupes as $groupe) {
                 // Construire la phrase avec le groupe de mots
                 $phraseConstruite = $groupe . $sectionAffectee;
     
                 // Cloner le QueryBuilder initial pour cette requête
                 $tempQueryBuilder = clone $queryBuilder;
     
                 // Ajouter la condition pour cette itération
                 $tempQueryBuilder->andWhere('d.sectionAffectee = :sectionAffectee')
                     ->setParameter('sectionAffectee', $phraseConstruite);
     
                 // Exécuter la requête pour cette itération et accumuler les résultats
                 $resultatsSectionAffectee = array_merge(
                     $resultatsSectionAffectee,
                     $tempQueryBuilder->getQuery()->getResult()
                 );
             }
     
             // Si des résultats sont trouvés pour la section affectée, filtrer la liste
             if (!empty($resultatsSectionAffectee)) {
                 // Optionnel : enlever les doublons si nécessaire
                 $resultatsSectionAffectee = array_unique($resultatsSectionAffectee, SORT_REGULAR);
                 // Retourner les résultats trouvés
                 return $resultatsSectionAffectee;
             }
         }

        $queryBuilder->orderBy('d.dateDemande', 'DESC')
        ->addOrderBy('d.numeroDemandeIntervention', 'ASC');
            

        return $queryBuilder->getQuery()->getResult();
    }

}