<?php

namespace app\Security;


use App\security\Voter;
use App\Entity\admin\utilisateur\User;

interface PermissionDebugger
{
    public function debug(Voter $voter, bool $vote, string $permission, User $user, $subject): void;
}
