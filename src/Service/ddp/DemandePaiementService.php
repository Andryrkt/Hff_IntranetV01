<?php

namespace App\Service\ddp;

use App\Dto\ddp\DemandePaiementDto;
use App\Model\ddp\DemandePaiementModel;
use App\Service\TableauEnStringService;
use Doctrine\ORM\EntityManagerInterface;
use App\Mapper\ddp\DemandePaiementMapper;

class DemandePaiementService
{
    private EntityManagerInterface $em;
    private DemandePaiementModel $ddpModel;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->ddpModel  = new DemandePaiementModel();
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

   
}
