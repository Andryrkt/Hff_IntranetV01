<?php

namespace App\Repository\da;

use Doctrine\ORM\EntityRepository;

class DemandeApproRepository extends EntityRepository 
{
    public function findDaData(array $criteria = [])
    {
        $qb = $this->createQueryBuilder('da')
            ->select('da')
            ->orderBy('da.id', 'DESC');

        // Filtre sur le numero DIT
        if (isset($criteria['numDit'])) {
            $qb->andWhere("da.numeroDemandeDit LIKE :numDit")
                ->setParameter('numDit', '%'. $criteria['numDit'] .'%');
        }

        //filtre sur le numéro de DA
        if (isset($criteria['numDa'])) {
            $qb->andWhere("da.numeroDemandeAppro LIKE :numDa")
                ->setParameter('numDa', '%'. $criteria['numDa'] .'%');
        }

        //filtre sur le demandeur
        if (isset($criteria['demandeur'])) {
            $qb->andWhere("da.demandeur LIKE :demandeur")
                ->setParameter('demandeur', '%'. $criteria['demandeur'] .'%');
        }

        //Filtre sur l'id matériel
        if(isset($criteria['idMateriel'])) {
            $qb->andWhere("da.idMateriel = :idMat")
                ->setParameter('idMat', (int)$criteria['idMateriel']);
        }

        // Filtre sur la date de création
        if (isset($criteria['dateDebutCreation'])) {
            $qb->andWhere("da.dateCreation >= :dateDebut")
                ->setParameter('dateDebut', $criteria['dateDebutCreation']);
        }
        if (isset($criteria['dateFinCreation'])) {
            $qb->andWhere("da.dateCreation <= :dateFin")
                ->setParameter('dateFin', $criteria['dateFinCreation']);
        }

        //filtre sur la date  de fin souhaitée
        if (isset($criteria['dateDebutfinSouhaite'])) {
            $qb->andWhere("da.dateFinSouhaite >= :dateDebut")
                ->setParameter('dateDebut', $criteria['dateDebutfinSouhaite']);
        }
        if (isset($criteria['dateFinFinSouhaite'])) {
            $qb->andWhere("da.dateFinSouhaite <= :dateFin")
                ->setParameter('dateFin', $criteria['dateFinFinSouhaite']);
        }

        // Filtre sur l'agence Emetteur
        if (isset($criteria['agenceEmetteur'])) {
            $qb->andWhere("da.agenceEmetteur = :agenceEmetteur")
                ->setParameter('agenceEmetteur', $criteria['agenceEmetteur']->getId());
        }

        // Filtre sur le service Emetteur
        if (isset($criteria['serviceEmetteur'])) {
            $qb->andWhere("da.serviceEmetteur = :serviceEmetteur")
                ->setParameter('serviceEmetteur', $criteria['serviceEmetteur']->getId());
        }

        //Filtre sur l'agence destinataire
        if (isset($criteria['agenceDestinataire'])) {
            $qb->andWhere("da.agenceDestinataire = :agenceDestinataire")
                ->setParameter('agenceDestinataire', $criteria['agenceDestinataire']->getId());
        }

        // Filtre sur le service destinataire
        if (isset($criteria['serviceDestinataire'])) {
            $qb->andWhere("da.serviceDestinataire = :serviceDestinataire")
                ->setParameter('serviceDestinataire', $criteria['serviceDestinataire']->getId());
        }
        
        return $qb->getQuery()->getResult();
    }
}
