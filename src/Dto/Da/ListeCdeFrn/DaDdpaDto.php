<?php

namespace App\Dto\Da\ListeCdeFrn;

class DaDdpaDto
{
    public $totalMontantCommande;
    public $ratio = 0;
    public $cumul = 0;
    public $numeroDdp;
    public $dateCreation;
    public $motif;
    public $montant = 0;

    public function getRatio()
    {
        if ($this->totalMontantCommande == 0) {
            return 0;
        }
        return (($this->montant / $this->totalMontantCommande) * 100);
    }
}
