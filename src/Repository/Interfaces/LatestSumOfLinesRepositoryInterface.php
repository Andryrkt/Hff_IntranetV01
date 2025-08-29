<?php

namespace App\Repository\Interfaces;

interface LatestSumOfLinesRepositoryInterface
{
    /**
     * Trouve la somme des lignes la plus récente pour un identifiant donné.
     * L'identifiant peut être un numéro de devis, un numéro de facture, etc.
     *
     * @param string $identifier L'identifiant unique.
     * @return int|null La somme des lignes sous forme d'entier, ou null si non trouvé.
     */
    public function findLatestSumOfLinesByIdentifier(string $identifier): ?int;
}