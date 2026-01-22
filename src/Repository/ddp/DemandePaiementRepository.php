<?php

namespace App\Repository\ddp;

use Doctrine\ORM\EntityRepository;
use App\Service\TableauEnStringService;

class DemandePaiementRepository extends EntityRepository
{
    public function CompteNbrligne($numerofournisseur)
    {
        $nbrLigne = $this->createQueryBuilder('ddp')
            ->select('COUNT(ddp.numeroFournisseur)')
            ->where('ddp.numeroFournisseur = :numFrn')
            ->andWhere('ddp.statut != :statut')
            ->setParameters([
                'numFrn' => $numerofournisseur,
                'statut' => 'Annulé'
            ])
            ->getQuery()
            ->getSingleScalarResult();;

        return $nbrLigne ? $nbrLigne : 0;
    }

    public function recuperation_numFrs_numCde($numeroDdp)
    {
        $data = $this->createQueryBuilder('ddp')
            ->select('ddp.numeroFournisseur, ddp.numeroCommande')
            ->where('ddp.numeroDdp = :numDdp')
            ->setParameters([
                'numDdp' => $numeroDdp
            ])
            ->getQuery()
            ->getOneOrNullResult();

        if ($data) {
            return [
                'numeroFournisseur' => $data['numeroFournisseur'],
                'numeroCommande' => is_array($data['numeroCommande'])
                    ? TableauEnStringService::TableauEnString(",", $data['numeroCommande'])
                    : $data['numeroCommande']
            ];
        }

        return null;
        return $data;
    }

    public function findNumeroVersionMax(string $numDdp)
    {
        $numeroVersionMax = $this->createQueryBuilder('Ddp')
            ->select('MAX(Ddp.numeroVersion)')
            ->where('Ddp.numeroDdp = :numDdp')
            ->setParameter('numDdp', $numDdp)
            ->getQuery()
            ->getSingleScalarResult();

        return $numeroVersionMax;
    }

    public function findDemandePaiement($criteria)
    {
        $qb = $this->createQueryBuilder('d');

        // Sous-requête imbriquée dans la clause WHERE
        $qb->where(
            'd.numeroVersion = (
                SELECT MAX(dp2.numeroVersion)
                FROM App\Entity\ddp\DemandePaiement dp2
                WHERE dp2.numeroDdp = d.numeroDdp
                AND dp2.agenceDebiter = d.agenceDebiter
                AND dp2.serviceDebiter = d.serviceDebiter
            )'
        );
        if (!empty($criteria->getAgence())) {
            $qb->andWhere('d.agenceDebiter = :agenceDebiter')
                ->setParameter('agenceDebiter', $criteria->getAgence()->getCodeAgence());
        }
        if (!empty($criteria->getService())) {
            $qb->andWhere('d.serviceDebiter = :serviceDebiter')
                ->setParameter('serviceDebiter', $criteria->getService()->getCodeService());
        }
        if (!empty($criteria->getTypeDemande())) {
            $qb->andWhere('d.typeDemandeId = :typeDemandeId')
                ->setParameter('typeDemandeId', $criteria->getTypeDemande()->getId());
        }
        if (!empty($criteria->getNumDdp())) {
            $qb->andWhere('d.numeroDdp = :numeroDdp')
                ->setParameter('numeroDdp', $criteria->getNumDdp());
        }
        if (!empty($criteria->getNumCommande())) {
            $qb->andWhere('d.numeroCommande LIKE :numeroCommande')
                ->setParameter('numeroCommande', '%' . $criteria->getNumCommande() . '%');
        }
        if (!empty($criteria->getNumFacture())) {
            $qb->andWhere('d.numeroFacture LIKE :numeroFacture')
                ->setParameter('numeroFacture', '%' . $criteria->getNumFacture() . '%');
        }

        if (!empty($criteria->getUtilisateur())) {
            $qb->andWhere('d.demandeur = :demandeur')
                ->setParameter('demandeur', $criteria->getUtilisateur());
        }

        if (!empty($criteria->getStatut())) {
            $qb->andWhere('d.statut = :statut')
                ->setParameter('statut', $criteria->getStatut());
        }

        if (!empty($criteria->getDateDebut())) {
            $qb->andWhere('d.dateDemande >= :dateDebut')
                ->setParameter('dateDebut', $criteria->getDateDebut());
        }

        if (!empty($criteria->getDateFin())) {
            $qb->andWhere('d.dateDemande <= :dateFin')
                ->setParameter('dateFin', $criteria->getDateFin());
        }

        if (!empty($criteria->getFournisseur())) {
            $qb->andWhere('d.numeroFournisseur = :numFournisseur')
                ->setParameter('numFournisseur', explode('-', $criteria->getFournisseur())[0]);
        }

        // Tri
        $qb->orderBy('d.dateCreation', 'DESC');
        // $query = $qb->getQuery();
        //         $sql = $query->getSQL();
        //         $params = $query->getParameters();

        //         dump("SQL : " . $sql . "\n");
        //         foreach ($params as $param) {
        //             dump($param->getName());
        //             dump($param->getValue());
        //         }
        //         die();
        return $qb->getQuery()->getResult();
    }

    // public function getnumFacture()
    // {
    //     return  $this->createQueryBuilder('d')
    //             ->select('d.numeroFacture')
    //             ->getQuery()
    //             ->getSingleColumnResult()
    //             ;
    // }

    public function getnumCde()
    {
        return  $this->createQueryBuilder('d')
            ->select('d.numeroCommande')
            ->getQuery()
            ->getSingleColumnResult()
        ;
    }

    public function getMontantDejaPayer(string $numeroCommande)
    {
        $resultat = $this->createQueryBuilder('d')
            ->select('SUM(d.montantAPayers)')
            ->where('d.statut = :statut')
            ->setParameter('statut', 'Validé')
            // Cherche le numéro dans le JSON (format: ["num1", "num2"])
            ->andWhere('d.numeroCommande LIKE :numero')
            ->setParameter('numero', '%"' . $numeroCommande . '"%')
            ->getQuery()
            ->getSingleScalarResult();

        return $resultat ?? 0;
    }
}
