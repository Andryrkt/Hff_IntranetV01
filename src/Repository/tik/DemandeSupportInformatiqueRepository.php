<?php

namespace App\Repository\tik;

use App\Entity\tik\TikSearch;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

class DemandeSupportInformatiqueRepository extends EntityRepository
{
    public function findPaginatedAndFiltered(int $page = 1, int $limit = 10, TikSearch $tikSearch = null)
    {
        $queryBuilder = $this->createQueryBuilder('tki')
            ->leftJoin('tki.niveauUrgence', 'nu')
            ;

            //filtre pour le niveau d'urgence
        if (!empty($tikSearch->getNiveauUrgence())) {
            $queryBuilder->andWhere('nu.description LIKE :niveauUrgence')
                ->setParameter('niveauUrgence', '%' . $tikSearch->getNiveauUrgence() . '%');
        }

        //filtre selon le statut
        if (!empty($tikSearch->getStatut())) {
            $queryBuilder->andWhere('tki.idStatutDemande = :idStatut')
            ->setParameter('idStatut',  $tikSearch->getStatut()->getId());
        }

        //filtrer selon le nom d'intervenant
        if(!empty($tikSearch->getNomIntervenant())) {
            $queryBuilder->andWhere('tki.nomIntervenant = :interv')
            ->setParameter('interv', $tikSearch->getNomIntervenant());
        }

        $this->conditionSaisieLibre($queryBuilder, $tikSearch);
        $this->dateFinDebut($queryBuilder, $tikSearch);
        $this->agenceServiceEmetteur($queryBuilder, $tikSearch);
        $this->agenceServiceDebiteur($queryBuilder, $tikSearch);
        $this->conditionCategorie($queryBuilder, $tikSearch);
        
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

    private function conditionSaisieLibre($queryBuilder, $tikSearch)
    {
        //filtre selon le numero ticket
        if(!empty($tikSearch->getNumeroTicket())) {
            $queryBuilder->andWhere('tki.numeroTicket LIKE :numtik')
            ->setParameter('numtik', '%' . $tikSearch->getNumeroTicket() . '%');
        }

        //filtre selon l'utilisateur demandeur
        if(!empty($tikSearch->getDemandeur())) {
            $queryBuilder->andWhere('tki.utilisateurDemandeur LIKE :utilisateur')
            ->setParameter('utilisateur', '%' . $tikSearch->getDemandeur() . '%');
        }

        //filtre selon le numero parc informatique
        if(!empty($tikSearch->getNumParc())) {
            $queryBuilder->andWhere('tki.parcInformatique LIKE :numParc')
            ->setParameter('numParc', '%' . $tikSearch->getNumParc() . '%');
        }
    }

    private function dateFinDebut($queryBuilder, $tikSearch)
    {
         //filtre date debut
        if (!empty($tikSearch->getDateDebut())) {
            $queryBuilder->andWhere('tki.dateCreation >= :dateDebut')
                ->setParameter('dateDebut', $tikSearch->getDateDebut());
        }

        //filtre date fin
        if (!empty($tikSearch->getDateFin())) {
            $queryBuilder->andWhere('tki.dateCreation <= :dateFin')
                ->setParameter('dateFin', $tikSearch->getDateFin());
        }
    }

    private function agenceServiceEmetteur($queryBuilder, $tikSearch)
    {
        //filtre selon l'agence emettteur
        if (!empty($tikSearch->getAgenceEmetteur())) {
            $queryBuilder->andWhere('tki.agenceEmetteurId = :agEmet')
            ->setParameter('agEmet',  $tikSearch->getAgenceEmetteur()->getId());
        }
        //filtre selon le service emetteur
        if (!empty($tikSearch->getServiceEmetteur())) {
            $queryBuilder->andWhere('tki.serviceEmetteurId = :agServEmet')
            ->setParameter('agServEmet', $tikSearch->getServiceEmetteur()->getId());
        }
    }

    private function agenceServiceDebiteur($queryBuilder, $tikSearch)
    {
          //filtre selon l'agence debiteur
        if (!empty($tikSearch->getAgenceDebiteur())) {
            $queryBuilder->andWhere('tki.agenceDebiteurId = :agDebit')
                ->setParameter('agDebit',  $tikSearch->getAgenceDebiteur()->getId() );
        }

        //filtre selon le service debiteur
        if(!empty($tikSearch->getServiceDebiteur())) {
            $queryBuilder->andWhere('tki.serviceDebiteurId = :serviceDebiteur')
            ->setParameter('serviceDebiteur', $tikSearch->getServiceDebiteur()->getId());
        }
    }

    private function conditionCategorie($queryBuilder, $tikSearch)
    {
         //filtre selon la categorie
        if(!empty($tikSearch->getCategorie())) {
            $queryBuilder->andWhere('tki.categorie = :categorieId')
            ->setParameter('categorieId', $tikSearch->getCategorie()->getId());
        }

        //filtre selon la sous categorie
        if(!empty($tikSearch->getSousCategorie())) {
            $queryBuilder->andWhere('tki.sousCategorie = :sousCategorieId')
            ->setParameter('sousCategorieId', $tikSearch->getSousCategorie()->getId());
        }

        //filtre selon la autres categorie
        if(!empty($tikSearch->getAutresCategories())) {
            $queryBuilder->andWhere('tki.autresCategorie = :autresCategorieId')
            ->setParameter('autresCategorieId', $tikSearch->getAutresCategories()->getId());
        }
    }
}

?>