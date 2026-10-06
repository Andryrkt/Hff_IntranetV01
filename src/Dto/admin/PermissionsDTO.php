<?php

namespace App\Dto\admin;

use App\Entity\admin\ApplicationProfil;
use App\Entity\admin\AgenceService;
use Doctrine\Common\Collections\Collection;

class PermissionsDTO
{
    public ?ApplicationProfil $applicationProfil = null;

    /** @var AgenceService[] */
    public array $agenceServices = [];

    public Collection $lignes;
}
