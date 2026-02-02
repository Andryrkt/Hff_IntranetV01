<?php

namespace App\Service\da;

use App\Entity\da\DaAfficher;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\da\DaAfficherRepository;

class DaTimelineService
{
    private DaAfficherRepository $daAfficherRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->daAfficherRepository = $em->getRepository(DaAfficher::class);
    }
}
