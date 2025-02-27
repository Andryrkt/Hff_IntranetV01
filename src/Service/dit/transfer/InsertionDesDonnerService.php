<?php

namespace App\Service\dit\transfer;

use App\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;

class InsertionDesDonnerService
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function insertionTableDit(array $demandeInterventions)
    {
        $batchSize = 20; // Optimisation pour les grands volumes
        $i = 0;

        foreach ($demandeInterventions as $demandeIntervention) {

            $this->em->persist($demandeIntervention);

            // Gestion des lots
            if (($i % $batchSize) === 0) {
                $this->em->flush();
                $this->em->clear();
            }
            $i++;
        }
        
        // Dernier flush
        $this->em->flush();
    }
}