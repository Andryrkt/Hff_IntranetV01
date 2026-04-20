<?php

namespace App\Service\ddp;

use App\Dto\ddp\DemandePaiementDto;

class DdpFinancialService
{
    /**
     * Calcule les montants et pourcentages globaux pour le DTO.
     */
    public function calculateGlobalFinancials(DemandePaiementDto $dto, float $totalPayer): void
    {
        $dto->montantTotalCde = (float)$dto->montantTotalCde;
        $dto->montantDejaPaye = $totalPayer;
        
        $montantAPayer = $dto->montantAPayer();
        $dto->montantRestantApayer = $dto->montantTotalCde - $dto->montantDejaPaye;
        $dto->montantAPayer = (string)$dto->montantRestantApayer;
        
        if ($dto->montantTotalCde > 0) {
            $dto->pourcentageAvance = (($dto->montantDejaPaye + $montantAPayer) / $dto->montantTotalCde) * 100;
            $dto->pourcentageAPayer = (int)(($montantAPayer / $dto->montantTotalCde) * 100);
        } else {
            $dto->pourcentageAvance = 0.0;
            $dto->pourcentageAPayer = 0;
        }
    }

    /**
     * Calcule les ratios de paiement pour une commande.
     * 
     * @return array [ratioTotalPayer, montantAregulariser, ratioMontantARegul]
     */
    public function calculatePaymentRatios(float $totalPayer, float $totalMontantCommande): array
    {
        if ($totalMontantCommande <= 0) {
            return [0, 0, 0];
        }

        $ratioTotalPayer = ($totalPayer / $totalMontantCommande) * 100;
        $montantAregulariser = $totalMontantCommande - $totalPayer;
        $ratioMontantARegul = ($montantAregulariser / $totalMontantCommande) * 100;

        return [$ratioTotalPayer, $montantAregulariser, $ratioMontantARegul];
    }
}
