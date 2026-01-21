<?php

namespace App\Dto\Da\ListeCdeFrn;

class DaSoumisionBlReapproDto
{
    public int $numCde;
    public string $numDa;
    public int $numOr;
    public string $pieceJoint1;
    public array $pieceJoint2 = [];
    public ?string $numeroFactureReappro;
    public bool $estFactureReappro = false;
}
