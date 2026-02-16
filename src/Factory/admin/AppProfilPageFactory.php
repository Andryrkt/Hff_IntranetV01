<?php

namespace App\Factory\admin;

use App\Dto\admin\AppProfilPageDTO;
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
}
