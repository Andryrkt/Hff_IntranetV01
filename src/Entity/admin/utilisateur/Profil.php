<?php

namespace App\Entity\admin\utilisateur;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\Application;
use App\Entity\admin\ApplicationProfil;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="profil")
 * @ORM\HasLifecycleCallbacks
 */
class Profil
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", name="ref_profil", length=10)
     */
    private $reference;

    /**
     * @ORM\Column(type="string", name="designation_profil", length=100)
     */
    private $designation;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="profil")
     */
    private Collection $users;

    /**
     * @ORM\OneToMany(targetEntity=ApplicationProfil::class, mappedBy="profil", cascade={"persist", "remove"})
     */
    private Collection $applicationProfils;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->applicationProfils = new ArrayCollection();
    }

    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     */
    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of reference
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set the value of reference
     */
    public function setReference($reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get the value of designation
     */
    public function getDesignation()
    {
        return $this->designation;
    }

    /**
     * Set the value of designation
     */
    public function setDesignation($designation): self
    {
        $this->designation = $designation;

        return $this;
    }

    /**
     * Get the value of users
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * Set the value of users
     */
    public function setUsers(Collection $users): self
    {
        $this->users = $users;

        return $this;
    }

    /**
     * Add User
     */
    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setProfil($this);
        }

        return $this;
    }

    /**
     * Remove User
     */
    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            if ($user->getProfil() === $this) {
                $user->setProfil(null);
            }
        }

        return $this;
    }

    /**
     * Get the value of applications
     */
    public function getApplications(): ?Collection
    {
        return $this->applicationProfils->map(fn(ApplicationProfil $applicationProfil) => $applicationProfil->getApplication());
    }

    /**
     * Get the value of applicationProfils
     */
    public function getApplicationProfils(): Collection
    {
        return $this->applicationProfils;
    }

    /**
     * Set the value of applicationProfils
     */
    public function addApplicationProfil(ApplicationProfil $applicationProfil): self
    {
        $this->applicationProfils[] = $applicationProfil;

        return $this;
    }

    public function removeApplicationProfil(ApplicationProfil $applicationProfil): self
    {
        $this->applicationProfils->removeElement($applicationProfil);

        return $this;
    }
}
