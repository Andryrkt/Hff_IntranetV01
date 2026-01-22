<?php

namespace App\Mapper\ddp;

use App\Dto\ddp\DemandePaiementDto;
use App\Entity\ddp\DemandePaiementLigne;

class DemandePaiementLigneMapper
{
    public static function map(DemandePaiementDto $dto)
    {
        $ddpls = [];

        for ($i = 0; $i < count($dto->numeroCommande); $i++) {
            $ddpl = new DemandePaiementLigne();
            $ddpl->setNumeroDdp($dto->numeroDdp)
                ->setNumeroLigne($i + 1)
                ->setNumeroCommande('')
                ->setNumeroFacture('')
                ->setMontantFacture('')
                ->setNumeroVersion(1)
                ->setRatioMontantPayer(0.00);
        }
    }
}
