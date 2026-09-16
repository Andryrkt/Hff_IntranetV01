<?php

namespace App\Dto\Dit;

class DitCommandeDto
{
    public ?string $numero        = null;
    public ?string $dateFormatee  = null;
    public ?string $statut        = null;

    public static function fromRow(array $row): self
    {
        $dto = new self();
        $dto->numero       = $row['slor_numcf'] ?? null;
        $dto->dateFormatee = !empty($row['fcde_date']) ? (new \DateTime($row['fcde_date']))->format('d/m/Y') : null;
        $dto->statut       = '--';

        return $dto;
    }
}
