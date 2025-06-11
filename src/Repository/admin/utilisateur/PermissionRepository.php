<?php

namespace App\Repository\admin\utilisateur;


use Doctrine\ORM\EntityRepository;


class PermissionRepository extends EntityRepository
{


    public function getPermissionGroupe($userId, $permissionName)
    {
        return $this->createQueryBuilder('p')
            ->select('1')
            ->leftJoin('p.roles', 'r')
            ->leftJoin('r.users', 'u')
            ->where('u.id = :userId')
            ->andWhere('p.permissionName = :permissionName')
            ->setParameters([
                'userId' => $userId,
                'permissionName' => $permissionName
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getPermissionUser($userId, $permissionName)
    {
        return $this->createQueryBuilder('p')
            ->select('1')
            ->join('p.users', 'u')
            ->where('u.id = :userId')
            ->andWhere('p.permissionName = :permissionName')
            ->setParameters([
                'userId' => $userId,
                'permissionName' => $permissionName
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
