<?php

namespace App\Service\Admin;

use App\Dto\admin\PermissionsDTO;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Collection;
use App\Entity\admin\utilisateur\ApplicationProfilAgenceService;

class PermissionsService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function synchroniserLiaisons(PermissionsDTO $dto, Collection $oldLinks): void
    {
        $existingIds = array_map(fn($l) => $l->getAgenceService()->getId(), $oldLinks->toArray());
        $newIds = array_map(fn($a) => $a->getId(), $dto->agenceServices);

        // Ajout
        foreach ($dto->agenceServices as $agenceService) {
            if (!in_array($agenceService->getId(), $existingIds)) {
                $lien = new ApplicationProfilAgenceService($dto->applicationProfil, $agenceService);
                $this->entityManager->persist($lien);
            }
        }

        // Suppression
        foreach ($oldLinks as $link) {
            if (!in_array($link->getAgenceService()->getId(), $newIds)) {
                $this->entityManager->remove($link);
            }
        }
    }
}
