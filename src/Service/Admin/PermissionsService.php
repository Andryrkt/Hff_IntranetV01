<?php

namespace App\Service\Admin;

use App\Dto\admin\PermissionsDTO;
use App\Dto\admin\AppProfilPageDTO;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Collection;
use App\Entity\admin\utilisateur\ApplicationProfilPage;
use App\Entity\admin\utilisateur\ApplicationProfilAgenceService;
use App\Factory\admin\AppProfilPageFactory;

class PermissionsService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function synchroniserLiaisons(PermissionsDTO $dto, Collection $linksAgServ, Collection $linksPage): void
    {
        // 1 - Synchroniser les liaisons agence service
        $existingIds = array_map(fn($l) => $l->getAgenceService()->getId(), $linksAgServ->toArray());
        $newIds = array_map(fn($a) => $a->getId(), $dto->agenceServices);

        // Ajout
        foreach ($dto->agenceServices as $agenceService) {
            if (!in_array($agenceService->getId(), $existingIds)) {
                $lien = new ApplicationProfilAgenceService($dto->applicationProfil, $agenceService);
                $this->entityManager->persist($lien);
            }
        }

        // Suppression
        /** @var ApplicationProfilAgenceService $link */
        foreach ($linksAgServ as $link) {
            if (!in_array($link->getAgenceService()->getId(), $newIds)) {
                $this->entityManager->remove($link);
            }
        }

        // 2 - Synchroniser les liaisons page
        $mappedLinksPage = [];
        /** @var ApplicationProfilPage $link */
        foreach ($linksPage as $link) {
            $mappedLinksPage[$link->getPage()->getId()] = $link;
        }

        $factory = new AppProfilPageFactory();
        /** @var AppProfilPageDTO $dtoPage */
        foreach ($dto->lignes as $dtoPage) {
            // Ajout ou Mise à jour
            if ($dtoPage->peutVoir) {
                if (isset($mappedLinksPage[$dtoPage->page->getId()])) {
                    $link = $mappedLinksPage[$dtoPage->page->getId()];
                    $factory->updateFromDTO($dtoPage, $link);
                } else {
                    $link = $factory->createFromDTO($dtoPage, $dto->applicationProfil);
                    $this->entityManager->persist($link);
                }
            }
            // Suppression
            else {
                if (isset($mappedLinksPage[$dtoPage->page->getId()])) {
                    $link = $mappedLinksPage[$dtoPage->page->getId()];
                    $this->entityManager->remove($link);
                }
            }
        }
    }
}
