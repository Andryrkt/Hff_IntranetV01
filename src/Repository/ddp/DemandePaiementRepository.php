<?php

namespace App\Repository\ddp;

use Doctrine\ORM\EntityRepository;
use App\Entity\admin\ddp\DdpSearch;
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

    public function findDemandePaiement(DdpSearch $ddpSearch, array $agenceServiceAutorises)
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

        if (!empty($ddpSearch->getAgence())) {
            $qb->andWhere('d.agenceDebiter = :agenceDebiter')
                ->setParameter('agenceDebiter', $ddpSearch->getAgence());
        }
        if (!empty($ddpSearch->getService())) {
            $qb->andWhere('d.serviceDebiter = :serviceDebiter')
                ->setParameter('serviceDebiter', $ddpSearch->getService());
        }
        if (!empty($ddpSearch->getTypeDemande())) {
            $qb->andWhere('d.typeDemandeId = :typeDemandeId')
                ->setParameter('typeDemandeId', $ddpSearch->getTypeDemande()->getId());
        }
        if (!empty($ddpSearch->getNumDdp())) {
            $qb->andWhere('d.numeroDdp = :numeroDdp')
                ->setParameter('numeroDdp', $ddpSearch->getNumDdp());
        }
        if (!empty($ddpSearch->getNumCommande())) {
            $qb->andWhere('d.numeroCommande LIKE :numeroCommande')
                ->setParameter('numeroCommande', '%' . $ddpSearch->getNumCommande() . '%');
        }
        if (!empty($ddpSearch->getNumFacture())) {
            $qb->andWhere('d.numeroFacture LIKE :numeroFacture')
                ->setParameter('numeroFacture', '%' . $ddpSearch->getNumFacture() . '%');
        }

        if (!empty($ddpSearch->getUtilisateur())) {
            $qb->andWhere('d.demandeur = :demandeur')
                ->setParameter('demandeur', $ddpSearch->getUtilisateur());
        }

        if (!empty($ddpSearch->getStatut())) {
            $qb->andWhere('d.statut = :statut')
                ->setParameter('statut', $ddpSearch->getStatut());
        }

        if (!empty($ddpSearch->getDateDebut())) {
            $qb->andWhere('d.dateDemande >= :dateDebut')
                ->setParameter('dateDebut', $ddpSearch->getDateDebut());
        }

        if (!empty($ddpSearch->getDateFin())) {
            $qb->andWhere('d.dateDemande <= :dateFin')
                ->setParameter('dateFin', $ddpSearch->getDateFin());
        }

        if (!empty($ddpSearch->getFournisseur())) {
            $qb->andWhere('d.numeroFournisseur = :numFournisseur')
                ->setParameter('numFournisseur', explode('-', $ddpSearch->getFournisseur())[0]);
        }

        // Condition sur les couples agences-services
        $orX = $qb->expr()->orX();
        foreach ($agenceServiceAutorises as $i => $tab) {
            $orX->add(
                $qb->expr()->andX(
                    $qb->expr()->eq('d.agenceDebiter', ':ag_' . $i),
                    $qb->expr()->eq('d.serviceDebiter', ':serv_' . $i)
                )
            );
            $qb->setParameter('ag_' . $i, $tab['agence_code']);
            $qb->setParameter('serv_' . $i, $tab['service_code']);
        }

        $qb
            ->andWhere($orX)
            ->orderBy('d.dateCreation', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function getnumCde()
    {
        return  $this->createQueryBuilder('d')
            ->select('d.numeroCommande')
            ->getQuery()
            ->getSingleColumnResult()
        ;
    }
}
