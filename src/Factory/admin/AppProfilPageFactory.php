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
        $dto->peutVoirListeAvecDebiteur = $appProfilPage->isPeutVoirListeAvecDebiteur();
        $dto->peutMultiSuccursale = $appProfilPage->isPeutMultiSuccursale();
        $dto->peutSupprimer = $appProfilPage->isPeutSupprimer();
        $dto->peutExporter = $appProfilPage->isPeutExporter();
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
        $entity->setPeutVoirListeAvecDebiteur($dto->peutVoirListeAvecDebiteur);
        $entity->setPeutMultiSuccursale($dto->peutMultiSuccursale);
        $entity->setPeutSupprimer($dto->peutSupprimer);
        $entity->setPeutExporter($dto->peutExporter);

        return $entity;
    }

    public function updateFromDTO(AppProfilPageDTO $dto, ApplicationProfilPage $appProfilPage): ApplicationProfilPage
    {
        $appProfilPage->setPeutVoir($dto->peutVoir);
        $appProfilPage->setPeutVoirListeAvecDebiteur($dto->peutVoirListeAvecDebiteur);
        $appProfilPage->setPeutMultiSuccursale($dto->peutMultiSuccursale);
        $appProfilPage->setPeutSupprimer($dto->peutSupprimer);
        $appProfilPage->setPeutExporter($dto->peutExporter);

        return $appProfilPage;
    }
}
