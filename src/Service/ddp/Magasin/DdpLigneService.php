<?php

namespace App\Service\ddp\Magasin;

use App\Dto\ddp\DdpDto;
use App\Mapper\ddp\Magasin\DdpLigneMapper;
use Doctrine\ORM\EntityManagerInterface;

class DdpLigneService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param DdpDto $dto
     */
    public function createLignesFromDto(DdpDto $dto): int
    {
        $lignes = DdpLigneMapper::map($dto);

        foreach ($lignes as $ligne) {

            $this->em->persist($ligne);
        }

        $this->em->flush();

        return count($lignes);
    }
}
