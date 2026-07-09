<?php

namespace App\Repository\dw;

use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;

class DwBcApproRepository extends EntityRepository
{
    /** 
     * Récupère le chemin du document associé à un numéro de bon de commande (BC).
     * @param string $numeroCde Le numéro de bon de commande pour lequel on souhaite récupérer le chemin.
     * @return string|null Le chemin du document associé au numéro de bon de commande, ou
     */
    public function getPath(string $numeroCde): ?string
    {
        return  $this->createQueryBuilder('d')
            ->select('d.path')
            ->where('d.numeroBc = :numeroBc')
            ->setParameter('numeroBc', $numeroCde)
            ->orderBy('d.path', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);
    }

    /** 
     * Récupère le chemin du document associé à un numéro de demande Appro.
     * @param string $numeroDa Le numéro de demande appro lequel on souhaite récupérer le chemin.
     */
    public function getPathAndNumeroBCByNumDa(string $numeroDa)
    {
        return  $this->createQueryBuilder('d')
            ->select('d.path', 'd.numeroBc')
            ->where('d.numeroDa = :numeroDa')
            ->setParameter('numeroDa', $numeroDa)
            ->orderBy('d.numeroBc', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getInfoValidationBC(string $numeroBc)
    {
        return $this->createQueryBuilder('d')
            ->select('d.validateur', 'd.dateValidation')
            ->where('d.numeroBc = :numeroBc')
            ->setParameter('numeroBc', $numeroBc)
            ->orderBy('d.numeroVersion', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findValidationInfosForBcs(array $numerosBc): array
    {
        if (empty($numerosBc)) return [];

        // Sous-requête : dernière version pour chaque numeroBc
        $subQuery = $this->createQueryBuilder('d2')
            ->select('MAX(d2.numeroVersion)')
            ->where('d2.numeroBc = d.numeroBc')
            ->getDQL();

        $results = $this->createQueryBuilder('d')
            ->select('d.numeroBc', 'd.validateur', 'd.dateValidation')
            ->where('d.numeroBc IN (:numerosBc)')
            ->andWhere("d.numeroVersion = ({$subQuery})")
            ->setParameter('numerosBc', $numerosBc)
            ->getQuery()
            ->getResult();

        // Indexer par numeroBc pour un accès facile
        $indexed = [];
        foreach ($results as $row) {
            $indexed[$row['numeroBc']] = [
                'validateur'     => $row['validateur'],
                'dateValidation' => $row['dateValidation'],
            ];
        }

        // Construire le résultat final en garantissant une entrée par BC demandé
        $final = [];
        foreach ($numerosBc as $numeroBc) {
            $final[$numeroBc] = $indexed[$numeroBc] ?? null; // null = pas trouvé en BDD
        }

        return $final;
    }

    /**
     * Récupération du date de validation commande
     *
     * @param string $numeroBc
     * @return \DateTimeInterface|null
     */
    public function getDateValidationBC(string $numeroBc): ?\DateTimeInterface
    {
        $result = $this->createQueryBuilder('d')
            ->select('d.dateValidation')
            ->where('d.numeroBc = :numeroBc')
            ->setParameter('numeroBc', $numeroBc)
            ->orderBy('d.numeroVersion', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(Query::HYDRATE_SINGLE_SCALAR);

        return $result ? new \DateTime($result) : null;
    }
}
