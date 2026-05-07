<?php

namespace App\Service\ddp;

use App\Dto\ddp\DdpDto;
use App\Dto\ddp\DemandePaiementDto;
use App\Entity\ddp\DemandePaiement;
use App\Mapper\ddp\CommandeLivraisonMapper;
use Doctrine\ORM\EntityManagerInterface;

class CommandeLivraisonService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param DemandePaiementDto|DdpDto $dto
     */
    public function createCommandeLivraison($dto, DemandePaiement $ddp)
    {
        $commandeLivraison = CommandeLivraisonMapper::map($dto, $ddp);

        $this->em->persist($commandeLivraison);
        $this->em->flush();
    }
}
