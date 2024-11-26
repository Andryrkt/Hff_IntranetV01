<?php

namespace App\Repository\dit;

use App\Entity\dit\DitSearch;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;


class DitRepository extends EntityRepository
{

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

            $this->applyStatusFilter($queryBuilder, $ditSearch);

            $this->applyCommonFilters($queryBuilder, $ditSearch, $options);

            $this->applyniveauUrgenceFilters($queryBuilder, $ditSearch);

             // section affect et support section
            $this->applySection($queryBuilder, $ditSearch);

            $this->applyAgencyServiceFilters($queryBuilder, $ditSearch, $options);
        
            if (!$options['boolean']) {
                $queryBuilder
                    ->andWhere(
                        $queryBuilder->expr()->orX(
                            'd.agenceDebiteurId IN (:agenceAutoriserIds)',
                            'd.agenceEmetteurId = :codeAgence'
                        )
                    )
                    ->setParameter('agenceAutoriserIds', $options['agenceAutoriserIds'], \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
                    ->setParameter('codeAgence', $options['codeAgence'])
                    ->andWhere(
                        $queryBuilder->expr()->orX(
                            'd.serviceDebiteurId IN (:serviceAutoriserIds)',
                            'd.serviceEmetteurId IN (:serviceAutoriserIds)'
                        )
                    )
                    ->setParameter('serviceAutoriserIds', $options['serviceAutoriserIds'], \Doctrine\DBAL\Connection::PARAM_INT_ARRAY);
            }
            

        $queryBuilder->orderBy('d.dateDemande', 'DESC')
        ->addOrderBy('d.numeroDemandeIntervention', 'ASC');

        $queryBuilder->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ;

            $paginator = new DoctrinePaginator($queryBuilder->getQuery());

            $totalItems = count($paginator);
            $lastPage = ceil($totalItems / $limit);
            //  $sql = $queryBuilder->getQuery()->getSQL();
            //  echo $sql;

            // Récupérer le nombre de lignes par statut
            $statusCounts = $this->countByStatus($ditSearch, $options);
        //return $queryBuilder->getQuery()->getResult();
        return [
            'data' => iterator_to_array($paginator->getIterator()), // Convertir en tableau si nécessaire
            'totalItems' => $totalItems,
            'currentPage' => $page,
            'lastPage' => $lastPage,
            'statusCounts' => $statusCounts,
        ];
    }

   

    /** =====================================================
     * Undocumented function
     *
     * @param DitSearch $ditSearch
     * @param array $options
     * @return void
     *======================================================*/
    public function countByStatus(DitSearch $ditSearch, array $options)
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->select('s.description AS statut, COUNT(d.id) AS count')
            ->leftJoin('d.idStatutDemande', 's')
            ->leftJoin('d.typeDocument', 'td')
            ->groupBy('s.description');

            // Appliquer le filtre par statut ou exclure les statuts par défaut
            if (!empty($ditSearch->getStatut())) {
                // Si un statut spécifique est recherché, l'utiliser dans la requête
                $queryBuilder->andWhere('s.description LIKE :statut')
                    ->setParameter('statut', '%' . $ditSearch->getStatut() . '%');
            }

            $this->applyCommonFilters($queryBuilder, $ditSearch, $options);
             // section affect et support section
        $this->applySection($queryBuilder, $ditSearch);

        $this->applyAgencyServiceFilters($queryBuilder, $ditSearch, $options);
        if (!$options['boolean']) {
            $queryBuilder
                ->andWhere(
                    $queryBuilder->expr()->orX(
                        'd.agenceDebiteurId IN (:agenceAutoriserIds)',
                        'd.agenceEmetteurId = :codeAgence'
                    )
                )
                ->setParameter('agenceAutoriserIds', $options['agenceAutoriserIds'], \Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
                ->setParameter('codeAgence', $options['codeAgence'])
                ->andWhere(
                    $queryBuilder->expr()->orX(
                        'd.serviceDebiteurId IN (:serviceAutoriserIds)',
                        'd.serviceEmetteurId IN (:serviceAutoriserIds)'
                    )
                )
                ->setParameter('serviceAutoriserIds', $options['serviceAutoriserIds'], \Doctrine\DBAL\Connection::PARAM_INT_ARRAY);
        }
        
        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Undocumented function
     *
     * @param DitSearch $ditSearch
     * @param array $options
     * @return void
     */
    public function findAndFilteredExcel( DitSearch $ditSearch, array $options)
    {
        $queryBuilder = $this->createQueryBuilder('d')
        ->leftJoin('d.typeDocument', 'td')
        ->leftJoin('d.idNiveauUrgence', 'nu')
        ->leftJoin('d.idStatutDemande', 's')
            ;

            $this->applyStatusFilter($queryBuilder, $ditSearch);
            $this->applyniveauUrgenceFilters($queryBuilder, $ditSearch);
            $this->applyCommonFilters($queryBuilder, $ditSearch, $options);

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

    private function applyAgencyServiceFilters($queryBuilder, DitSearch $ditSearch, array $options)
    {
        //if ($options['boolean']) {
            if (!empty($ditSearch->getAgenceEmetteur())) {
                $queryBuilder->andWhere('d.agenceEmetteurId = :agEmet')
                    ->setParameter('agEmet', $ditSearch->getAgenceEmetteur()->getId());
            }
            if (!empty($ditSearch->getServiceEmetteur())) {
                $queryBuilder->andWhere('d.serviceEmetteurId = :agServEmet')
                    ->setParameter('agServEmet', $ditSearch->getServiceEmetteur()->getId());
            }
        // } else {
        //     if ($options['autorisationRoleEnergie']) {
        //         $this->applyAgencyRoleFilter($queryBuilder, $ditSearch, [9, 10, 11]);
        //     } else {
        //         $this->applyAgencyRoleFilter($queryBuilder, $ditSearch, [$options['codeAgence']]);
        //     }
        // }

        if (!empty($ditSearch->getAgenceDebiteur())) {
            $queryBuilder->andWhere('d.agenceDebiteurId = :agDebit')
                        //->andWhere('d.agenceEmetteurId = :agEmet')
                        ->setParameter('agDebit', $ditSearch->getAgenceDebiteur()->getId())
                        //->setParameter('agEmet', $options['codeAgence'])
                        ;
        }

        if (!empty($ditSearch->getServiceDebiteur())) {
            $queryBuilder->andWhere('d.serviceDebiteurId = :serviceDebiteur')
                ->setParameter('serviceDebiteur', $ditSearch->getServiceDebiteur()->getId());
        }
    }

    
    private function applyStatusFilter($queryBuilder, DitSearch $ditSearch)
    {
        $statusesDefault = [50, 51, 53];
        
        if (!empty($ditSearch->getStatut())) {
            $queryBuilder->andWhere('s.description LIKE :statut')
                ->setParameter('statut', '%' . $ditSearch->getStatut() . '%');
        } else {
            $queryBuilder->andWhere($queryBuilder->expr()->in('s.id', ':excludedStatuses'))
                ->setParameter('excludedStatuses', $statusesDefault);
        }
    }


    private function applyniveauUrgenceFilters($queryBuilder, DitSearch $ditSearch)
    {
        if (!empty($ditSearch->getNiveauUrgence())) {
            $queryBuilder->andWhere('nu.description LIKE :niveauUrgence')
                ->setParameter('niveauUrgence', '%' . $ditSearch->getNiveauUrgence()->getDescription() . '%');
        }
    }

    private function applyCommonFilters($queryBuilder, DitSearch $ditSearch, array $options)
    {
        // Filters for type, urgency, material, etc.
        if (!empty($ditSearch->getTypeDocument())) {
            $queryBuilder->andWhere('td.description LIKE :typeDocument')
                ->setParameter('typeDocument', '%' . $ditSearch->getTypeDocument() . '%');
        }
        
        if (!empty($ditSearch->getIdMateriel())) {
            $queryBuilder->andWhere('d.idMateriel = :idMateriel')
                ->setParameter('idMateriel', $ditSearch->getIdMateriel());
        }

        if (!empty($ditSearch->getInternetExterne())) {
            $queryBuilder->andWhere('d.internetExterne = :internetExterne')
                ->setParameter('internetExterne', $ditSearch->getInternetExterne());
        }

        if (!empty($ditSearch->getDateDebut())) {
            $queryBuilder->andWhere('d.dateDemande >= :dateDebut')
                ->setParameter('dateDebut', $ditSearch->getDateDebut());
        }

        if (!empty($ditSearch->getDateFin())) {
            $queryBuilder->andWhere('d.dateDemande <= :dateFin')
                ->setParameter('dateFin', $ditSearch->getDateFin());
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

    }


    private function applyAgencyRoleFilter($queryBuilder, DitSearch $ditSearch, array $agencyIds)
    {
        if (!empty($ditSearch->getAgenceEmetteur())) {
            $queryBuilder->andWhere('d.agenceEmetteurId = :agEmet')
                ->setParameter('agEmet', $ditSearch->getAgenceEmetteur()->getId());
        } else {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    'd.agenceEmetteurId IN (:agencesRattachees)',
                    'd.agenceDebiteurId IN (:agencesRattachees)'
                )
            )
            ->setParameter('agencesRattachees', $agencyIds);
        }
    }

private function applySection($queryBuilder, DitSearch $ditSearch)
{
    // Filtrer selon la section affectée
    $sectionAffectee = $ditSearch->getSectionAffectee();
    if (!empty($sectionAffectee)) {
        $groupes = ['Chef section', 'Chef de section', 'Responsable section'];
        $orX = $queryBuilder->expr()->orX();

        foreach ($groupes as $index => $groupe) {
            $phraseConstruite = $groupe . $sectionAffectee;
            // Utiliser une clé paramétrique plus simple
            $paramKey = 'sectionAffectee_' . $index;
            $orX->add($queryBuilder->expr()->like('d.sectionAffectee', ":$paramKey"));
            $queryBuilder->setParameter($paramKey, '%'.$phraseConstruite.'%');
        }

        // Ajouter la clause WHERE avec OR
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
}

    

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
}