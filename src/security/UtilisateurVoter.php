<?php

namespace App\security;

use App\Entity\DemandeIntervention;
use App\Entity\User;
use App\security\Voter;


class UtilisateurVoter implements Voter
{

    const CREATE = 'cree_dit';
    const READ = 'lire_dit';

    public function canVote(string $permission, $subject = null): bool
    {
        return ($permission === self::CREATE || $permission === self::READ) && $subject instanceof DemandeIntervention;
    }

    public function vote(User $user, string $permission, $subject = null): bool
    {
        if(!$subject instanceof DemandeIntervention) {
            throw new \RuntimeException('Le sujet doit être une instance de ' . DemandeIntervention::class);
        }

        return true;
    }
}