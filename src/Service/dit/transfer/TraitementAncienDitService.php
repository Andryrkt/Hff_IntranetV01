<?php

namespace App\Service\dit\transfer;

use Symfony\Component\Console\Helper\ProgressBar;

class TraitementAncienDitService
{
    private RecupDataAncienDitService $recupAncienDit;
    private TransformerEnObjetService $transformEnObjet;
    private InsertionDesDonnerService $insertionDonnee;
    private RecupDataService $recupDataService;

    public function __construct(
        RecupDataService $recupDataService,
        RecupDataAncienDitService $recupAncienDit,
        TransformerEnObjetService $transformEnObjet,
        InsertionDesDonnerService $insertionDonnee
    ) {
        $this->recupDataService = $recupDataService;
        $this->recupAncienDit = $recupAncienDit;
        $this->transformEnObjet = $transformEnObjet;
        $this->insertionDonnee = $insertionDonnee;
    }

    public function getNombreElementsDit(): int
    {
        return count($this->recupDataService->recupDansBaseDeDonnerDit());
    }

    public function traitementDit(ProgressBar $progressBar)
    {
        $ancienDitData = $this->recupDataService->recupDansBaseDeDonnerDit();
        $ancienDitTabObj = $this->transformEnObjet->transformDitEnObjet($ancienDitData, $progressBar);
        $this->insertionDonnee->insertionTableDit($ancienDitTabObj);
    }

    public function getNombreElementsDevis(): int
    {
        return count($this->recupDataService->recupDansExcel());
    }

    public function traitementDevis(ProgressBar $progressBar)
    {
        $ancienDevisData = $this->recupDataService->recupDansExcel();
        $ancienDevisTabObj = $this->transformEnObjet->transformDevisEnObjet($ancienDevisData, $progressBar);
        $this->insertionDonnee->insertionTableDit($ancienDevisTabObj);
    }
}
