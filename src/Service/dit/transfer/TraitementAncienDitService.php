<?php

namespace App\Service\dit\transfer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class TraitementAncienDitService
{
    private RecupDataAncienDitService $recupAncienDit;

    private TransformerEnObjetService $transformEnObjet;

    private InsertionDesDonnerService $insertionDonnee;

    private RecupDataService $recupDataService;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->recupDataService = new RecupDataService();
        $this->recupAncienDit = new RecupDataAncienDitService($entityManager);
        $this->transformEnObjet = new TransformerEnObjetService($entityManager);
        $this->insertionDonnee = new InsertionDesDonnerService($entityManager);
    }

    public function getNombreElementsDit(): int
    {
        return count($this->recupDataService->recupDansBaseDeDonnerDit());
    }

    public function traitementDit(ProgressBar $progressBar)
    {
        //recupération des anciens données
        $ancienDitData = $this->recupDataService->recupDansBaseDeDonnerDit();
        //reorganisation des données dans un tableau
        // $ancienDitTabs = $this->recupAncienDit->dataDit($ancienDitData);
        //crée une tableau d'objet
        $ancienDitTabObj = $this->transformEnObjet->transformDitEnObjet($ancienDitData, $progressBar);
        // Insertion des données dans le nouveau base de donnée
        $this->insertionDonnee->insertionTableDit($ancienDitTabObj);
    }

    public function getNombreElementsDevis(): int
    {
        return count($this->recupDataService->recupDansExcel());
    }

    public function traitementDevis(ProgressBar $progressBar)
    {
        //recupération des anciens données
        $ancienDevisData = $this->recupDataService->recupDansExcel();
        //reorganisation des données dans un tableau
        $ancienDevisTabs = $this->recupAncienDit->dataDevis($ancienDevisData);
        //crée une tableau d'objet
        $ancienDevisTabObj = $this->transformEnObjet->transformDevisEnObjet($ancienDevisTabs, $progressBar);
        // Insertion des données dans le nouveau base de donnée
        $this->insertionDonnee->insertionTableDit($ancienDevisTabObj);
    }
}
