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

    public static function fromArray(array $data): self
    {
        $dto = new self();

        $dto->numDa       = $data['numDa']       ?? null;
        $dto->numCde      = $data['numCde']      ?? null;
        $dto->numLivIps   = $data['numLivIps']   ?? null;
        $dto->numDdp      = $data['numDdp']      ?? null;
        $dto->FactureBl   = $data['FactureBl']   ?? null;
        $dto->fournisseur = $data['fournisseur'] ?? null;
        $dto->numCla      = $data['numCla']      ?? null;
        $dto->aTraiter    = (bool) ($data['aTraiter'] ?? false);

        return $dto;
    }
}
