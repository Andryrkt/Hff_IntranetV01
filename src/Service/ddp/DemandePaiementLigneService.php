<?php

namespace App\Service\ddp;

use App\Dto\ddp\DemandePaiementDto;
use Doctrine\ORM\EntityManagerInterface;
use App\Mapper\ddp\DemandePaiementLigneMapper;

class DemandePaiementLigneService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createLignesFromDto(DemandePaiementDto $dto): int
    {
        $lignes = DemandePaiementLigneMapper::map($dto);

        foreach ($lignes as $ligne) {

            $this->em->persist($ligne);
        }

        $this->em->flush();

        return count($lignes);
    }
}
