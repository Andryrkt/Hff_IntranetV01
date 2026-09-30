<?php

namespace App\Service\magasin\cdeFrn;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\cde\CdefnrSoumisAValidation;
use App\Mapper\Magasin\CdeFrn\CdeFrnSoumissionMapper;
use App\Dto\Magasin\cdeFrn\CdeFrnSoumisAValidationDTO;
use App\Factory\magasin\cdeFrn\soumission\CdeFrnSoumisAValidationFactory;
use App\Model\magasin\cdeFrn\soumission\CdeSoumissionModel;
use App\Service\genererPdf\magasin\cdeFrn\GeneratePdfCdeMagasin;

final class CdeFrnSoumissionService
{
    private EntityManagerInterface $em;
    private CdeFrnSoumissionStore $store;
    private CdeFrnSoumissionMapper $mapper;
    private CdeFrnSoumisAValidationFactory $factory;
    private CdeSoumissionModel $cdeSoumissionModel;
    private GeneratePdfCdeMagasin $pdfGenerator;

    public function __construct(EntityManagerInterface $em, CdeFrnSoumissionStore $store, CdeFrnSoumissionMapper $mapper, CdeFrnSoumisAValidationFactory $factory)
    {
        $this->em      = $em;
        $this->store   = $store;
        $this->mapper  = $mapper;
        $this->factory = $factory;
        $this->cdeSoumissionModel = new CdeSoumissionModel();
    }

    public function generatePdfForSubmission(string $numCde, string $userMail, string $codeSociete): ?CdeFrnSoumisAValidationDTO
    {
        // 1. Récupération des données du document
        $commandeSoumissionDto = $this->cdeSoumissionModel->findInfoCommande($numCde, $userMail, $codeSociete);

        if ($commandeSoumissionDto === null) return null;

        // 2. Création du DTO pour la soumission à validation
        $cdeFrnSoumisAValidationDTO = $this->factory->hydrate($commandeSoumissionDto);

        // 3. Génération du PDF
        $this->pdfGenerator->generate($commandeSoumissionDto, $cdeFrnSoumisAValidationDTO->urlPDFLong);

        // 4. Retourner le DTO pour la soumission
        return $cdeFrnSoumisAValidationDTO;
    }

    public function soumettre(string $userMail, ?string $token, ?string $numCdeSaisi): CdefnrSoumisAValidation
    {
        $numCdeSaisi = trim((string) $numCdeSaisi);
        $dto = $token ? $this->store->get($userMail, $token) : null;

        if ($dto === null) {
            throw new \DomainException('La génération du PDF a expiré ou est introuvable. Veuillez régénérer le PDF.');
        }

        // Cohérence : le n° saisi doit être celui du PDF généré
        if ($dto->numCde !== $numCdeSaisi) {
            throw new \DomainException('Le numéro de commande a changé depuis la génération. Veuillez régénérer le PDF.');
        }

        $entity = $this->em->wrapInTransaction(function () use ($dto) {
            // Fraîcheur : la version a pu évoluer pendant le délai écoulé
            $versionCourante = $this->repository->findNextVersion($dto->numCde);
            if ($versionCourante !== $dto->numVersion) {
                throw new \DomainException('La commande a évolué depuis la génération. Veuillez régénérer le PDF.');
            }

            $entity = $this->mapper->toEntity($dto); // DTO + lignes → entité(s)
            $this->em->persist($entity);

            return $entity;
        });

        $this->store->discard($userMail, $token);

        return $entity;
    }
}
