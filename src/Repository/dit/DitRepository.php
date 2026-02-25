<?php

namespace App\Repository\dit;

use App\Entity\dit\DitSearch;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use App\Entity\dit\DemandeIntervention;
use Doctrine\ORM\NonUniqueResultException;
use App\Entity\atelierRealise\AtelierRealise;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;


class DitRepository extends EntityRepository
{
    // $query = $queryBuilder->getQuery();
    // $sql = $query->getSQL();
    // $params = $query->getParameters();

    // dump("SQL : " . $sql . "\n");
    // foreach ($params as $param) {
    //     dump($param->getName());
    //     dump($param->getValue());
    // }

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
    public function findPaginatedAndFiltered(int $page = 1, int $limit = 10, DitSearch $ditSearch, array $options, array $agenceServiceAutorises)
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->leftJoin('d.typeDocument', 'td')
            ->leftJoin('d.idNiveauUrgence', 'nu')
            ->leftJoin('d.idStatutDemande', 's')
            ->leftJoin(AtelierRealise::class, 'ar', 'WITH', 'd.reparationRealise = ar.codeAtelier')
            ->leftJoin('d.societe', 'soc')
            ->andWhere('soc.codeSociete = :codeSociete')
            ->setParameter('codeSociete', 'HF');

        $this->applyStatusFilter($queryBuilder, $ditSearch);
        $this->applyCommonFilters($queryBuilder, $ditSearch, $options);
        $this->applyniveauUrgenceFilters($queryBuilder, $ditSearch);
        $this->applySection($queryBuilder, $ditSearch); // section affect et support section
        $this->applyAgencyServiceFilters($queryBuilder, $ditSearch, $agenceServiceAutorises);
        $this->applyAgencyUserFilter($queryBuilder, $options);


        $queryBuilder->orderBy('d.dateDemande', 'DESC')
            ->addOrderBy('d.numeroDemandeIntervention', 'ASC');

        $queryBuilder->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
        ;

        $paginator = new DoctrinePaginator($queryBuilder->getQuery());
        $totalItems = count($paginator);
        $lastPage = ceil($totalItems / $limit);

        // Récupérer le nombre de lignes par statut
        $statusCounts = $this->countByStatus($ditSearch, $options, $agenceServiceAutorises);

        return [
            'data' => iterator_to_array($paginator->getIterator()), // Convertir en tableau si nécessaire
            'totalItems' => $totalItems,
            'currentPage' => $page,
            'lastPage' => $lastPage,
            'statusCounts' => $statusCounts,
        ];
    }

    /**
     * Applique le filtre par agence de l'utilisateur connecté
     *
     * @param QueryBuilder $queryBuilder
     * @param array $options
     * @return void
     */
    private function applyAgencyUserFilter(QueryBuilder $queryBuilder, array $options): void
    {
        // Vérifier si l'agence de l'utilisateur est fournie dans les options
        if (isset($options['user_agency']) && !empty($options['user_agency']) && !$options['boolean'] && in_array($options['user_agency'], ['01', '20', '30', '40', '60'])) {
            $queryBuilder
                ->andWhere('ar.codeAgence = :userAgency')
                ->setParameter('userAgency', $options['user_agency']);
        }
    }

    public function recupConstraitSoumission($numDit)
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->select('d.internetExterne AS client, s.description AS statut, d.numeroOR AS numero_or')
            ->leftJoin('d.idStatutDemande', 's')
            ->andWhere('d.numeroDemandeIntervention = :numDit')
            ->setParameter('numDit', $numDit);

        return $queryBuilder->getQuery()->getResult();
    }


    /** =====================================================
     * Undocumented function
     *
     * @param DitSearch $ditSearch
     * @param array $options
     * @return void
     *======================================================*/
    public function countByStatus(DitSearch $ditSearch, array $options, array $agenceServiceAutorises)
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

        $this->applyAgencyServiceFilters($queryBuilder, $ditSearch, $agenceServiceAutorises);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * recupère les donnnées à ajouter dans excel
     *
     * @param DitSearch $ditSearch
     * @param array $options
     * @return void
     */
    public function findAndFilteredExcel(DitSearch $ditSearch, array $options)
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->leftJoin('d.typeDocument', 'td')
            ->leftJoin('d.idNiveauUrgence', 'nu')
            ->leftJoin('d.idStatutDemande', 's');

        $this->applyStatusFilter($queryBuilder, $ditSearch);
        $this->applyniveauUrgenceFilters($queryBuilder, $ditSearch);
        $this->applyCommonFilters($queryBuilder, $ditSearch, $options);
        $this->applySection($queryBuilder, $ditSearch); // section affect et support section
        $this->applyAgencyServiceFilters($queryBuilder, $ditSearch, $options);
        $this->applyAgencyUserFilter($queryBuilder, $options);

        //filtre selon le section affectée
        // $sectionAffectee = $ditSearch->getSectionAffectee();
        // if (!empty($sectionAffectee)) {
        //     $groupes = ['Chef section', 'Chef de section', 'Responsable section', 'Chef d\'équipe']; // Les groupes de mots disponibles
        //     $resultatsSectionAffectee = [];

        //     foreach ($groupes as $groupe) {
        //         // Construire la phrase avec le groupe de mots
        //         $phraseConstruite = $groupe . $sectionAffectee;

        //         // Cloner le QueryBuilder initial pour cette requête
        //         $tempQueryBuilder = clone $queryBuilder;

        //         // Ajouter la condition pour cette itération
        //         $tempQueryBuilder->andWhere('d.sectionAffectee = :sectionAffectee')
        //             ->setParameter('sectionAffectee', $phraseConstruite);

        //         // Exécuter la requête pour cette itération et accumuler les résultats
        //         $resultatsSectionAffectee = array_merge(
        //             $resultatsSectionAffectee,
        //             $tempQueryBuilder->getQuery()->getResult()
        //         );
        //     }

        //     // Si des résultats sont trouvés pour la section affectée, filtrer la liste
        //     if (!empty($resultatsSectionAffectee)) {
        //         // Optionnel : enlever les doublons si nécessaire
        //         $resultatsSectionAffectee = array_unique($resultatsSectionAffectee, SORT_REGULAR);
        //         // Retourner les résultats trouvés
        //         return $resultatsSectionAffectee;
        //     }
        // }


        $queryBuilder->orderBy('d.dateDemande', 'DESC')
            ->addOrderBy('d.numeroDemandeIntervention', 'ASC');


        return $queryBuilder->getQuery()->getResult();
    }

    private function applyAgencyServiceFilters($queryBuilder, DitSearch $ditSearch, array $agenceServiceAutorises)
    {
        // Condition sur les couples agences-services
        $orX1 = $queryBuilder->expr()->orX();
        $orX2 = $queryBuilder->expr()->orX();
        foreach ($agenceServiceAutorises as $i => $tab) {
            $orX1->add(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('d.agenceEmetteurId', ':agEmetteur_' . $i),
                    $queryBuilder->expr()->eq('d.serviceEmetteurId', ':servEmetteur_' . $i)
                )
            );
            $queryBuilder->setParameter('agEmetteur_' . $i, $tab['agence_id']);
            $queryBuilder->setParameter('servEmetteur_' . $i, $tab['service_id']);
            $orX2->add(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('d.agenceDebiteurId', ':agDebiteur_' . $i),
                    $queryBuilder->expr()->eq('d.serviceDebiteurId', ':servDebiteur_' . $i)
                )
            );
            $queryBuilder->setParameter('agDebiteur_' . $i, $tab['agence_id']);
            $queryBuilder->setParameter('servDebiteur_' . $i, $tab['service_id']);
        }
        $queryBuilder
            ->andWhere($orX1)
            ->andWhere($orX2);

        if (!empty($ditSearch->getAgenceEmetteur())) {
            $queryBuilder->andWhere('d.agenceEmetteurId = :agEmet')
                ->setParameter('agEmet', $ditSearch->getAgenceEmetteur());
        }
        if (!empty($ditSearch->getServiceEmetteur())) {
            $queryBuilder->andWhere('d.serviceEmetteurId = :agServEmet')
                ->setParameter('agServEmet', $ditSearch->getServiceEmetteur());
        }

        if (!empty($ditSearch->getAgenceDebiteur())) {
            $queryBuilder->andWhere('d.agenceDebiteurId = :agDebit')
                ->setParameter('agDebit', $ditSearch->getAgenceDebiteur());
        }

        if (!empty($ditSearch->getServiceDebiteur())) {
            $queryBuilder->andWhere('d.serviceDebiteurId = :serviceDebiteur')
                ->setParameter('serviceDebiteur', $ditSearch->getServiceDebiteur());
        }
    }


    private function applyStatusFilter($queryBuilder, DitSearch $ditSearch)
    {
        $statusesDefault = [
            DemandeIntervention::STATUT_A_AFFECTER,
            DemandeIntervention::STATUT_AFFECTEE_SECTION,
            DemandeIntervention::STATUT_CLOTUREE_VALIDER
        ];

        if (!empty($ditSearch->getStatut())) {
            $queryBuilder->andWhere('s.description LIKE :statut')
                ->setParameter('statut', '%' . $ditSearch->getStatut() . '%');
        } elseif (empty($ditSearch->getNumDit()) && empty($ditSearch->getIdMateriel()) && empty($ditSearch->getNumParc()) && empty($ditSearch->getNumSerie()) && (empty($ditSearch->getNumOr()) && $ditSearch->getNumOr() == 0) && empty($ditSearch->getEtatFacture())) {
            $queryBuilder->andWhere($queryBuilder->expr()->in('s.id', ':excludedStatuses'))
                ->setParameter('excludedStatuses', $statusesDefault);
        }
    }

    private function applyStatusFilterDa($queryBuilder, DitSearch $ditSearch)
    {
        $statusesDefault = [
            DemandeIntervention::STATUT_AFFECTEE_SECTION,
            DemandeIntervention::STATUT_CLOTUREE_VALIDER
        ];

        if (!empty($ditSearch->getStatut())) {
            $queryBuilder->andWhere('s.description LIKE :statut')
                ->setParameter('statut', '%' . $ditSearch->getStatut() . '%');
        }
        $queryBuilder->andWhere($queryBuilder->expr()->in('s.id', ':excludedStatuses'))
            ->setParameter('excludedStatuses', $statusesDefault);
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

        if (!empty($ditSearch->getEtatFacture())) {
            $queryBuilder->andWhere('d.etatFacturation = :etatFac')
                ->setParameter('etatFac', $ditSearch->getEtatFacture());
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
        if (!empty($ditSearch->getNumDit())) {

            $queryBuilder->andWhere('d.numeroDemandeIntervention = :numDit')
                ->setParameter('numDit', $ditSearch->getNumDit());
        }

        //filtrer selon le numero dit
        if (!empty($ditSearch->getNumDevis())) {

            $queryBuilder->andWhere('d.numeroDevisRattache = :numDevis')
                ->setParameter('numDevis', $ditSearch->getNumDevis());
        }

        //filtre selon le numero Or
        if (!empty($ditSearch->getNumOr()) && $ditSearch->getNumOr() !== 0) {
            $queryBuilder->andWhere('d.numeroOR = :numOr')
                ->setParameter('numOr', $ditSearch->getNumOr());
        }

        //filtre selon le numero Or mais pour le elseif filtre tous les listes de ne pas afficher les statuts réfusé
        if (!empty($ditSearch->getStatutOr())) {
            $queryBuilder->andWhere('d.statutOr = :statutOr')
                ->setParameter('statutOr',  $ditSearch->getStatutOr());
        } elseif (empty($ditSearch->getNumOr()) && empty($ditSearch->getNumDevis()) && empty($ditSearch->getNumDit())) {
            $queryBuilder->andWhere('d.statutOr NOT LIKE :statutRefuser OR d.statutOr IS NULL')
                ->setParameter('statutRefuser', 'Refusé%');
        }

        //filtre selon le categorie de demande
        if (!empty($ditSearch->getCategorie())) {
            $queryBuilder->andWhere('d.categorieDemande = :categorieDemande')
                ->setParameter('categorieDemande', $ditSearch->getCategorie());
        }

        //filtre selon le categorie de demande
        if (!empty($ditSearch->getUtilisateur())) {
            $queryBuilder->andWhere('d.utilisateurDemandeur LIKE :utilisateur')
                ->setParameter('utilisateur', '%' . $ditSearch->getUtilisateur() . '%');
        }

        if ($ditSearch->getDitSansOr()) {
            $queryBuilder->andWhere("d.numeroOR = ''");
        }

        if (!empty($ditSearch->getReparationRealise())) {
            $queryBuilder->andWhere('d.reparationRealise = :reparationRealise')
                ->setParameter('reparationRealise', $ditSearch->getReparationRealise());
        }
    }


    // private function applyAgencyRoleFilter($queryBuilder, DitSearch $ditSearch, array $agencyIds)
    // {
    //     if (!empty($ditSearch->getAgenceEmetteur())) {
    //         $queryBuilder->andWhere('d.agenceEmetteurId = :agEmet')
    //             ->setParameter('agEmet', $ditSearch->getAgenceEmetteur()->getId());
    //     } else {
    //         $queryBuilder->andWhere(
    //             $queryBuilder->expr()->orX(
    //                 'd.agenceEmetteurId IN (:agencesRattachees)',
    //                 'd.agenceDebiteurId IN (:agencesRattachees)'
    //             )
    //         )
    //         ->setParameter('agencesRattachees', $agencyIds);
    //     }
    // }

    private function applySection($queryBuilder, DitSearch $ditSearch)
    {
        // Filtrer selon la section affectée
        $sectionAffectee = $ditSearch->getSectionAffectee();
        if (!empty($sectionAffectee)) {
            $groupes = ['Chef section', 'Chef de section', 'Responsable section', 'Chef d\'équipe'];
            $orX = $queryBuilder->expr()->orX();

            foreach ($groupes as $index => $groupe) {
                $phraseConstruite = $groupe . $sectionAffectee;
                // Utiliser une clé paramétrique plus simple
                $paramKey = 'sectionAffectee_' . $index;
                $orX->add($queryBuilder->expr()->like('d.sectionAffectee', ":$paramKey"));
                $queryBuilder->setParameter($paramKey, '%' . $phraseConstruite . '%');
            }

            // Ajouter la clause WHERE avec OR
            $queryBuilder->andWhere($orX);
        }

        //filtre selon le section support 1
        $sectionSupport1 = $ditSearch->getSectionSupport1();
        if (!empty($sectionSupport1)) {
            $groupes = ['Chef section', 'Chef de section', 'Responsable section', 'Chef d\'équipe'];
            $orX = $queryBuilder->expr()->orX();

            foreach ($groupes as $groupe) {
                $phraseConstruite = $groupe . $sectionSupport1;
                $orX->add($queryBuilder->expr()->eq('d.sectionSupport1', ':sectionSupport1_' . md5($phraseConstruite)));
                $queryBuilder->setParameter('sectionSupport1_' . md5($phraseConstruite), $phraseConstruite);
            }

            $queryBuilder->andWhere($orX);
        }

        //filtre selon le section support 2
        $sectionSupport2 = $ditSearch->getSectionSupport2();
        if (!empty($sectionSupport2)) {
            $groupes = ['Chef section', 'Chef de section', 'Responsable section', 'Chef d\'équipe'];
            $orX = $queryBuilder->expr()->orX();

            foreach ($groupes as $groupe) {
                $phraseConstruite = $groupe . $sectionSupport2;
                $orX->add($queryBuilder->expr()->eq('d.sectionSupport2', ':sectionSupport2_' . md5($phraseConstruite)));
                $queryBuilder->setParameter('sectionSupport2_' . md5($phraseConstruite), $phraseConstruite);
            }

            $queryBuilder->andWhere($orX);
        }

        //filtre selon le section support 3
        $sectionSupport3 = $ditSearch->getSectionSupport3();
        if (!empty($sectionSupport3)) {
            $groupes = ['Chef section', 'Chef de section', 'Responsable section', 'Chef d\'équipe'];
            $orX = $queryBuilder->expr()->orX();

            foreach ($groupes as $groupe) {
                $phraseConstruite = $groupe . $sectionSupport3;
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
                "SUM(
                (CASE WHEN d.pieceJoint01 IS NOT NULL AND d.pieceJoint01 != '' THEN 1 ELSE 0 END) + 
                (CASE WHEN d.pieceJoint02 IS NOT NULL AND d.pieceJoint02 != '' THEN 1 ELSE 0 END) + 
                (CASE WHEN d.pieceJoint03 IS NOT NULL AND d.pieceJoint03 != '' THEN 1 ELSE 0 END)
            ) AS nombrePiecesJointes"
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

        if (!empty($criteria['niveauUrgence'])) {
            $queryBuilder->andWhere('d.idNiveauUrgence = :idniveau')
                ->setParameter('idniveau', $criteria['niveauUrgence']->getId());
        }

        $results = $queryBuilder->getQuery()->getArrayResult();

        // Extraire les resultats dans un tableau simple
        $numOr = array_column($results, 'numeroOR');

        return $numOr;
    }

    public function findNumDit($numOr)
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->leftJoin('d.idNiveauUrgence', 'nu');
        $queryBuilder
            ->select('d.numeroDemandeIntervention, nu.description')
            ->Where('d.numeroOR = :numOR')
            ->setParameter('numOR', $numOr)
        ;

        return $queryBuilder->getQuery()->getResult();
    }

    public function findAteRealiserPar($numDit)
    {
        try {
            return $this->createQueryBuilder('d')
                ->select('d.reparationRealise')
                ->where('d.numeroDemandeIntervention = :numDit')
                ->setParameter('numDit', $numDit)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return null; // Ou toute autre valeur par défaut
        }
    }

    /** MIGRATION */
    public function findDitMigration()
    {
        return $this->createQueryBuilder('d')
            ->Where('d.numMigration = :numMigr')
            ->setParameter('numMigr', 7)
            // ->andWhere('d.numeroDemandeIntervention = :numDit')
            // ->setParameter('numDit', 'DIT25010315')
            ->orderBy('d.numeroDemandeIntervention', 'ASC')
            ->getQuery()
            ->getResult();
    }


    /** RECUPERE interne exter pour facture */
    public function findInterneExterne($numDit)
    {
        return $this->createQueryBuilder('d')
            ->select('d.internetExterne')
            ->where('d.numeroDemandeIntervention = :numDit')
            ->setParameter('numDit', $numDit)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findNumClient(string $numDit)
    {
        return $this->createQueryBuilder('d')
            ->select('d.numeroClient')
            ->where('d.numeroDemandeIntervention = :numDit')
            ->setParameter('numDit', $numDit)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function findNumeroOrDit(string $numDit)
    {
        return $this->createQueryBuilder('d')
            ->select('d.numeroOR')
            ->where('d.numeroDemandeIntervention = :numDit')
            ->setParameter('numDit', $numDit)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * FONCTION Pour récupérer les donnée filtrer  pour demande d'approvisionnement
     *
     * @param integer $page
     * @param integer $limit
     * @param DitSearch $ditSearch
     * @param array $options
     * @return void
     */
    public function findPaginatedAndFilteredDa(int $page = 1, int $limit = 10, DitSearch $ditSearch, array $options)
    {

        $queryBuilder = $this->createQueryBuilder('d')
            ->leftJoin('d.typeDocument', 'td')
            ->leftJoin('d.idNiveauUrgence', 'nu')
            ->leftJoin('d.idStatutDemande', 's')
            ->innerJoin('d.societe', 'soc')
            ->where('d.sectionAffectee <> :sectionAffectee')
            ->setParameter('sectionAffectee', '')
            ->andWhere('soc.codeSociete = :codeSociete')
            ->setParameter('codeSociete', 'HF');

        $this->applyStatusFilterDa($queryBuilder, $ditSearch);

        $this->applyCommonFilters($queryBuilder, $ditSearch, $options);

        $this->applyniveauUrgenceFilters($queryBuilder, $ditSearch);

        // section affect et support section
        $this->applySection($queryBuilder, $ditSearch);

        $this->applyAgencyServiceFilters($queryBuilder, $ditSearch, $options);


        $queryBuilder->orderBy('d.dateDemande', 'DESC')
            ->addOrderBy('d.numeroDemandeIntervention', 'ASC');

        $queryBuilder->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
        ;
        //DEBUT
        // $query = $queryBuilder->getQuery();
        // $sql = $query->getSQL();
        // $params = $query->getParameters();

        // dump("SQL : " . $sql . "\n");
        // foreach ($params as $param) {
        //     dump($param->getName());
        //     dump($param->getValue());
        // }
        //FIN
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

    public function getNumDitAAnnuler()
    {
        $dateNow = new \DateTime(); // maintenant
        $dateYesterday = (clone $dateNow)->modify('-1 day'); // 1 jour avant

        return $this->createQueryBuilder('d')
            ->select('d.numeroDemandeIntervention')
            ->where('d.aAnnuler = :aAnnuler')
            ->andWhere('d.dateAnnulation BETWEEN :yesterday AND :now')
            ->setParameters([
                'aAnnuler' => 1,
                'yesterday' => $dateYesterday,
                'now' => $dateNow,
            ])
            ->getQuery()
            ->getSingleColumnResult()
        ;
    }

    public function getNumclient($numOr)
    {
        try {
            $numcli =  $this->createQueryBuilder('d')
                ->select('d.numeroClient')
                ->where('d.numeroOR = :numOr')
                ->setParameter('numOr', $numOr)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            $numcli = null; // ou une valeur par défaut
        }
        return $numcli;
    }

    public function getInterneExterne($numOr)
    {
        try {
            $intExt =  $this->createQueryBuilder('d')
                ->select('d.internetExterne')
                ->where('d.numeroOR = :numOr')
                ->setParameter('numOr', $numOr)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            $intExt = null; // ou une valeur par défaut
        }
        return $intExt;
    }

    public function getStatutIdDit(string $numDit)
    {
        return $this->createQueryBuilder('d')
            ->select('s.id') // <-- un champ scalaire
            ->join('d.idStatutDemande', 's') // <-- jointure obligatoire
            ->where('d.numeroDemandeIntervention = :numDit')
            ->setParameter('numDit', $numDit)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getNiveauUrgence(string $numDit)
    {
        $queryBuilder =  $this->createQueryBuilder('d')
            ->select('nu.description') // <-- un champ scalaire
            ->join('d.idNiveauUrgence', 'nu') // <-- jointure obligatoire
            ->where('d.numeroDemandeIntervention = :numDit')
            ->setParameter('numDit', $numDit);

        return $queryBuilder->getQuery()
            ->getSingleScalarResult();
    }

    public function getNumOr(string $numDit)
    {
        return $this->createQueryBuilder('d')
            ->select('d.numeroOR')
            ->where('d.numeroDemandeIntervention = :numDit')
            ->setParameter('numDit', $numDit)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
