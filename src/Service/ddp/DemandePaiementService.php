<?php

namespace App\Service\ddp;

use App\Dto\ddp\DemandePaiementDto;
use Doctrine\ORM\EntityManagerInterface;
use App\Mapper\ddp\DemandePaiementMapper;

class DemandePaiementService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Undocumented function
     *
     * @param DemandePaiementDto $dto
     * @return void
     */
    public function createDdp(DemandePaiementDto $dto)
    {
        $ddp = DemandePaiementMapper::map($dto);

        $this->em->persist($ddp);
        $this->em->flush();
    }

    public function createHistoriqueStatut(DemandePaiementDto $dto)
    {
        $hitoriqueStatut = DemandePaiementMapper::map($dto);

        $this->em->persist($hitoriqueStatut);
        $this->em->flush();
    }
}
