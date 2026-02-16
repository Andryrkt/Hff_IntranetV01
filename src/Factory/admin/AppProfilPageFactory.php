<?php

namespace App\Factory\admin;

use App\Dto\admin\AppProfilPageDTO;
use App\Entity\admin\ApplicationProfil;
use App\Entity\admin\utilisateur\ApplicationProfilPage;
use Doctrine\Common\Collections\Collection;

class AppProfilPageFactory
{
    public function createDTOFromAppProfilPage(ApplicationProfilPage $appProfilPage): AppProfilPageDTO
    {
        $dto = new AppProfilPageDTO;
        $dto->page = $appProfilPage->getPage();
        $dto->peutVoir = $appProfilPage->isPeutVoir();
        $dto->peutAjouter = $appProfilPage->isPeutAjouter();
        $dto->peutModifier = $appProfilPage->isPeutModifier();
        $dto->peutSupprimer = $appProfilPage->isPeutSupprimer();
        return $dto;
    }

    public function createDTOCollection(Collection $appProfilPages): Collection
    {
        return $appProfilPages->map(fn(ApplicationProfilPage $appProfilPage) => $this->createDTOFromAppProfilPage($appProfilPage));
    }

    public function createFromDTO(AppProfilPageDTO $dto, ApplicationProfil $applicationProfil): ApplicationProfilPage
    {
        $entity = new ApplicationProfilPage($applicationProfil, $dto->page);
        $entity->setPeutVoir($dto->peutVoir);
        $entity->setPeutAjouter($dto->peutAjouter);
        $entity->setPeutModifier($dto->peutModifier);
        $entity->setPeutSupprimer($dto->peutSupprimer);

        return $entity;
    }

    public function updateFromDTO(AppProfilPageDTO $dto, ApplicationProfilPage $appProfilPage): ApplicationProfilPage
    {
        $appProfilPage->setPeutVoir($dto->peutVoir);
        $appProfilPage->setPeutAjouter($dto->peutAjouter);
        $appProfilPage->setPeutModifier($dto->peutModifier);
        $appProfilPage->setPeutSupprimer($dto->peutSupprimer);

        return $appProfilPage;
    }
}
