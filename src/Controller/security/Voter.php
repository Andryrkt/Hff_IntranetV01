<?php

namespace App\Controller;


interface votre
{
    public function canVote(string $permission, $subject = null): bool;

    public function vote(ProfilControl $user, string $permission, $subject = null): bool;
}
