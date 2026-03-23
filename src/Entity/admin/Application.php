<?php

namespace App\Entity\admin;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\utilisateur\User;
use App\Entity\admin\ApplicationProfil;
use App\Entity\admin\dit\CategorieAteApp;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\admin\historisation\pageConsultation\PageHff;

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
     * @ORM\Column(type="string", length=11, name="derniere_id", nullable=true)
     *
     * @var ?string
     */
    private ?string $derniereId = null;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="applications")
     */
    private $users;


    /**
     * @ORM\ManyToMany(targetEntity=CategorieAteApp::class, mappedBy="applications")
     */
    private $categorieAtes;

    /**
     * @ORM\ManyToOne(targetEntity=Vignette::class, inversedBy="applications")
     * @ORM\JoinColumn(name="vignette_id", referencedColumnName="id", nullable=true)
     */
    private ?Vignette $vignette = null;

    /**
     * @ORM\OneToMany(targetEntity=PageHff::class, mappedBy="application", cascade={"persist"})
     */
    private Collection $pages;

    /**
     * @ORM\OneToMany(targetEntity=ApplicationProfil::class, mappedBy="application", cascade={"persist"})
     */
    private Collection $applicationProfils;

    public function __construct()
    {
        $this->applicationProfils = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->categorieAtes = new ArrayCollection();
        $this->pages = new ArrayCollection();
    }


    public function getId(): ?int
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


    public function setDerniereId(?string $derniereId): self
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

    public function __toString()
    {
        return $this->codeApp;
    }

    /**
     * Get the value of vignette
     */
    public function getVignette(): ?Vignette
    {
        return $this->vignette;
    }

    /**
     * Set the value of vignette
     */
    public function setVignette(?Vignette $vignette): self
    {
        $this->vignette = $vignette;

        return $this;
    }

    /**
     * Get the value of pages
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    /**
     * Add Page
     */
    public function addPage(PageHff $page): self
    {
        if (!$this->pages->contains($page)) {
            $this->pages[] = $page;
            $page->setApplication($this);
        }

        return $this;
    }

    /**
     * Remove Page
     */
    public function removePage(PageHff $page): self
    {
        if ($this->pages->contains($page)) {
            $this->pages->removeElement($page);
            if ($page->getApplication() === $this) {
                $page->setApplication(null);
            }
        }

        return $this;
    }

    /**
     * Set the value of pages
     */
    public function setPages(Collection $pages): self
    {
        $this->pages = $pages;

        return $this;
    }

    /**
     * Get the value of profils
     */
    public function getProfils(): ?Collection
    {
        return $this->applicationProfils->map(fn(ApplicationProfil $applicationProfil) => $applicationProfil->getProfil());
    }

    /**
     * Get the value of applicationProfils
     */
    public function getApplicationProfils(): Collection
    {
        return $this->applicationProfils;
    }

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
