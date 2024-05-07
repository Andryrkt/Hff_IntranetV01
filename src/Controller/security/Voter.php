<?php

namespace App\Controller\security;

use App\Controller\ProfilControl;


interface votre
{
    public function canVote(string $permission, $subject = null): bool;

    public function vote(ProfilControl $user, string $permission, $subject = null): bool;
}
