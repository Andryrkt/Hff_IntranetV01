<?php

namespace App\Dto\admin;

use App\Entity\admin\ApplicationProfil;
use App\Entity\admin\AgenceService;

class PermissionsDTO
{
    public ?ApplicationProfil $applicationProfil = null;

    /** @var AgenceService[] */
    public array $agenceServices = [];
}
