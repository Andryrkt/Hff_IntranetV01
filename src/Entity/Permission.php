<?php

namespace App\Entity;

use App\Entity\Role;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="permissions")
 * @ORM\HasLifecycleCallbacks
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
     */
    private $date_creation;

    /**
     * @ORM\Column(type="date")
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
   

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->date_creation = $dateCreation;

        return $this;
    }

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->date_modification;
    }

    public function setDateModification(\DateTimeInterface $dateModification): self
    {
        $this->date_modification = $dateModification;

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