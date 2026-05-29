<?php

namespace App\Dto\Da\ListeCdeFrn;

use DateTime;

class DaDdpaDto
{
    public ?float $totalMontantCommande = 0.00;
    public float $ratio = 0.00;
    public float $cumul = 0.00;
    public ?string $numeroDdp = null;
    public ?DateTime $dateCreation = null;
    public ?string $motif = null;
    public float $montant = 0.00;
    public ?string $statut = null;

    public function getRatio()
    {
        if ($this->totalMontantCommande == 0) {
            return 0;
        }
        return (($this->montant / $this->totalMontantCommande) * 100);
    }
}
