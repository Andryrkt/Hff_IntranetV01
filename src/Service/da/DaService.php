<?php

namespace App\Service\da;

use App\Entity\da\DemandeAppro;
use App\Entity\da\DaObservation;
use App\Entity\da\DemandeApproL;
use App\Entity\da\DemandeApproLR;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\da\DemandeApproRepository;
use App\Repository\da\DaObservationRepository;
use App\Repository\da\DemandeApproLRepository;
use App\Repository\da\DemandeApproLRRepository;

class DaService
{
    private DemandeApproRepository $demandeApproRepository;
    private DemandeApproLRepository $demandeApproLRepository;
    private DemandeApproLRRepository $demandeApproLRRepository;
    private DaObservationRepository $daObservationRepository;
    private FileUploaderForDAService $daFileUploader;

    public function __construct(EntityManagerInterface $em, FileUploaderForDAService $daFileUploader)
    {
        $this->demandeApproRepository   = $em->getRepository(DemandeAppro::class);
        $this->demandeApproLRepository  = $em->getRepository(DemandeApproL::class);
        $this->demandeApproLRRepository = $em->getRepository(DemandeApproLR::class);
        $this->daObservationRepository  = $em->getRepository(DaObservation::class);
        $this->daFileUploader           = $daFileUploader;
    }
}
