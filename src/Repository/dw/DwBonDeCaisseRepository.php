<?php

namespace App\Repository\dw;

use Doctrine\ORM\EntityRepository;

class DwBonDeCaisseRepository extends EntityRepository
{
    /**
     * Récupère les chemins PDF pour une liste de numéros de bon de caisse
     *
     * @param string[] $numeros
     * @return array Associatif [numeroDemande => cheminPdf]
     */
    public function getCheminsPourNumeros(array $numeros): array
    {
        if (empty($numeros)) return [];

        $qb = $this->createQueryBuilder('b')
            ->select('b.numeroBcs, b.path')
            ->where('b.numeroBcs IN (:numeros)')
            ->andWhere("b.path IS NOT NULL AND b.path != ''")
            ->setParameter('numeros', $numeros);

        $result = $qb->getQuery()->getArrayResult();

        $chemins = [];
        foreach ($result as $row) {
            $chemins[$row['numeroBcs']] = $_ENV['BASE_PATH_FICHIER_COURT'] . '/' . $row['path'];
        }

        return $chemins;
    }
}
