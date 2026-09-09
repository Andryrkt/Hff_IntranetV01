<?php

namespace App\Dto\Dit;

class DitStatusCountDto
{
    public string $statut;
    public int $count;
    public string $cssClass;

    public static function fromRow(array $row): self
    {
        $dto = new self();
        $dto->statut = $row['statut'];
        $dto->count = $row['count'];
        $dto->cssClass = str_replace(' ', '_', strtolower($row['statut']));

        return $dto;
    }
}
