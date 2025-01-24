<?php

namespace App\Entity\admin;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\utilisateur\User;
use App\Entity\admin\AgenceServiceIrium;
use Doctrine\Common\Collections\Collection;
use App\Repository\admin\PersonnelRepository;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="Personnel")
 * @ORM\Entity(repositoryClass=PersonnelRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Personnel 
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $Matricule;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private ?string $Nom;

    /**
     * @ORM\Column(type="string", length=4)
     */
    private ?string $Code_AgenceService_Sage;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $Numero_Fournisseur_IRIUM;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $Code_AgenceService_IRIUM;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private ?string $Numero_Telephone = null;

    /**
     * @ORM\Column(type="string")
     */
    private ?string $Numero_Compte_Bancaire;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private ?DateTime $Date_creation = null;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private ?string $Libelle_AgenceService_Sage = null;

    /**
     * @ORM\Column(type="string", length=4, nullable=true)
     */
    private ?string $Code_Service_Agence_IRIUM = null;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private ?string $Libelle_Service_Agence_IRIUM = null;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private ?string $Prenoms;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private ?string $Qualification;

    /**
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="personnels")
     */
    private $users;

    /**
     * @ORM\ManyToOne(targetEntity=AgenceServiceIrium::class, inversedBy="personnelId")
     * @ORM\JoinColumn(name="agence_service_irium_id", referencedColumnName="id")
     */
    private $agenceServiceIriumId;
    

    public function __construct()
    {
        $this->Date_creation = new \DateTime();
        $this->users = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist(): void
    {
        $this->Date_creation = new \DateTime();
    }

    //======================================================
    // Getters and Setters...

    public function getId(): int
    {
        return $this->id;
    }

    public function getMatricule(): int
    {
        return $this->Matricule;
    }

    public function setMatricule(int $Matricule): self
    {
        $this->Matricule = $Matricule;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->Nom;
    }

    public function setNom(?string $Nom): self
    {
        $this->Nom = $Nom;
        return $this;
    }

    public function getCodeAgenceServiceSage(): ?string
    {
        return $this->Code_AgenceService_Sage;
    }

    public function setCodeAgenceServiceSage(?string $Code_AgenceService_Sage): self
    {
        $this->Code_AgenceService_Sage = $Code_AgenceService_Sage;
        return $this;
    }

    public function getNumeroFournisseurIRIUM(): ?int
    {
        return $this->Numero_Fournisseur_IRIUM;
    }

    public function setNumeroFournisseurIRIUM(?int $Numero_Fournisseur_IRIUM): self
    {
        $this->Numero_Fournisseur_IRIUM = $Numero_Fournisseur_IRIUM;
        return $this;
    }

    public function getCodeAgenceServiceIRIUM(): ?int
    {
        return $this->Code_AgenceService_IRIUM;
    }

    public function setCodeAgenceServiceIRIUM(?int $Code_AgenceService_IRIUM): self
    {
        $this->Code_AgenceService_IRIUM = $Code_AgenceService_IRIUM;
        return $this;
    }

    public function getNumeroTelephone(): ?string
    {
        return $this->Numero_Telephone;
    }

    public function setNumeroTelephone(?string $Numero_Telephone): self
    {
        $this->Numero_Telephone = $Numero_Telephone;
        return $this;
    }

    public function getNumeroCompteBancaire(): ?string
    {
        return $this->Numero_Compte_Bancaire;
    }

    public function setNumeroCompteBancaire(string $Numero_Compte_Bancaire): self
    {
        $this->Numero_Compte_Bancaire = $Numero_Compte_Bancaire;
        return $this;
    }

    public function getDatecreation(): ?DateTime
    {
        return $this->Date_creation;
    }

    public function setDatecreation(?DateTime $Date_creation): self
    {
        $this->Date_creation = $Date_creation;
        return $this;
    }

    public function getLibelleAgenceServiceSage(): ?string
    {
        return $this->Libelle_AgenceService_Sage;
    }

    public function setLibelleAgenceServiceSage(?string $Libelle_AgenceService_Sage): self
    {
        $this->Libelle_AgenceService_Sage = $Libelle_AgenceService_Sage;
        return $this;
    }

    public function getCodeServiceAgenceIRIUM(): ?string
    {
        return $this->Code_Service_Agence_IRIUM;
    }

    public function setCodeServiceAgenceIRIUM(?string $Code_Service_Agence_IRIUM): self
    {
        $this->Code_Service_Agence_IRIUM = $Code_Service_Agence_IRIUM;
        return $this;
    }

    public function getPrenoms(): string
    {
        return $this->Prenoms;
    }

    public function setPrenoms(string $Prenoms): self
    {
        $this->Prenoms = $Prenoms;
        return $this;
    }

    public function getQualification(): string
    {
        return $this->Qualification;
    }

    public function setQualification(string $Qualification): self
    {
        $this->Qualification = $Qualification;
        return $this;
    }

    public function getLibelleServiceAgenceIRIUM(): ?string
    {
        return $this->Libelle_Service_Agence_IRIUM;
    }

    public function setLibelleServiceAgenceIRIUM(?string $Libelle_Service_Agence_IRIUM): self
    {
        $this->Libelle_Service_Agence_IRIUM = $Libelle_Service_Agence_IRIUM;
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
            $user->setPersonnels($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            if ($user->getPersonnels() === $this) {
                $user->setPersonnels(null);
            }
        }
        
        return $this;
    }

    public function setUsers($users): self
    {
        $this->users = $users;

        return $this;
    }

  
    public function getAgenceServiceIriumId()
    {
        return $this->agenceServiceIriumId;
    }

  
    public function setAgenceServiceIriumId($agenceServiceIriumId): self
    {
        $this->agenceServiceIriumId = $agenceServiceIriumId;

        return $this;
    }

    public function toArray(): array
    {
        return [
            
            'Matricule' => $this->Matricule
        ];
    }

    
}
