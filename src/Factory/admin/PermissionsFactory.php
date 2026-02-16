<?php

namespace App\Factory\admin;

use App\Dto\admin\PermissionsDTO;
use App\Entity\admin\ApplicationProfil;
use Doctrine\Common\Collections\Collection;

class PermissionsFactory
{
    public function createDTOFromAppProfil(ApplicationProfil $appProfil, Collection $links): PermissionsDTO
    {
        $dto = new PermissionsDTO();
        $dto->applicationProfil = $appProfil;
        $dto->agenceServices = $links->map(fn($l) => $l->getAgenceService())->toArray();
        return $dto;
    }
}
