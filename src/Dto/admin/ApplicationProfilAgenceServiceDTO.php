<?php

namespace App\Dto\admin;

use App\Entity\admin\ApplicationProfil;

class ApplicationProfilAgenceServiceDTO
{
    public ?ApplicationProfil $applicationProfil = null;

    /** @var int[] */
    public array $agenceServiceIds = [];
}
