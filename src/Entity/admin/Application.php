<?php

namespace App\Entity\admin;



use App\Entity\Traits\DateTrait;
use App\Entity\admin\dit\CategorieAteApp;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\utilisateur\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="applications")
 * @ORM\HasLifecycleCallbacks
 */
class Application
{
    public const ID_DEMANDE_D_ORDRE_DE_MISSION = 1;
    public const ID_NOUVEAU_BORDEREAU_D_ACQUISITION_ET_DE_MOUVEMENT_MATERIEL = 2;
    public const ID_CHANGEMENT_CASIER = 3;
    public const ID_DEMANDE_D_INTERVENTION = 4;
    public const ID_MENU_MAGASIN = 5;
    public const ID_REPORTING = 6;
    public const ID_DEMANDE_SUPPORT_INFORMATIQUE = 7;
    public const ID_COMMANDE_FOURNISSEUR = 8;
    public const ID_DEMANDE_DE_PAIEMENT = 9;
    public const ID_DEMANDE_DE_MUTATION = 10;
    public const ID_DEMANDE_D_APPROVISIONNEMENT = 11;
    public const ID_IVENTAIRE = 12;
    public const ID_LISTE_COMMENDE_FOURNISSEUR = 13;
    public const ID_DOSSIER_DE_REGULATION = 14;
    public const ID_BON_DE_LIVRAISON = 15;

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
}
