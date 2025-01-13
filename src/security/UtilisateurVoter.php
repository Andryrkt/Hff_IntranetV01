<?php

namespace App\security;

use App\security\Voter;
use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DemandeIntervention;


class UtilisateurVoter implements Voter
{

    const CREATE = 'cree_dit';
    const READ = 'lire_dit';

    public function canVote(string $permission, $subject = null): bool
    {

        if (in_array($permission, [self::CREATE, self::READ ])&& $subject instanceof DemandeIntervention) {
            return true;
        }
        return false ;
    }

    public function vote(User $user, string $permission, $subject = null): bool
    {
        if(!$subject instanceof DemandeIntervention) {
            throw new \RuntimeException('Le sujet doit être une instance de ' . DemandeIntervention::class);
        }

        return true;
    }
}