<?php

namespace App\Entity\admin\utilisateur;

use App\Entity\CategorieAteApp;
use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="fonctions")
 * @ORM\HasLifecycleCallbacks
 */
class Fonction
{
   use DateTrait;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $description;

  

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="fonction")
     */
    private $users;


    public function __construct()
    {
        $this->users = new ArrayCollection();
     
    }

    public function getDescription()
    {
        return $this->description;
    }

    
    public function setDescription($description): self
    {
        $this->description = $description;

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
            $user->setFonction($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            if ($user->getFonction() === $this) {
                $user->setFonction(null);
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
