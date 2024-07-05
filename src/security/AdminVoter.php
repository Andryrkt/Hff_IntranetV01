<?php

namespace App\security;

use App\Entity\User;
use App\security\Voter;


class AdminVoter implements Voter
{
    public function canVote(string $permission, $subject = null): bool
    {
        return true;
    }

    public function vote(User $user, string $permission, $subject = null): bool
    {

        return $user->getRoles() === 'ADMINISTRATEUR';
    }
}