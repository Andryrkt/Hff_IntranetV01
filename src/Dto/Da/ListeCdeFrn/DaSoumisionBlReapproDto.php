<?php

namespace App\Dto\Da\ListeCdeFrn;

class DaSoumisionBlReapproDto
{
    public ?string $numCde;
    public string $numDa;
    public string $numOr;
    public string $pieceJoint1;
    public array $pieceJoint2 = [];
    public ?string $numeroFactureReappro;
    public bool $estFactureReappro = false;
}
