<?php

namespace App\Repository\ddp;

use App\Constants\ddp\StatutConstants;
use App\Constants\ddp\TypeDemandePaiementConstants;
use App\Dto\Da\ddp\BapSearchDto;
use App\Entity\admin\utilisateur\User;
use App\Entity\ddp\DemandePaiement;
use App\Service\TableauEnStringService;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

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

    public function getDdpSelonNumCde(string $numCde)
    {
        $queryBuilder =  $this->createQueryBuilder('d')
            ->where('d.numeroCommande = :numero')
            ->andWhere('d.statut NOT IN (:statut)')
            ->setParameter('numero', (string) $numCde)
            ->setParameter('statut', StatutConstants::REFUSES_DDP)
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
            ->setParameter('numero', (string) $numCde)
            ->setMaxResults(1)
            ->orderBy('d.numeroDdp', 'DESC');


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

    public function getDernierStatutDddp(string $numeroCde, string $numeroDa)
    {
        $queryBuilder =  $this->createQueryBuilder('d')
            ->select('d.statut')
            ->where('d.numeroDemandeAppro = :numeroDa')
            ->andWhere('d.numeroCommande = :numero')
            ->setParameter('numeroDa', $numeroDa)
            ->setParameter('numero', (string) $numeroCde)
            ->orderBy('d.dateModification', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();


        return $queryBuilder ? $queryBuilder['statut'] : null;
    }

    public function findByConsultationFactureCriteria(BapSearchDto $criteria, bool $appro)
    {
        $qb = $this->createQueryBuilder('d')
            ->leftJoin('d.commandeLivraisons', 'cl')
            ->addSelect('cl')
            ->where('d.appro = :appro')
            ->setParameter('appro', $appro);

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
                ->setParameter('numeroCommande', (string) $criteria->numCde);
        }

        if (!empty($criteria->numCla)) {
            $qb->andWhere('d.numeroCla = :numeroCla')
                ->setParameter('numeroCla', $criteria->numCla);
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

        if (!empty($criteria->aTraiter)) {
            $qb->andWhere('d.statut IN (:statut)')
                ->setParameter('statut', StatutConstants::STATUT_A_TRANSMETTRE);
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

    public function getSommeMontantDdpaValide(string $numeroCde, string $codeSociete)
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->select('SUM(d.montantAPayers)')
            ->where('d.typeDemandeId = :typeDdp')
            ->andWhere('d.numeroCommande = :numCde')
            ->andWhere('d.statut in (:statut)')
            ->andWhere('d.codeSociete = :codeSociete')
            ->setParameters([
                'typeDdp' => 1,
                'numCde' => $numeroCde,
                'statut' => [StatutConstants::VALIDE, StatutConstants::TRANSMIS_COMPTA],
                'codeSociete' => $codeSociete
            ]);

        return $queryBuilder->getQuery()->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);
    }

    /**
     * Somme des montants des factures déjà payées pour une commande, c'est-à-dire
     * des demandes de paiement "après arrivage" validées ou transmises en compta
     * (les paiements à l'avance sont sommés à part par getSommeMontantDdpaValide()).
     */
    public function getMontantFactureDejaPayer(string $numeroCde, string $codeSociete): ?float
    {
        $result = $this->createQueryBuilder('d')
            ->select('SUM(d.montantAPayers)')
            ->Where('d.numeroCommande = :numCde')
            ->andWhere('d.statut in (:statut)')
            ->andWhere('d.codeSociete = :codeSociete')
            ->setParameters([
                'numCde' => $numeroCde,
                'statut' => [StatutConstants::VALIDE, StatutConstants::TRANSMIS_COMPTA],
                'codeSociete' => $codeSociete
            ])
            ->getQuery()
            ->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);

        return $result !== null ? (float) $result : null;
    }

    /**
     * Somme des montants des demandes de paiement validées (validé ou transmis
     * compta) pour une commande, à l'avance comme après arrivage. Seule la
     * dernière version de chaque demande est prise en compte.
     */
    /**
     * Somme des montants des demandes de paiement validées d'une commande
     * (dernière version de chaque DDP), éventuellement limitée à un type de demande.
     */
    public function getSommeMontantDdpValideParCommande(string $numeroCde, string $codeSociete, ?int $typeDemandeId = null): float
    {
        $result = $this->ddpValideParCommandeQueryBuilder($numeroCde, $codeSociete, $typeDemandeId)
            ->select('SUM(d.montantAPayers)')
            ->getQuery()
            ->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);

        return $result !== null ? (float) $result : 0.0;
    }

    /**
     * Cumul des pourcentages (montant à payer / montant total du commande) des
     * demandes de paiement validées d'une commande.
     */
    public function getCumulRatioDdpValideParCommande(string $numeroCde, string $codeSociete, ?int $typeDemandeId = null): float
    {
        $cumul = 0.0;
        /** @var DemandePaiement $ddp */
        foreach ($this->ddpValideParCommandeQueryBuilder($numeroCde, $codeSociete, $typeDemandeId)->getQuery()->getResult() as $ddp) {
            $cumul += $ddp->getRatioMontantTotalCommande();
        }

        return $cumul;
    }

    /**
     * Demandes de paiement validées (dernière version) d'une commande
     */
    private function ddpValideParCommandeQueryBuilder(string $numeroCde, string $codeSociete, ?int $typeDemandeId)
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->where('d.numeroCommande = :numCde')
            ->andWhere('d.statut IN (:statutsValides)')
            ->andWhere('d.codeSociete = :codeSociete')
            ->andWhere('d.numeroVersion = (
                SELECT MAX(d2.numeroVersion) FROM ' . DemandePaiement::class . ' d2
                WHERE d2.numeroDdp = d.numeroDdp
            )')
            ->setParameters([
                'numCde'         => $numeroCde,
                'statutsValides' => [StatutConstants::VALIDE, StatutConstants::TRANSMIS_COMPTA],
                'codeSociete'    => $codeSociete,
            ]);

        if ($typeDemandeId !== null) {
            $queryBuilder
                ->andWhere('IDENTITY(d.typeDemandeId) = :typeDemandeId')
                ->setParameter('typeDemandeId', $typeDemandeId);
        }

        return $queryBuilder;
    }

    public function getSommeMontantValide(string $numeroCde, string $codeSociete)
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->select('SUM(d.montantAPayers)')
            ->andWhere('d.numeroCommande = :numCde')
            ->andWhere('d.statut in (:statut)')
            ->andWhere('d.codeSociete = :codeSociete')
            ->setParameters([
                'numCde' => $numeroCde,
                'statut' => [StatutConstants::VALIDE, StatutConstants::TRANSMIS_COMPTA],
                'codeSociete' => $codeSociete
            ]);

        $result = $queryBuilder->getQuery()
            ->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);;

        return $result !== null ? (float) $result : null;
    }
}
