<?php

namespace App\Service\migration;

use App\Entity\da\DaValider;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use App\Repository\da\DaValiderRepository;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;
use App\Repository\da\DemandeApproRepository;
use Doctrine\ORM\EntityManagerInterface;

class MigrationDaService
{
    private DemandeApproRepository $demandeApproRepository;
    private DemandeApproLRepository $demandeApproLRepository;
    private DemandeApproLRRepository $demandeApproLRRepository;
    private DaValiderRepository $daValiderRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->daValiderRepository = $em->getRepository(DaValider::class);
        $this->demandeApproLRRepository = $em->getRepository(DemandeApproLR::class);
        $this->demandeApproLRepository = $em->getRepository(DemandeApproL::class);
        $this->demandeApproRepository = $em->getRepository(DemandeAppro::class);
    }

    public function migrationDa($output)
    {

    }

    public function recuperationDonnee()
    {
        $daValiders = $this->daValiderRepository->findBy([]);
    }
}