<?php

namespace App\Repository\da;

use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;

class DaSoumissionFacBlRepository extends EntityRepository
{

    public function getNumeroVersionMax(string $numeroCde, string $codeSociete): ?int
    {
        $result = $this->createQueryBuilder('dabc')
            ->select('MAX(dabc.numeroVersion)')
            ->where('dabc.numeroCde = :numCde')
            ->andWhere('dabc.codeSociete = :codeSociete')
            ->setParameter('numCde', $numeroCde)
            ->setParameter('codeSociete', $codeSociete)
            ->getQuery()
            ->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);

        return $result !== null ? (int) $result : null;
    }

    public function getStatut(?string $numCde): ?string
    {
        // Étape 1 : Récupérer le numeroVersion maximum
        $numeroVersionMax = $this->createQueryBuilder('dabc')
            ->select('MAX(dabc.numeroVersion)')
            ->where('dabc.numeroCde = :numCde')
            ->setParameter('numCde', $numCde)
            ->getQuery()
            ->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);

        if ($numeroVersionMax === null) {
            return null; // ou une valeur par défaut, selon vos besoins
        }

        // Étape 2 : Récupérer le statut correspondant
        $statut = $this->createQueryBuilder('dabc')
            ->select('dabc.statut')
            ->where('dabc.numeroCde = :numCde')
            ->andWhere('dabc.numeroVersion = :numVersion')
            ->setParameters([
                'numCde' => $numCde,
                'numVersion' => $numeroVersionMax
            ])
            ->getQuery()
            ->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);

        return $statut;
    }

    public function getAllLivraisonSoumis(string $numDa, string $numCde, string $codeSociete)
    {
        return array_filter($this->createQueryBuilder('dabc')
            ->select('dabc.numLiv')
            ->where('dabc.numeroDemandeAppro = :numDa')
            ->andWhere('dabc.numeroCde = :numCde')
            ->andWhere('dabc.codeSociete = :codeSociete')
            ->setParameter('numDa', $numDa)
            ->setParameter('numCde', $numCde)
            ->setParameter('codeSociete', $codeSociete)
            ->getQuery()
            ->getSingleColumnResult());
    }

    public function getAll(array $criteria = [], string $codeSociete)
    {
        $qb = $this->createQueryBuilder('dabc')
            ->andWhere('dabc.codeSociete = :codeSociete')
            ->setParameter('codeSociete', $codeSociete);

        // filtres par le numero demande appro
        if (isset($criteria['numDa']) && !empty($criteria['numDa'])) {
            $qb->andWhere('dabc.numeroDemandeAppro = :numDa')
                ->setParameter('numDa', $criteria['numDa']);
        }

        // filtres par le numero commande
        if (isset($criteria['numCde']) && !empty($criteria['numCde'])) {
            $qb->andWhere('dabc.numeroCde = :numCde')
                ->setParameter('numCde', $criteria['numCde']);
        }

        // filtres par le numero livraison IPS
        if (isset($criteria['numLivIps']) && !empty($criteria['numLivIps'])) {
            $qb->andWhere('dabc.numLiv = :numLivIps')
                ->setParameter('numLivIps', $criteria['numLivIps']);
        }

        // filtres par le numero demande de paiement
        if (isset($criteria['numDdp']) && !empty($criteria['numDdp'])) {
            $qb->andWhere('dabc.numeroDemandePaiement = :numDdp')
                ->setParameter('numDdp', $criteria['numDdp']);
        }

        // filtres par la facture ou le bon de livraison
        if (isset($criteria['FactureBl']) && !empty($criteria['FactureBl'])) {
            $qb->andWhere('dabc.refBlFac = :facBl')
                ->setParameter('facBl', $criteria['FactureBl']);
        }

        // filtres par le numéro fournisseur
        if (isset($criteria['fournisseur']) && !empty($criteria['fournisseur'])) {
            $qb->andWhere('dabc.numeroFournisseur = :fournisseur')
                ->setParameter('fournisseur', trim(explode('-', $criteria['fournisseur'])[0]));
        }

        return $qb->orderBy('dabc.id', 'DESC')->getQuery()->getResult();
    }

    public function getAllSelonNumBap(array $bapNumbers)
    {
        return  $this->createQueryBuilder('dabc')
            ->where('dabc.numeroBap IN (:numBap)')
            ->setParameter('numBap', $bapNumbers)
            ->getQuery()
            ->getResult();
    }
    /**
     * Récupération du date de livraison commande
     *
     * @param string $numeroBc
     * @return \DateTimeInterface|null
     */
    public function getDateLivraisonArticle(string $numeroBc): ?\DateTimeInterface
    {
        $result = $this->createQueryBuilder('d')
            ->select('d.dateCreation')
            ->where('d.numeroCde = :numeroBc')
            ->andWhere('d.numeroVersion = :firstVersion')
            ->setParameters([
                'numeroBc' => $numeroBc,
                'firstVersion' => 1
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);

        return $result ? new \DateTime($result) : null;
    }
}
