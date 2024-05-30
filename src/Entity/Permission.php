<?php

namespace App\Entity;

use App\Entity\Role;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="permissions")
 */
class Permission
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", name="permission_name")
     */
    private $permissionName;

     /**
     * @ORM\Column(type="date")
     *
     * @var [type]
     */
    private $date_creation;

    /**
     * @ORM\Column(type="date")
     *
     * @var [type]
     */
    private $date_modification;

    /**
     * @ORM\ManyToMany(targetEntity=Role::class, mappedBy="permissions")
     *
     * @var [type]
     */
    private $roles;

    public function __construct()
    {
        $this->date_creation = new \DateTime();
        $this->roles = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }


    public function getPermissionName()
    {
        return $this->permissionName;
    }

    
    public function setPermissionName($permissionName): self
    {
        $this->permissionName = $permissionName;

        return $this;
    }
     /**
     * @ORM\PrePersist
     */
    public function onPrePersist(): void
    {
        $this->date_creation = new \DateTime();
        $this->date_modification = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function onPreUpdate(): void
    {
        $this->date_modification = new \DateTime();
    }

   

   

    /**
     * Get the value of date_creation
     *
     * @return  [type]
     */ 
    public function getDatecreation()
    {
        return $this->date_creation;
    }


    public function setDatecreation( $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    
    public function getDatemodification()
    {
        return $this->date_modification;
    }

  
    public function setDatemodification( $date_modification): self
    {
        $this->date_modification = $date_modification;

        return $this;
    }

     /**
     * @return Collection|Roles[]
     */ 
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRoles(Role $role): self
    {
        if(!$this->roles->contains($role)){
            $this->roles[] = $role;
            $role->addPermission($this);
        }
        return $this;
    }

    public function removeRoles(Role $role): self
    {
        if($this->roles->contains($role)) {
            $this->roles->removeElement($role);
          $role->removePermission($this);
        }
        
        return $this;
    }
}