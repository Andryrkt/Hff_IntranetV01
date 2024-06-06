<?php

namespace App\security;

use App\Controller\ProfilControl;


interface Voter
{
    public function canVote(string $permission, $subject = null): bool;

    public function vote(ProfilControl $user, string $permission, $subject = null): bool;
}
