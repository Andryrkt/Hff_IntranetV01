<?php

namespace App\Factory\magasin\cdeFrn\soumission;

use App\Dto\Magasin\cdeFrn\CommandeSoumissionDTO;
use App\Dto\Magasin\cdeFrn\CdeFrnSoumisAValidationDTO;
use App\Dto\Magasin\cdeFrn\CdeFrnSoumisAValidationLigneDTO;

class CdeFrnSoumisAValidationFactory
{
    public function hydrate(CommandeSoumissionDTO $commandeSoumissionDTO, int $numeroVersion, string $filePath): CdeFrnSoumisAValidationDTO
    {
        $dto = new CdeFrnSoumisAValidationDTO();

        $dto->numCde     = $commandeSoumissionDTO->numeroCommande;
        $dto->codeFrn    = $commandeSoumissionDTO->numFrn;
        $dto->libelleFrn = $commandeSoumissionDTO->nomFrn;
        $dto->numVersion = $numeroVersion;
        $dto->urlPDF     = rtrim($_ENV['BASE_PATH_FICHIER_COURT'], '/\\') . "/$filePath";

        foreach ($commandeSoumissionDTO->allValidatedOR as $validatedOR) {
            $dto->lignes[] = $this->hydrateLigne($dto, $validatedOR, "OR");
        }

        foreach ($commandeSoumissionDTO->allValidatedPO as $validatedPO) {
            $dto->lignes[] = $this->hydrateLigne($dto, $validatedPO, "NEG");
        }

        return $dto;
    }

    private function hydrateLigne(CdeFrnSoumisAValidationDTO $cdeFrnSoumisAValidationDTO, string $numeroDocument, string $typeDocument): CdeFrnSoumisAValidationLigneDTO
    {
        $dto = new CdeFrnSoumisAValidationLigneDTO();

        $dto->numCde         = $cdeFrnSoumisAValidationDTO->numCde;
        $dto->typeDocument   = $typeDocument;
        $dto->numeroDocument = $numeroDocument;
        $dto->numeroVersion  = $cdeFrnSoumisAValidationDTO->numVersion;

        return $dto;
    }
}
