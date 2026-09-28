<?php

namespace App\Dto\Magasin\cdeFrn;

class CommandeSoumissionLigneDTO
{
    public ?string $numLine        = null;
    public ?string $const          = null;
    public ?string $avBat          = null;
    public ?string $ref            = null;
    public ?string $packQty        = null;
    public ?string $designation    = null;
    public ?string $npr            = null;
    public ?string $fms            = null;
    public ?string $ret            = null;
    public ?int    $qteDem         = null;
    public ?int    $qteDispo       = null;
    public ?int    $qteDispoMin    = null;
    public ?int    $qteDispoMax    = null;
    public ?int    $qteVteDer6Mois = null;
    public ?int    $nbrVteDer6Mois = null;
    public ?float  $prixUnitaire   = null;
    public ?float  $prixTotal      = null;
    public ?float  $poids          = null;

    /** @var list<CommandeSoumissionDetailDTO> */
    public array   $details        = [];

    public function getPrixUnitaire(): string
    {
        if ($this->prixUnitaire === null) return "";

        return number_format($this->prixUnitaire, 2, ',', ' ') . " Ar";
    }

    public function getPrixTotal(): string
    {
        if ($this->prixTotal === null) return "";

        return number_format($this->prixTotal, 2, ',', ' ') . " Ar";
    }

    public function getPoids(): string
    {
        if ($this->poids === null) return "";

        return number_format($this->poids, 2, ',', ' ');
    }
}
