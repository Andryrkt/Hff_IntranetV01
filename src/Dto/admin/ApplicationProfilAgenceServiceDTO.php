<?php

namespace App\Dto\admin;

use App\Entity\admin\ApplicationProfil;
use App\Entity\admin\AgenceService;

class ApplicationProfilAgenceServiceDTO
{
    public ?ApplicationProfil $applicationProfil = null;

    /** @var AgenceService[] */
    public array $agenceServices = [];
}
