<?php

namespace App\Service\ddp\Magasin;

use App\Dto\ddp\DdpDto;
use App\Entity\ddp\DemandePaiement;
use App\Mapper\ddp\Magasin\DdpMapper;
use App\Repository\ddp\DemandePaiementRepository;
use Doctrine\ORM\EntityManagerInterface;

class DdpService
{
    private EntityManagerInterface $em;
    private DemandePaiementRepository $ddpRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->ddpRepository = $this->em->getRepository(DemandePaiement::class);
    }



    /**
     * crée une nouvelle demande de paiement
     *
     * @param DdpDto $dto
     * @return DemandePaiement
     */
    public function createDdp(DdpDto $dto): DemandePaiement
    {
        $ddp = DdpMapper::map($dto);

        $this->em->persist($ddp);
        $this->em->flush();

        return $ddp;
    }
}
