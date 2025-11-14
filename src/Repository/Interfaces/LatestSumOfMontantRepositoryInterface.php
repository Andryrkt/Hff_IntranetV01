<?php

namespace App\Repository\Interfaces;

/**
 * Interface pour les repositories qui peuvent récupérer le montant total le plus récent
 * d'une entité par son identifiant
 */
interface LatestSumOfMontantRepositoryInterface
{
    /**
     * Trouve le montant total le plus récent d'une entité par son identifiant
     * 
     * @param string $identifier L'identifiant de l'entité (ex: numéro de devis)
     * @return float|null Le montant total le plus récent ou null si aucun montant trouvé
     */
    public function findLatestSumOfMontantByIdentifier(string $identifier): ?float;
}
