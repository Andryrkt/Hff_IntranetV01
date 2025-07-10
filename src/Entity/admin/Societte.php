<?php

namespace App\Entity\admin;

use App\Entity\badm\Badm;
use App\Entity\TypeReparation;
use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\utilisateur\User;
use App\Entity\dit\DemandeIntervention;
use Doctrine\Common\Collections\Collection;
use App\Repository\admin\SocietteRepository;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=SocietteRepository::class)
 * @ORM\Table(name="societe")
 * @ORM\HasLifecycleCallbacks
 */
class Societte
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=3, name="code_societe")
     */
    private $codeSociete;


    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="societtes", orphanRemoval=true)
     */
    private $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();

    }

    public function getId(): int
    {
        return $this->id;
    }



    public function getNom()
    {
        return $this->nom;
    }

  
    public function setNom($nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getCodeSociete()
    {
        return $this->codeSociete;
    }

    
    public function setCodeSociete($codeSociete): self
    {
        $this->codeSociete = $codeSociete;

        return $this;
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
            $user->setSociettes($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            if ($user->getSociettes() === $this) {
                $user->setSociettes(null);
            }
        }
        
        return $this;
    }

    public function setUsers($users): self
    {
        $this->users = $users;

        return $this;
    }
}
