<?php

namespace App\Service\da;

use App\Dto\Da\ListeCdeFrn\DaSoumissionFacBlDto;
use App\Entity\ddp\DemandePaiement;
use App\Mapper\Da\ListCdeFrn\DaSoumissionFacBlMapper;
use App\Model\da\DaSoumissionFacBlModel;
use Doctrine\ORM\EntityManagerInterface;

class DaSoumissionCalculService
{
    private EntityManagerInterface $em;
    private DaSoumissionFacBlModel $daSoumissionFacBlModel;

    public function __construct(EntityManagerInterface $em, DaSoumissionFacBlModel $daSoumissionFacBlModel)
    {
        $this->em = $em;
        $this->daSoumissionFacBlModel = $daSoumissionFacBlModel;
    }

    /**
     * Calcule le montant total d'une commande
     */
    public function getTotalMontantCommande(string $numCde): float
    {
        $totalMontantCommande = $this->daSoumissionFacBlModel->getTotalMontantCommande($numCde);
        return $totalMontantCommande ? (float)$totalMontantCommande[0] : 0.0;
    }

    /**
     * Calcule le total déjà payé pour une liste de demandes de paiement
     */
    public function getTotalPayer(array $ddps): float
    {
        $montantPayer = 0.0;
        foreach ($ddps as $item) {
            /** @var DemandePaiement $item */
            $montantPayer += (float)$item->getMontantAPayers();
        }
        return $montantPayer;
    }

    /**
     * Enrichit le DTO avec les montants et ratios calculés
     */
    public function calculerMontantEtRatios(DaSoumissionFacBlDto $dto): DaSoumissionFacBlDto
    {
        $ddpRepository = $this->em->getRepository(DemandePaiement::class);
        $ddps = $ddpRepository->getDdpSelonNumCde($dto->numeroCde);

        $totalMontantPayer = $this->getTotalPayer($ddps);
        $ratioTotalPayer = ($dto->totalMontantCommande > 0) ? ($totalMontantPayer / $dto->totalMontantCommande) * 100 : 0;
        $montantAregulariser = $dto->totalMontantCommande - $totalMontantPayer;
        $ratioMontantARegul = ($dto->totalMontantCommande > 0) ? ($montantAregulariser /  $dto->totalMontantCommande) * 100 : 0;

        $dto->totalMontantPayer = $totalMontantPayer;

        // Utilisation du mapper pour les données de sortie spécifiques au DTO
        return DaSoumissionFacBlMapper::mapTotalPayer(
            $dto, 
            $totalMontantPayer, 
            $ratioTotalPayer, 
            $montantAregulariser, 
            $ratioMontantARegul
        );
    }

    /**
     * Calcule le cumul des ratios pour les DDPA d'un DTO
     */
    public function calculerCumulRatios(DaSoumissionFacBlDto $dto): DaSoumissionFacBlDto
    {
        $runningCumul = 0;
        foreach ($dto->daDdpa as $ddpaDto) {
            $runningCumul += $ddpaDto->ratio;
            $ddpaDto->cumul = $runningCumul;
        }
        return $dto;
    }
}
