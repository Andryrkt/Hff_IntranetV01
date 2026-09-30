<?php

namespace App\Service\magasin\cdeFrn;

use App\Entity\cde\CdefnrSoumisAValidation;
use App\Mapper\Magasin\CdeFrn\CdeFrnSoumissionMapper;
use Doctrine\ORM\EntityManagerInterface;

final class CdeFrnSoumissionService
{
    private EntityManagerInterface $em;
    private CdeFrnSoumissionStore $store;
    private CdeFrnSoumissionMapper $mapper;

    public function __construct(
        EntityManagerInterface $em,
        CdeFrnSoumissionStore $store,
        CdeFrnSoumissionMapper $mapper
    ) {
        $this->em     = $em;
        $this->store  = $store;
        $this->mapper = $mapper;
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
