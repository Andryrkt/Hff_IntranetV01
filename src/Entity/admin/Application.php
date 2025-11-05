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
    /** Identifiant de l'application DOM (Demande d'Ordre de Mission). */
    public const ID_DOM = 1;
    /** Identifiant de l'application BADM (Nouveau Bordereau d'acquisition et de mouvement matériel). */
    public const ID_BADM = 2;
    /** Identifiant de l'application CAS (Changement de Casier) */
    public const ID_CAS = 3;
    /** Identifiant de l'application DIT (Demande d'intervention) */
    public const ID_DIT = 4;
    /** Identifiant de l'application MAG (Magasin) */
    public const ID_MAG = 5;
    /** Identifiant de l'application REP (Reporting) */
    public const ID_REP = 6;
    /** Identifiant de l'application TIK (Demande de support informatique) */
    public const ID_TIK = 7;
    /** Identifiant de l'application CFR (Commande Fournisseur) */
    public const ID_CFR = 8;
    /** Identifiant de l'application DDP (Demande de paiement) */
    public const ID_DDP = 9;
    /** Identifiant de l'application MUT (Demande de mutation) */
    public const ID_MUT = 10;
    /** Identifiant de l'application DAP (Demande d'approvisionnement) */
    public const ID_DAP = 11;
    /** Identifiant de l'application INV (Inventaire) */
    public const ID_INV = 12;
    /** Identifiant de l'application LCF (Liste de commande fournisseur) */
    public const ID_LCF = 13;
    /** Identifiant de l'application DDR (Dossier de régulation DDP) */
    public const ID_DDR = 14;
    /** Identifiant de l'application PAT (Planning Atelier interne) */
    public const ID_PAT = 15;
    /** Identifiant de l'application DDC (Demande de congé) */
    public const ID_DDC = 16;
    /** Identifiant de l'application BDL (Bon de livraison) */
    public const ID_BDL = 17;
    /** Identifiant de l'application DVM (Devis magasin) */
    public const ID_DVM = 18;
    /** Identifiant de l'application BCS (Bon de caisse) */
    public const ID_BCS = 19;

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
