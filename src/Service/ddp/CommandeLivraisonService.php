<?php

namespace App\Service\ddp;

use App\Mapper\ddp\CommandeLivraisonMapper;
use Doctrine\ORM\EntityManagerInterface;

class CommandeLivraisonService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createCommandeLivraison($dto)
    {
        $commandeLivraison = CommandeLivraisonMapper::map($dto);

        $this->em->persist($commandeLivraison);
        $this->em->flush();
    }
}
