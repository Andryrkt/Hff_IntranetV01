<?php

namespace App\Entity;

use App\Entity\User;
use App\Entity\Permission;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\RoleRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="roles")
 * @ORM\Entity(repositoryClass=RoleRepository::class)
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
     * @ORM\OneToMany(targetEntity="User", mappedBy="role")
     */
    private $users;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $role_name;

    /**
     * @ORM\Column(type="date")
     */
    private $date_creation;

    /**
     * @ORM\Column(type="date")
     */
    private $date_modification;

    /**
     * @ORM\ManyToMany(targetEntity=Permission::class, inversedBy="roles")
     * @ORM\JoinTable(name="role_permissions")
     */
    private $permissions;

    public function __construct()
    {
        $this->date_creation = new \DateTime();
        $this->users = new ArrayCollection();
        $this->permissions = new ArrayCollection();
    }

    public function getId(): ?int
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
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setRole($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            if ($user->getRole() === $this) {
                $user->setRole(null);
            }
        }
        
        return $this;
    }

    public function setUsers($users): self
    {
        $this->users = $users;

        return $this;
    }

    public function getRoleName(): ?string
    {
        return $this->role_name;
    }

    public function setRoleName(string $roleName): self
    {
        $this->role_name = $roleName;

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
     * @return Collection|Permission[]
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function addPermission(Permission $permission): self
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions[] = $permission;
        }

        return $this;
    }

    public function removePermission(Permission $permission): self
    {
        if ($this->permissions->contains($permission)) {
            $this->permissions->removeElement($permission);
        }

        return $this;
    }
}
