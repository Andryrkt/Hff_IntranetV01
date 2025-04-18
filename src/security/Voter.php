<?php

namespace App\Security;

use App\Controller\ProfilControl;
use App\Entity\admin\utilisateur\User;

interface Voter
{
    public function canVote(string $permission, $subject = null): bool;

    public function vote(User $user, string $permission, $subject = null): bool;
}
