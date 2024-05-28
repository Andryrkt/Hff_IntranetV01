<?php

namespace App\Entity;

use App\Entity\User;
use App\Entity\Permission;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="roles")
 * @ORM\HasLifecycleCallbacks
 */
class Role
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="roles")
     */
    private $users;

    /**
     * @ORM\Column(type="string", length="255")
     *
     * @var string
     */
    private $role_name;

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
     * @ORM\ManyToMany(targetEntity=Permission::class, inversedBy="roles")
     *
     * @var [type]
     */
    private $permissions;

    public function __construct()
    {
        $this->date_creation = new \DateTime();
        $this->users = new ArrayCollection();
        $this->permissions = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Collection|User[]
     */ 
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if(!$this->users->contains($user)){
            $this->users[] = $user;
            $user->setRole($this);
        }
        return $this;
    }

    public function removeUser(User $user): self
    {
        if($this->users->contains($user)) {
            $this->users->removeElement($user);
            if($user->getRole() === $this){
                $user->setRole(null);
            }
        }
        
        return $this;
    }
    /**
     * Set the value of rolePermissions
     *
     * @return  self
     */ 
    public function setUser($user)
    {
        $this->users = $user;

        return $this;
    }

    /**
     * Get the value of roleName
     */ 
    public function getRoleName()
    {
        return $this->role_name;
    }

    /**
     * Set the value of roleName
     *
     * @return  self
     */ 
    public function setRoleName($roleName)
    {
        $this->role_name = $roleName;

        return $this;
    }

    /**
     * Get the value of dateCreation
     */ 
    public function getDateCreation()
    {
        return $this->date_creation;
    }

    /**
     * Set the value of dateCreation
     *
     * @return  self
     */ 
    public function setDateCreation($dateCreation)
    {
        $this->date_creation = $dateCreation;

        return $this;
    }

    /**
     * Get the value of dateModification
     */ 
    public function getDateModification()
    {
        return $this->date_modification;
    }

    /**
     * Set the value of dateModification
     *
     * @return  self
     */ 
    public function setDateModification($dateModification)
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
 * 
 *
 * @return Collection|Permissions[]
 */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }


    public function addPermissions(Permission $permission): self
    {
        if(!$this->permissions->contains($permission))
        {
            $this->permissions[] = $permission;
        }

        return $this;
    }

    public function removePermissions(Permission $permission): self
    {
        if($this->permissions->contains($permission)){
            $this->permissions->removeElement($permission);
        }

        return $this;
    }
}