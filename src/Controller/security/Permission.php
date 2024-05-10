<?php

namespace App\Controller\security;

use App\Controller\ProfilControl;



class Permission
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
