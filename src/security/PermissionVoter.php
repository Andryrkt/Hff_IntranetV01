<?php

namespace App\security;

use App\Controller\ProfilControl;



class PermissionVoter
{
    private array $voters = [];

    public function can(ProfilControl $user, string $permission, $subject = null): bool
    {
        return false;
    }


    public function addvoter($voter)
    {
        $this->voters[] = $voter;
    }
}
