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

    public static function mapReception(DaSoumissionFacBlDdpaDto $dto, $reception)
    {
        $dto->const = $reception['constructeur'];
        $dto->ref = $reception['reference'];
        $dto->designation = $reception['designation'];
        $dto->qteCde = $reception['qte_cde'];
        $dto->qteReceptionnee = $reception['qte_receptionnee'];
        $dto->qteReliquat = $reception['qte_reliquat'];
        self::getStatutRecep($dto);

        return $dto;
    }

    private static function getStatutRecep($dto)
    {
        $qteCde = (int)$dto->qteCde;
        $qteReliq = (int)$dto->qteReliquat;
        $qteRecep = (int)$dto->qteReceptionnee;

        $partiellementDispo = $qteReliq !== 0 && $qteRecep > 0 && $qteCde !== $qteReliq;
        $completNonLivrer =  $qteReliq === 0 && $qteCde === $qteRecep;
        $nonReceptionner = $qteRecep === 0 && $qteCde === $qteReliq;


        if ($partiellementDispo) {
            $dto->statutRecep = 'Partiellement dispo';
        } elseif ($completNonLivrer) {
            $dto->statutRecep = 'Complet non livré';
        } elseif ($nonReceptionner) {
            $dto->statutRecep = 'Non receptionné';
        } else {
            $dto->statutRecep = 'erreur';
        }
    }
}
