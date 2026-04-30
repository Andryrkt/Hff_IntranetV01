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
    public function createDTOFromAppProfil(ApplicationProfil $appProfil, Collection $linksAgServ, Collection $linksPage): PermissionsDTO
    {
        $dto = new PermissionsDTO();
        $dto->applicationProfil = $appProfil;
        $dto->agenceServices = $linksAgServ->map(fn($l) => $l->getAgenceService())->toArray();
        $dto->lignes = $this->createLigneFromAppProfil($appProfil, $linksPage);
        return $dto;
    }

    private function createLigneFromAppProfil(ApplicationProfil $appProfil, Collection $linksPage): Collection
    {
        $factory = new AppProfilPageFactory();

        $pageLinkedId = $linksPage->map(fn(ApplicationProfilPage $l) => $l->getPage()->getId())->toArray();

        $newLinks = $appProfil->getApplication()->getPages()
            ->filter(fn(PageHff $page) => !in_array($page->getId(), $pageLinkedId))
            ->map(fn(PageHff $page) => (new ApplicationProfilPage($appProfil, $page))->setPeutVoir(false)); // initialiser à false pour les nouveaux

        $collection = new ArrayCollection(
            array_merge($linksPage->toArray(), $newLinks->toArray())
        );

        return $factory->createDTOCollection($collection);
    }
}
