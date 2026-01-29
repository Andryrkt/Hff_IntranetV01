<?php

namespace App\Service\ddp;

use App\Constants\ddp\StatutConstants;
use App\Dto\ddp\DemandePaiementDto;
use App\Entity\ddp\DemandePaiement;
use Doctrine\ORM\EntityManagerInterface;
use App\Mapper\ddp\DemandePaiementMapper;
use App\Repository\ddp\DemandePaiementRepository;

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
     * @param DemandePaiementDto $dto
     * @return void
     */
    public function createDdp(DemandePaiementDto $dto)
    {
        $ddp = DemandePaiementMapper::map($dto);

        $this->em->persist($ddp);
        $this->em->flush();
    }

    /**
     * Modifier une demande de paiement existant
     *
     * @param DemandePaiementDto $dto
     * @return void
     */
    public function updateDdp(DemandePaiementDto $dto)
    {
        $ddp = $this->ddpRepository->findOneBy(['numeroDdp' => $dto->numeroDdp]);
        $ddp = DemandePaiementMapper::mapUpdate($dto, $ddp);

        $this->em->persist($ddp);
        $this->em->flush();
    }

    public function createHistoriqueStatut(DemandePaiementDto $dto)
    {
        $hitoriqueStatut = DemandePaiementMapper::mapStatut($dto);

        $this->em->persist($hitoriqueStatut);
        $this->em->flush();
    }
}
