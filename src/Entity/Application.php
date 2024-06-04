<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Traits\DateTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="applications")
 * @ORM\HasLifecycleCallbacks
 */
class Application
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
    private string $nom;

    /**
     * @ORM\Column(type="string", length=255, name="code_app")
     */
    private string $codeApp;

    /**
     * @ORM\Column(type="string", length=11, name="derniere_id")
     *
     * @var ?string
     */
    private ?string $derniereId ;

    //  /**
    //  * @ORM\Column(type="date")
    //  *
    //  * @var [type]
    //  */
    // private $date_creation;

    
    // /**
    //  * @ORM\Column(type="date")
    //  *
    //  * @var [type]
    //  */
    // private $date_modification;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="applications")
     */
    private $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    //  /**
    //  * @ORM\PrePersist
    //  */
    // public function onPrePersist(): void
    // {
    //     $this->date_creation = new \DateTime();
    //     $this->date_modification = new \DateTime();
    // }

    // /**
    //  * @ORM\PreUpdate
    //  */
    // public function onPreUpdate(): void
    // {
    //     $this->date_modification = new \DateTime();
    // }
    // public function getDatecreation()
    // {
    //     return $this->date_creation;
    // }


    // public function setDatecreation( $date_creation): self
    // {
    //     $this->date_creation = $date_creation;

    //     return $this;
    // }

   
    // public function getDatemodification()
    // {
    //     return $this->date_modification;
    // }

  
    // public function setDatemodification( $date_modification): self
    // {
    //     $this->date_modification = $date_modification;

    //     return $this;
    // }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getCodeApp(): ?string
    {
        return $this->codeApp;
    }

    public function setCodeApp(string $codeApp): self
    {
        $this->codeApp = $codeApp;
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
            $user->addApplication($this);
        }
        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            $user->removeApplication($this);
        }
        return $this;
    }

    // /**
    //  * @ORM\PrePersist
    //  */
    // public function onPrePersist(): void
    // {
    //     $this->dateCreation = new \DateTime();
    //     $this->dateModification = new \DateTime();
    // }

    // /**
    //  * @ORM\PreUpdate
    //  */
    // public function onPreUpdate(): void
    // {
    //     $this->dateModification = new \DateTime();
    // }

   
    public function getDerniereId()
    {
        return $this->derniereId;
    }

  
    public function setDerniereId(string $derniereId): self
    {
        $this->derniereId = $derniereId;

        return $this;
    }
}
