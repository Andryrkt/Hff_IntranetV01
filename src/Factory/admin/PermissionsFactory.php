<?php

namespace App\Factory\admin;

use App\Dto\admin\PermissionsDTO;
use App\Entity\admin\ApplicationProfil;
use App\Entity\admin\historisation\pageConsultation\PageHff;
use App\Entity\admin\utilisateur\ApplicationProfilPage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class PermissionsFactory
{
    public function createDTOFromAppProfil(ApplicationProfil $appProfil, Collection $links): PermissionsDTO
    {
        $dto = new PermissionsDTO();
        $dto->applicationProfil = $appProfil;
        $dto->agenceServices = $links->map(fn($l) => $l->getAgenceService())->toArray();
        $dto->lignes = $this->createLigneFromAppProfil($appProfil);
        return $dto;
    }

    private function createLigneFromAppProfil(ApplicationProfil $appProfil): Collection
    {
        $factory = new AppProfilPageFactory();
        $links = $appProfil->getLiaisonsPage();

        $pageLinkedId = $links->map(fn(ApplicationProfilPage $l) => $l->getPage()->getId())->toArray();

        $newLinks = $appProfil->getApplication()->getPages()
            ->filter(fn(PageHff $page) => !in_array($page->getId(), $pageLinkedId))
            ->map(fn(PageHff $page) => new ApplicationProfilPage($appProfil, $page));

        $collection = new ArrayCollection(
            array_merge($links->toArray(), $newLinks->toArray())
        );

        return $factory->createDTOCollection($collection);
    }
}
