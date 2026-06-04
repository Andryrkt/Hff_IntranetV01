<?php

namespace App\Service\da;

use App\Constants\ddp\StatutConstants;
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
        $demandePaiementAvance = $dto->sommeMontantDdpaValider;
        $MontantFactureEnCours = $dto->montantBlFacture; // numero facture que l'utilisateur est entrain de soumettre
        $ratio = 0.0;
        $totalCommande = $dto->totalMontantCommande;
        $TotalMontantFactureSoumise = $dto->sommeMontantFactureDejaPayer;
        $ratioDejaPayer = ($totalCommande > 0) ? ($TotalMontantFactureSoumise / $totalCommande) * 100 : 0;

        // si il y une demande de paiement à l'avance  : appliquer le solde avance
        if ($demandePaiementAvance > 0) {

            // echo "misy demande de paiement à l'avance";
            // calcul solde      
            $totalMontantPayer = abs(($demandePaiementAvance - $TotalMontantFactureSoumise) - $MontantFactureEnCours); // 200 000 AR
            if ($totalCommande > 0) {
                $ratio = ($totalMontantPayer / $totalCommande) * 100;
            }

            if ($totalMontantPayer > 0) {
                // création demande demande de paiement.
                $dto->soumissionDdpAFaire = false;
            } else {
                // regul si = 0 / bloquer si < 0

                $dto->soumissionDdpAFaire = $totalMontantPayer < 0 ? true : false;
            }
        } else {
            // si pas de demande de paiement à l'avance : ne pas appliquer le solde avance
            // echo "tsy misy demande de paiement à l'avance";
            $totalMontantPayer = $MontantFactureEnCours;
            if ($totalCommande > 0) {
                $ratio = ($totalMontantPayer / $totalCommande) * 100;
            }
        }


        $dto->ratioMontantARegul = round($ratio, 2); // ratio du montant à régulariser par rapport au montant total de la commande
        $dto->ratioMontantDejaPaye = $ratioDejaPayer; // ratio du montant déjà payé par rapport au montant total de la commande
        $dto->totalMontantPayer = $TotalMontantFactureSoumise; // montant total à payer (somme du montant de la facture en cours et des montants des factures déjà soumises)
        $dto->montantAregulariser = $totalMontantPayer; // montant à régulariser (différence entre le montant total à payer et le montant déjà payé)


        // Utilisation du mapper pour les données de sortie spécifiques au DTO
        return DaSoumissionFacBlMapper::mapTotalPayer(
            $dto,
            $TotalMontantFactureSoumise,
            $ratioDejaPayer,
            $totalMontantPayer,
            $ratio
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
