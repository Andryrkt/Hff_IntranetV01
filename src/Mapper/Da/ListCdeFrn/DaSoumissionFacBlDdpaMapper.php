<?php

namespace App\Mapper\Da\ListCdeFrn;

use App\Dto\Da\ListeCdeFrn\DaSoumissionFacBlDdpaDto;
use App\Entity\ddp\DemandePaiement;

class DaSoumissionFacBlDdpaMapper
{
    public static  function mapDdp(DaSoumissionFacBlDdpaDto $dto, DemandePaiement $ddp): DaSoumissionFacBlDdpaDto
    {
        $dto->numeroDdp = $ddp->getNumeroDdp();
        $dto->dateCreation = $ddp->getDateCreation();
        $dto->motif = $ddp->getMotif();
        $dto->montant = $ddp->getMontantAPayers();
        $dto->ratio = $dto->getRatio();


        return $dto;
    }

    public static function mapTotalPayer(DaSoumissionFacBlDdpaDto $dto, $montantPayer, $ratioTotalPayer, $montantAregulariser, $ratioMontantARegul)
    {
        $dto->totalPayer = $montantPayer;
        $dto->ratioTotalPayer = $ratioTotalPayer;
        $dto->montantAregulariser = $montantAregulariser;
        $dto->ratioMontantARegul = $ratioMontantARegul;

        return $dto;
    }
}
