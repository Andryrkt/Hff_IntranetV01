<?php

namespace App\Service\dit\transfer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class TraitementAncienDitService
{
    private RecupDataAncienDitService $recupAncienDit;
    private TransformerEnObjetService $transformEnObjet;
    private InsertionDesDonnerService $insertionDonnee;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->recupAncienDit = new RecupDataAncienDitService($entityManager);
        $this->transformEnObjet = new TransformerEnObjetService();
        $this->insertionDonnee = new InsertionDesDonnerService($entityManager);
    }

    public function getNombreElementsDit(): int
    {
        return count($this->recupAncienDit->dataDit());
    }

    public function traitementDit(ProgressBar $progressBar)
    {   
        //recupération des anciens données
        $ancienDitTabs = $this->recupAncienDit->dataDit();
        //crée une tableau d'objet
        $ancienDitTabObj= $this->transformEnObjet->transformDitEnObjet($ancienDitTabs, $progressBar);
        // Insertion des données dans la base
        $this->insertionDonnee->insertionTableDit($ancienDitTabObj);
    }
}