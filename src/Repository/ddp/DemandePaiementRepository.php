<?php

namespace App\Repository\ddp;

use App\Constants\ddp\StatutConstants;
use App\Dto\Da\ddp\BapSearchDto;
use App\Entity\admin\utilisateur\User;
use App\Entity\da\DaSoumissionFacBl;
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

    public function findDemandePaiement($criteria, User $user)
    {
        $qb = $this->createQueryBuilder('d')
            ->leftJoin(DaSoumissionFacBl::class, 'bap', 'WITH',  'bap.numeroDemandePaiement = d.numeroDdp')
            ->addSelect('bap.numeroBap');
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
        if ($user->getCodeAgenceUser() === '80' && $user->getCodeServiceUser() === 'FIN') {
            $qb->andWhere('d.statut NOT IN (:statutPourDA)')
                ->setParameter('statutPourDa', StatutConstants::STATUT_A_TRANSMETTRE)
            ;
        }
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
