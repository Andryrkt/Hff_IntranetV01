<?php

namespace App\Dto\Magasin\Devis\Pointage;

class EnvoyerAuClientDto
{
    public string $numeroDevis;
    public string $codeSociete;
    public ?\DateTimeInterface $dateEnvoiDevisAuClient = null;

    public $statutDw;
    public $statutBc;
    public $numeroVersion;
    public $datePointage;
}
