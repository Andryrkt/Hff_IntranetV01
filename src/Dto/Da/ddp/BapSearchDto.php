<?php

namespace App\Dto\Da\ddp;

class BapSearchDto
{
    public ?string $numDa = null;
    public ?string $numCde = null;
    public ?string $numLivIps = null;
    public ?string $numDdp = null;
    public ?string $FactureBl = null;
    public ?string $fournisseur = null;
    public ?string $numCla = null;
    public bool $aTraiter = false;

    public function toArray(): array
    {
        return [
            'numDa'       => $this->numDa,
            'numCde'      => $this->numCde,
            'numLivIps'   => $this->numLivIps,
            'numDdp'      => $this->numDdp,
            'FactureBl'   => $this->FactureBl,
            'fournisseur' => $this->fournisseur,
            'numCla'      => $this->numCla,
            'aTraiter'    => $this->aTraiter,
        ];
    }
}
