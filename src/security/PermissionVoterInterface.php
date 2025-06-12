<?php

namespace App\Security;


use App\Entity\admin\utilisateur\User;

interface PermissionVoterInterface
{
    public function canVote(string $permission, $subject = null): bool;

    public function vote(User $user, string $permission, $subject = null): bool;
}
