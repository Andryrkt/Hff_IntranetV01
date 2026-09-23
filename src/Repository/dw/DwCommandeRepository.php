<?php

namespace App\Repository\dw;

use Doctrine\ORM\EntityRepository;

class DwCommandeRepository extends EntityRepository
{
    public function findNumCdeDw(): array
    {
        return $this->createQueryBuilder('c')
            ->select('DISTINCT c.numeroCde as numcde')
            ->where('c.path IS NOT NULL')
            ->orderBy('c.numeroCde', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function findPathByNumeroCde(string $numeroCde): array
    {
        // Sous-requête pour la date max
        $subQuery = $this->createQueryBuilder('c2')
            ->select('MAX(c2.dateCreation)')
            ->where('c2.numeroCde = :numeroCde');

        // Requête principale
        return $this->createQueryBuilder('c')
            ->select('DISTINCT c.path, c.numeroCde')
            ->where('c.numeroCde = :numeroCde')
            ->andWhere('c.dateCreation = (' . $subQuery->getDQL() . ')')
            ->setParameter('numeroCde', $numeroCde)
            ->getQuery()
            ->getResult();
    }

    /**
     * Équivalent batché de findPathByNumeroCde() : récupère en une seule requête
     * le dernier fichier (path) de chaque numéro de commande demandé, au lieu
     * d'exécuter une requête séparée par commande.
     *
     * @param string[] $numeroCdes
     */
    public function findPathsByNumeroCdes(array $numeroCdes): array
    {
        if (empty($numeroCdes)) {
            return [];
        }

        $subQuery = $this->createQueryBuilder('c2')
            ->select('MAX(c2.dateCreation)')
            ->where('c2.numeroCde = c.numeroCde');

        return $this->createQueryBuilder('c')
            ->select('DISTINCT c.path, c.numeroCde')
            ->where('c.numeroCde IN (:numeroCdes)')
            ->andWhere('c.dateCreation = (' . $subQuery->getDQL() . ')')
            ->setParameter('numeroCdes', $numeroCdes)
            ->getQuery()
            ->getResult();
    }
}
