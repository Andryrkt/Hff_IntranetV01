<?php

namespace App\Factory\ddp;

use App\Dto\ddp\DemandePaiementDto;
use App\Entity\admin\ddp\TypeDemande;
use Doctrine\ORM\EntityManagerInterface;

class DemandePaiementFactory
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function load(int $id): DemandePaiementDto
    {
        $dto = new DemandePaiementDto();
        $dto->typeDemande = $this->em->getRepository(TypeDemande::class)->find($id);

        return $dto;
    }
}
