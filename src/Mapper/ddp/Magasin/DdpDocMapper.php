<?php

namespace App\Mapper\ddp\Magasin;

use App\Dto\ddp\DdpDto;
use App\Entity\admin\ddp\DocDemandePaiement;

class DdpDocMapper
{
    public static function map(DdpDto $dto): array
    {
        $documents = [];

        foreach ($dto->getToutesLesNomFichiers() as $nomFichier) {

            $doc = new DocDemandePaiement();
            $doc
                ->setNumeroDdp($dto->numeroDdp)
                ->setTypeDocumentId($dto->typeDdp)
                ->setNomFichier($nomFichier)
                ->setNumeroVersion('1')
            ;

            $documents[] = $doc;
        }

        return $documents;
    }
}
