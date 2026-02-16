<?php

namespace App\Mapper\Da\ListCdeFrn;

use App\Dto\Da\ListeCdeFrn\DaDdpaDto;
use App\Dto\Da\ListeCdeFrn\DaSituationReceptionDto;
use App\Dto\Da\ListeCdeFrn\DaSoumissionFacBlDdpaDto;
use App\Entity\ddp\DemandePaiement;

class DaSoumissionFacBlDdpaMapper
{
    public static  function mapDdp(DaDdpaDto $ddpaDto, DemandePaiement $ddp): DaDdpaDto
    {
        $ddpaDto->numeroDdp = $ddp->getNumeroDdp();
        $ddpaDto->dateCreation = $ddp->getDateCreation();
        $ddpaDto->motif = $ddp->getMotif();
        $ddpaDto->montant = $ddp->getMontantAPayers();
        $ddpaDto->ratio = $ddpaDto->getRatio();


        return $ddpaDto;
    }

    public static function mapTotalPayer(DaSoumissionFacBlDdpaDto $dto, $montantPayer, $ratioTotalPayer, $montantAregulariser, $ratioMontantARegul)
    {
        $dto->totalPayer = $montantPayer;
        $dto->ratioTotalPayer = $ratioTotalPayer;
        $dto->montantAregulariser = $montantAregulariser;
        $dto->ratioMontantARegul = $ratioMontantARegul;

        return $dto;
    }

    public static function mapReception(DaSituationReceptionDto $situRecepDto, $reception)
    {
        $situRecepDto->const = $reception['constructeur'];
        $situRecepDto->ref = $reception['reference'];
        $situRecepDto->designation = $reception['designation'];
        $situRecepDto->qteCde = $reception['qte_cde'];
        $situRecepDto->qteReceptionnee = $reception['qte_receptionnee'];
        $situRecepDto->qteReliquat = $reception['qte_reliquat'];
        self::getStatutRecep($situRecepDto);

        return $situRecepDto;
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
