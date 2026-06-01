<?php

namespace App\Service\ddp;

use App\Dto\ddp\DdpDto;
use App\Dto\ddp\DemandePaiementDto;
use App\Mapper\ddp\DemandePaiementLigneMapper;
use Doctrine\ORM\EntityManagerInterface;

class DemandePaiementLigneService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param DemandePaiementDto|DdpDto $dto
     */
    public function createLignesFromDto($dto): int
    {
        $lignes = DemandePaiementLigneMapper::map($dto);

        foreach ($lignes as $ligne) {

            $this->em->persist($ligne);
        }

        $this->em->flush();

        return count($lignes);
    }
}
