<?php

namespace App\Service\ddp\Magasin;

use App\Dto\ddp\DdpDto;
use App\Mapper\ddp\Magasin\DdpDocMapper;
use Doctrine\ORM\EntityManagerInterface;

class DdpDocService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Undocumented function
     *
     * @param DdpDto $dto
     * @return void
     */
    public function createDocDdp(DdpDto $dto)
    {
        $documents = DdpDocMapper::map($dto);

        foreach ($documents as $doc) {
            $this->em->persist($doc);
        }
        $this->em->flush();
    }
}
