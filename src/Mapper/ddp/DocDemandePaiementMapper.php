<?php

namespace App\Mapper\ddp;

use App\Controller\Traits\ddp\DocDdpTrait;
use App\Dto\ddp\DemandePaiementDto;
use App\Entity\admin\ddp\DocDemandePaiement;

class DocDemandePaiementMapper
{
    use DocDdpTrait;

    public static function map(DemandePaiementDto $dto, array $cheminsFichiers): array
    {
        $documents = [];

        foreach ($cheminsFichiers as $chemin) {
            $nomFichier = self::nomFichier($chemin);

            $doc = new DocDemandePaiement();
            $doc
                ->setNumeroDdp($dto->numeroDdp)
                ->setTypeDocumentId($dto->typeDemande)
                ->setNomFichier($nomFichier)
                ->setNumeroVersion('1')
            ;

            $documents[] = $doc;
        }

        return $documents;
    }
}
