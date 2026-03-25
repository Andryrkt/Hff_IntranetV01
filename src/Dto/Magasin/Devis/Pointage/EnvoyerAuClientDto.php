<?php

namespace App\Dto\Magasin\Devis\Pointage;

class EnvoyerAuClientDto
{
    public string $numeroDevis;
    public ?\DateTimeInterface $dateEnvoiDevisAuClient = null;
}
