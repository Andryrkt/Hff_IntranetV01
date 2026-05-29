<?php

namespace App\Service\ddp;

use App\Dto\ddp\DemandePaiementDto;
use App\Entity\ddp\DemandePaiement;
use App\Model\ddp\DemandePaiementModel;
use Doctrine\ORM\EntityManagerInterface;

class DdpFinancialService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Calcule les montants et pourcentages globaux pour le DTO.
     */
    public function calculateGlobalFinancials(DemandePaiementDto $dto): array
    {
        $totalMontantCommande = $dto->totalMontantCommande;
        $dto->montantDejaPaye = $dto->montantDejaPaye;
        $dto->montantRestantApayer = $totalMontantCommande - $dto->montantDejaPaye;
        $dto->montantAPayer = $dto->montantRestantApayer;

        if ($totalMontantCommande > 0) {
            $pourcentageAvance = (($dto->montantDejaPaye + $dto->montantAPayer) / $totalMontantCommande) * 100 . ' %';
            $pourcentageAPayer = (int)(($dto->montantAPayer / $totalMontantCommande) * 100);
        } else {
            $pourcentageAvance = '0 %';
            $pourcentageAPayer = 0;
        }

        return [$pourcentageAvance, $pourcentageAPayer];
    }

    /**
     * Calcule les ratios de paiement pour une commande.
     * 
     * @return array 
     */
    public function calculatePaymentRatios(DemandePaiementDto $dto): array
    {
        $montantAregulariser = $this->calculMontantARegulariser($dto);
        $ratioMontantDejaPaye = $this->ratioMontantTotalDejaPaye($dto);
        $ratioMontantARegul = $this->ratioMontantARegulariser($dto);
        $montantDejaPaye = $this->montantTotalDejaPaye($dto);

        return [$montantDejaPaye, $ratioMontantDejaPaye, $montantAregulariser, $ratioMontantARegul];
    }

    /**
     * Récupération du montant total de la commande.
     * Dans la table frn_cde du base de donnée informix
     * 
     * @param string $numeroCommande
     * @param string $codeSociete
     * @return float
     * @throws \Exception
     */
    public function recuperationMontantTotalCommande(string $numeroCommande, string $codeSociete)
    {
        $demandePaiementModel = new DemandePaiementModel();
        $montantTotalCommande = (float) $demandePaiementModel->getMontantTotalCde($numeroCommande, $codeSociete);
        if ($montantTotalCommande <= 0) throw new \Exception("Le montant total de la commande est nul");
        return $montantTotalCommande;
    }

    /**
     * Calcul du ratio du montant à régulariser par rapport au montant total de la commande.
     * 
     * @param DemandePaiementDto $dto
     * @return float
     */
    private function ratioMontantARegulariser(DemandePaiementDto $dto): float
    {
        $totalMontantCommande = $dto->totalMontantCommande;
        $montantAregulariser = $this->calculMontantARegulariser($dto);
        return ($montantAregulariser / $totalMontantCommande) * 100;
    }

    /**
     * Calcul du montant à régulariser par rapport au montant total de la commande.
     * -----------------------------------------------------------------------------
     * Le montant à régulariser correspond à la différence entre le montant total de la commande et le montant déjà payé.
     * 
     * @param DemandePaiementDto $dto
     * @return float
     * @throws \Exception
     */
    private function calculMontantARegulariser(DemandePaiementDto $dto): float
    {
        $totalMontantCommande = $dto->totalMontantCommande;
        $montantDejaPaye = $this->montantTotalDejaPaye($dto);

        return $totalMontantCommande - $montantDejaPaye;
    }

    /**
     * Calcul du montant total déjà payé par rapport au montant total de la commande.
     * -----------------------------------------------------------------------------
     * Le montant total déjà payé correspond à la somme des montants déjà payés que
     * l'on recupère dans la table demande_paiement du base de donnée sqlServer.
     * 
     * @param DemandePaiementDto $dto
     * @return float
     */
    private function montantTotalDejaPaye(DemandePaiementDto $dto): float
    {
        $ddps = $this->em->getRepository(DemandePaiement::class)->getDdpSelonNumCde($dto->numeroCommande);
        $montantDejaPaye = 0.00;
        foreach ($ddps as $item) {
            $montantDejaPaye += $item->getMontantAPayers();
        }

        return $montantDejaPaye;
    }

    /**
     * Calcul du ratio du montant total déjà payé par rapport au montant total de la commande.
     * 
     * @param DemandePaiementDto $dto
     * @return float
     */
    private function ratioMontantTotalDejaPaye(DemandePaiementDto $dto): float
    {
        $totalMontantCommande = $dto->totalMontantCommande;
        $montantDejaPaye = $this->montantTotalDejaPaye($dto);

        return ($montantDejaPaye / $totalMontantCommande) * 100;
    }
}
