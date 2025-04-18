<?php

namespace App\Security;

use App\Entity\admin\utilisateur\User;
use app\Security\PermissionDebuggerInterface;

class PermissionDebugger implements PermissionDebuggerInterface
{
    public function debug(PermissionVoterInterface $voter, bool $vote, string $permission, User $user, $subject): void
    {
        echo sprintf(
            "[DEBUG] Voter: %s | Permission: %s | Result: %s\n",
            get_class($voter),
            $permission,
            $vote ? 'GRANTED' : 'DENIED'
        );
    }
}
