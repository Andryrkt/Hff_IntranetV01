<?php

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\admin\utilisateur\User;
use App\Entity\admin\utilisateur\Permission;
use App\Repository\admin\utilisateur\PermissionRepository;

class GroupePermissionVoter implements PermissionVoterInterface
{
    private $em;
    private PermissionRepository $permissionRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->permissionRepository = $em->getRepository(Permission::class);
    }

    public function canVote(string $permission, $subject = null): bool
    {
        return is_string($permission);
    }

    public function vote(User $user, string $permission, $subject = null): bool
    {
        if (!$user instanceof User) {
            return false;
        }

        return (bool) $this->permissionRepository->getPermissionGroupe($user->getId(), $permission);
    }
}
