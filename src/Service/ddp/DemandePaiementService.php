<?php

namespace App\Service\ddp;

use App\Dto\ddp\DdpDto;
use App\Dto\ddp\DemandePaiementDto;
use App\Entity\ddp\DemandePaiement;
use App\Mapper\ddp\DemandePaiementMapper;
use App\Repository\ddp\DemandePaiementRepository;
use Doctrine\ORM\EntityManagerInterface;

class DemandePaiementService
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
     * @param DemandePaiementDto|DdpDto $dto
     * @return DemandePaiement
     */
    public function createDdp($dto): DemandePaiement
    {
        $ddp = DemandePaiementMapper::map($dto);

        $this->em->persist($ddp);
        $this->em->flush();

        return $ddp;
    }

    /**
     * Modifier une demande de paiement existant
     *
     * @param DemandePaiementDto|DdpDto $dto
     * @return void
     */
    public function updateDdp($dto)
    {
        $ddp = $this->ddpRepository->findOneBy(['numeroDdp' => $dto->numeroDdp]);
        $ddp = DemandePaiementMapper::mapUpdate($dto, $ddp);

        $this->em->persist($ddp);
        $this->em->flush();
    }

    /**
     * @param DemandePaiementDto|DdpDto $dto
     */
    public function createHistoriqueStatut($dto): void
    {
        $hitoriqueStatut = DemandePaiementMapper::mapStatut($dto);

        $this->em->persist($hitoriqueStatut);
        $this->em->flush();
    }
}
