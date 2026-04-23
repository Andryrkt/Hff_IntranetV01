<?php

namespace App\Repository\ddp;

use App\Constants\ddp\StatutConstants;
use App\Dto\Da\ddp\BapSearchDto;
use App\Entity\admin\utilisateur\User;
use App\Service\TableauEnStringService;
use Doctrine\ORM\EntityRepository;

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

    public function findDemandePaiement($criteria, bool $estFinance)
    {
        $qb = $this->createQueryBuilder('d')
            ->join(User::class, 'u', 'WITH', 'd.demandeur = u.nom_utilisateur');

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
        if ($estFinance) {
            $qb->andWhere('d.statut NOT IN (:statutPourDA)')
                ->setParameter('statutPourDA', StatutConstants::STATUT_A_TRANSMETTRE);
        }

        if (!empty($criteria->debiteur['agence'])) {
            $qb->andWhere('d.agenceDebiter = :agenceDebiter')
                ->setParameter('agenceDebiter', $criteria->debiteur['agence']->getCodeAgence());
        }

        if (!empty($criteria->debiteur['service'])) {
            $qb->andWhere('d.serviceDebiter = :serviceDebiter')
                ->setParameter('serviceDebiter', $criteria->debiteur['service']->getCodeService());
        }

        if (!empty($criteria->typeDemande)) {
            $qb->andWhere('d.typeDemandeId = :typeDemandeId')
                ->setParameter('typeDemandeId', $criteria->typeDemande->getId());
        }

        if (!empty($criteria->numDdp)) {
            $qb->andWhere('d.numeroDdp = :numDdp')
                ->setParameter('numDdp', $criteria->numDdp);
        }

        if (!empty($criteria->numCommande)) {
            $qb->andWhere('d.numeroCommande LIKE :numeroCommande')
                ->setParameter('numeroCommande', '%' . $criteria->numCommande() . '%');
        }

        if (!empty($criteria->numFacture)) {
            $qb->andWhere('d.numeroFacture LIKE :numeroFacture')
                ->setParameter('numeroFacture', '%' . $criteria->numFacture() . '%');
        }

        if (!empty($criteria->utilisateur)) {
            $qb->andWhere('d.demandeur = :demandeur')
                ->setParameter('demandeur', $criteria->utilisateur);
        }

        if (!empty($criteria->statut)) {
            $qb->andWhere('d.statut = :statut')
                ->setParameter('statut', $criteria->statut);
        }

        if (!empty($criteria->dateCreation['debut'])) {
            $qb->andWhere('d.dateCreation >= :dateDebut')
                ->setParameter('dateDebut', $criteria->dateCreation['debut']);
        }

        if (!empty($criteria->dateCreation['fin'])) {
            $qb->andWhere('d.dateCreation <= :dateFin')
                ->setParameter('dateFin', $criteria->dateCreation['fin']);
        }

        if (!empty($criteria->fournisseur)) {
            $qb->andWhere('d.numeroFournisseur = :numFournisseur')
                ->setParameter('numFournisseur', explode('-', $criteria->fournisseur)[0]);
        }

        $qb->orderBy('d.dateCreation', 'DESC');

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

    public function getDdpSelonNumCde($numCde)
    {
        $queryBuilder =  $this->createQueryBuilder('d')
            ->where('d.numeroCommande = :numero')
            // ->andWhere('d.statut LIKE :statut')
            ->setParameter('numero', $numCde)
            // ->setParameter('statut', '%Validé%')
            ->orderBy('d.numeroDdp', 'ASC');


        return $queryBuilder->getQuery()
            ->getResult()
        ;
    }

    public function getStatutDdpSelonNumCde(string $numCde): array
    {
        $queryBuilder =  $this->createQueryBuilder('d')
            ->select('d.statut')
            ->where('d.numeroCommande = :numero')
            ->setParameter('numero', $numCde)
            ->orderBy('d.numeroDdp', 'ASC');


        return $queryBuilder->getQuery()
            ->getSingleColumnResult()
        ;
    }

    public function getDernierNumeroSoumissionDdpDa(string $numCde, string $numeroDa): ?string
    {
        try {
            return $this->createQueryBuilder('d')
                ->select('d.numeroSoumissionDdpDa')
                ->where('d.numeroDemandeAppro = :numeroDa')
                ->andWhere('d.numeroCommande LIKE :numero')
                ->setParameter('numeroDa', $numeroDa)
                ->setParameter('numero', '%' . $numCde . '%')
                ->orderBy('d.numeroSoumissionDdpDa', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        } catch (\Doctrine\ORM\NonUniqueResultException $e) {
            return null;
        }
    }

    public function getDernierStatutDddp($numeroCde, $numeroDa)
    {
        $queryBuilder =  $this->createQueryBuilder('d')
            ->select('d.statut')
            ->where('d.numeroDemandeAppro = :numeroDa')
            ->andWhere('d.numeroCommande = :numero')
            ->setParameter('numeroDa', $numeroDa)
            ->setParameter('numero', $numeroCde)
            ->orderBy('d.dateModification', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();


        return $queryBuilder ? $queryBuilder['statut'] : null;
    }

    public function findByConsultationFactureCriteria(BapSearchDto $criteria)
    {
        $qb = $this->createQueryBuilder('d')
            ->leftJoin('d.commandeLivraisons', 'cl')
            ->addSelect('cl')
            ->where('d.appro = :appro')
            ->setParameter('appro', true);

        if (!empty($criteria->numDa)) {
            $qb->andWhere('d.numeroDemandeAppro = :numeroDa')
                ->setParameter('numeroDa', $criteria->numDa);
        }
        if (!empty($criteria->numDdp)) {
            $qb->andWhere('d.numeroDdp = :numeroDdp')
                ->setParameter('numeroDdp', $criteria->numDdp);
        }

        if (!empty($criteria->numCde)) {
            $qb->andWhere('d.numeroCommande = :numeroCommande')
                ->setParameter('numeroCommande', $criteria->numCde);
        }

        if (!empty($criteria->FactureBl)) {
            $qb->andWhere('d.numeroFacture = :numeroFacture')
                ->setParameter('numeroFacture', $criteria->FactureBl);
        }

        if (!empty($criteria->numLivIps)) {
            $qb->andWhere('cl.numeroLivraison = :numeroLivraisonIps')
                ->setParameter('numeroLivraisonIps', $criteria->numLivIps);
        }

        if (!empty($criteria->fournisseur)) {
            $qb->andWhere('CONCAT(d.numeroFournisseur, \' - \', d.beneficiaire) = :fournisseur')
                ->setParameter('fournisseur', $criteria->fournisseur);
        }


        $qb->orderBy('d.dateCreation', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function findDdpByNumeroDdp(array $numeroDdp): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.numeroDdp IN (:numeroDdp)')
            ->setParameter('numeroDdp', $numeroDdp)
            ->getQuery()
            ->getResult();
    }
}
