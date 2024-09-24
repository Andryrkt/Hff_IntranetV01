<?php

namespace App\Entity\admin\utilisateur;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="role_permissions")
 */
class RolePermission
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Role")
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     */
    private $role;

    /**
     * @ORM\ManyToOne(targetEntity="Permission")
     * @ORM\JoinColumn(name="permission_id", referencedColumnName="id")
     */
    private $permission;

    // other properties and methods
}