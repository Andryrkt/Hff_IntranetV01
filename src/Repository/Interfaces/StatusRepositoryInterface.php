<?php

namespace App\Repository\Interfaces;

interface StatusRepositoryInterface
{
    /**
     * Trouve le statut le plus récent pour un identifiant donné.
     * L'identifiant peut être un numéro de devis, un numéro de facture, etc.
     *
     * @param string $identifier L'identifiant unique.
     * @return string|null Le statut sous forme de chaîne, ou null si non trouvé.
     */
    public function findLatestStatusByIdentifier(string $identifier): ?string;
}
