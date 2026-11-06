<?php

namespace App\Service\ddp;

use App\Dto\ddp\DdpDto;
use App\Dto\ddp\DemandePaiementDto;
use App\Entity\ddp\DemandePaiement;
use App\Mapper\ddp\DemandePaiementCommandeMapper;
use Doctrine\ORM\EntityManagerInterface;

class DemandePaiementCommandeService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param DemandePaiementDto|DdpDto $dto
     * @param DemandePaiement|null $ddp
     * @return void
     */
    public function createDdpCommande($dto, DemandePaiement $ddp): void
    {
        foreach (DemandePaiementCommandeMapper::map($dto, $ddp) as $ddpCommande) {
            $this->em->persist($ddpCommande);
        }

        $this->em->flush();
    }
}
