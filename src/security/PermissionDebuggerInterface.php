<?php

namespace app\Security;


use App\Entity\admin\utilisateur\User;
use App\Security\PermissionVoterInterface;

interface PermissionDebuggerInterface
{
    public function debug(PermissionVoterInterface $voter, bool $vote, string $permission, User $user, $subject): void;
}
