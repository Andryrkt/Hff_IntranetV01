<?php

namespace App\Service\ddp;

use App\Mapper\ddp\DemandePaiementCommandeMapper;
use Doctrine\ORM\EntityManagerInterface;

class DemandePaiementCommandeService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createDdpCommande($dto, $ddp)
    {
        $ddpCommande = DemandePaiementCommandeMapper::map($dto, $ddp);

        $this->em->persist($ddpCommande);
        $this->em->flush();
    }
}
