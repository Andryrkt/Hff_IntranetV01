<?php

namespace App\Mapper\Magasin\CdeFrn;

use App\Entity\cde\CdefnrSoumisAValidation;
use App\Dto\Magasin\cdeFrn\CdeFrnSoumisAValidationDTO;
use App\Entity\cde\CdefnrSoumisAValidationLigne;

final class CdeFrnSoumissionMapper
{
    /**
     * Mapper le DTO en entité de CdeFrnSoumisAValidation
     * 
     * @param CdeFrnSoumisAValidationDTO $dto DTO de cde frn soumis à validation
     * 
     * @return CdefnrSoumisAValidation Entité de cde frn soumis à validation
     */
    public function toEntityCdeFrnSoumission(CdeFrnSoumisAValidationDTO $dto): CdefnrSoumisAValidation
    {
        $entity = new CdefnrSoumisAValidation();

        $entity
            ->setNumCdeFournisseur($dto->numCde)
            ->setCodeFournisseur($dto->codeFrn)
            ->setLibelleFournisseur($dto->libelleFrn)
            ->setNumVersion($dto->numVersion)
            ->setStatut($dto->statut)
            ->setDateHeureSoumission(new \DateTime("now", new \DateTimeZone("Indian/Antananarivo")));

        return $entity;
    }

    /**
     * Mapper les lignes du DTO en liste d'entité CdefnrSoumisAValidationLigne
     * 
     * @param CdeFrnSoumisAValidationDTO $dto DTO de cde frn soumis à validation
     * 
     * @return CdefnrSoumisAValidationLigne[]
     */
    public function toEntityCdeFrnLignesSoumission(CdeFrnSoumisAValidationDTO $dto): array
    {
        $entities = [];

        foreach ($dto->lignes as $ligneDTO) {
            $entity = new CdefnrSoumisAValidationLigne();

            $entity
                ->setNumCde($ligneDTO->numCde)
                ->setTypeDocument($ligneDTO->typeDocument)
                ->setNumeroDocument($ligneDTO->numeroDocument)
                ->setNumeroVersion($ligneDTO->numeroVersion);

            $entities[] = $entity;
        }

        return $entities;
    }
}
