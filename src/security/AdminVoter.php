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
        $roles = $user->getRoles();

        $isAdmin = false;

        foreach ($roles as $role) {
            if ($role->getRoleName() === 'ADMINISTRATEUR') {
                $isAdmin = true;
                break;
            }
        }

        return $isAdmin;
    }
}