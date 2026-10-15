<?php

namespace App\Factory\magasin\cdeFrn\soumission;

use Doctrine\ORM\EntityManagerInterface;
use App\Dto\Magasin\cdeFrn\CommandeSoumissionDTO;
use App\Dto\Magasin\cdeFrn\CdeFrnSoumisAValidationDTO;
use App\Dto\Magasin\cdeFrn\CdeFrnSoumisAValidationLigneDTO;
use App\Entity\cde\CdefnrSoumisAValidation;
use App\Repository\cde\CdefnrSoumisAValidationRepository;

final class CdeFrnSoumisAValidationFactory
{
    private CdefnrSoumisAValidationRepository $cdeFrnSoumisAValidationRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->cdeFrnSoumisAValidationRepository = $em->getRepository(CdefnrSoumisAValidation::class);
    }

    /** 
     * Fonction pour hydrater un DTO de cde frn soumis à validation
     * 
     * @param CommandeSoumissionDTO $commandeSoumissionDTO DTO de cde frn
     * 
     * @return CdeFrnSoumisAValidationDTO DTO de cde frn soumis à validation
     */
    public function hydrate(CommandeSoumissionDTO $commandeSoumissionDTO): CdeFrnSoumisAValidationDTO
    {
        $numCde = $commandeSoumissionDTO->numeroCommande;
        $dto = new CdeFrnSoumisAValidationDTO();

        $dto->numCde     = $numCde;
        $dto->codeFrn    = $commandeSoumissionDTO->numFrn;
        $dto->libelleFrn = $commandeSoumissionDTO->nomFrn;
        $dto->numVersion = $this->cdeFrnSoumisAValidationRepository->findNumeroVersionMax($numCde) + 1;

        $urlsPDF          = $this->getUrlPDF($numCde);
        $dto->urlPDFCourt = $urlsPDF['urlPDFCourt'];
        $dto->urlPDFLong  = $urlsPDF['urlPDFLong'];

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

    /** 
     * Fonction pour obtenir l'URL du PDF à générer
     * 
     * @param string $numCde numéro du cde frn
     * 
     * @return array{urlPDFCourt:string,urlPDFLong:string}
     */
    private function getUrlPDF(string $numCde): array
    {
        $basePath = rtrim($_ENV['BASE_PATH_FICHIER'], '/\\');
        $filePath = "magasin/commandes fournisseurs/$numCde/$numCde.pdf";
        $urlPDFLong = "$basePath/$filePath";
        $dirPath  = dirname($urlPDFLong);

        if (!is_dir($dirPath)) mkdir($dirPath, 0777, true);

        if (file_exists($urlPDFLong)) unlink($urlPDFLong);

        return [
            'urlPDFCourt' => rtrim($_ENV['BASE_PATH_FICHIER_COURT'], '/\\') . "/$filePath",
            'urlPDFLong'  => $urlPDFLong
        ];
    }
}
