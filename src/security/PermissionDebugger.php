<?php

namespace app\security;

use App\Entity\User;
use App\security\Voter;

interface PermissionDebugger
{
    public function debug (Voter $voter, bool $vote, string $permission, User $user, $subject): void;
}