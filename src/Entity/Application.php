<?php

namespace App\Entity;

use App\Traits\DateTrait;
use App\Entity\CategorieAteApp;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

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

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="applications")
     */
    private $users;


    /**
     * @ORM\ManyToMany(targetEntity=CategorieAteApp::class, mappedBy="applications")
     */
    private $categorieAtes;


    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->categorieAtes = new ArrayCollection();
    }

    
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

   
   
    public function getDerniereId()
    {
        return $this->derniereId;
    }

  
    public function setDerniereId(string $derniereId): self
    {
        $this->derniereId = $derniereId;

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

  
    public function getCategorieAtes(): Collection
    {
        return $this->categorieAtes;
    }

    public function addCategorieAte(CategorieAteApp $categorieAteApp): self
    {
        if (!$this->categorieAtes->contains($$categorieAteApp)) {
            $this->categorieAtes[] = $$categorieAteApp;
            $$categorieAteApp->addApplication($this);
        }
        return $this;
    }

    public function removeCategorieAte(CategorieAteApp $categorieAteApp): self
    {
        if ($this->categorieAtes->contains($$categorieAteApp)) {
            $this->categorieAtes->removeElement($$categorieAteApp);
            $$categorieAteApp->removeApplication($this);
        }
        return $this;
    }
}
