<?php

namespace App\Factory\admin;

use App\Dto\admin\PermissionsDTO;

class PermissionsFactory
{
    public function createFromDTO(PermissionsDTO $dto): Permissions {}
}
