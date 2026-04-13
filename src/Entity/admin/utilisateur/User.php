<?php

namespace App\Entity\admin\utilisateur;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\admin\Personnel;
use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\Application;
use App\Entity\admin\AgenceServiceIrium;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\admin\utilisateur\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="users")
 * @ORM\HasLifecycleCallbacks
 */
class User implements UserInterface
{
    use DateTrait;

    public const PROFIL_CHEF_ATELIER = 9;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length="255")
     *
     * @var [type]
     */
    private $nom_utilisateur = '';

    /**
     * @ORM\Column(type="integer")
     *
     * @var [type]
     */
    private $matricule;

    /**
     * @ORM\Column(type="string")
     *
     * @var [type]
     */
    private $mail;

    /**
     * @ORM\ManyToMany(targetEntity=Application::class, inversedBy="users")
     * @ORM\JoinTable(name="users_applications")
     */
    private $applications;

    /**
     * @ORM\ManyToOne(targetEntity=Personnel::class, inversedBy="users")
     * @ORM\JoinColumn(name="personnel_id", referencedColumnName="id")
     */
    private $personnels;

    /**
     * @ORM\ManyToOne(targetEntity=AgenceServiceIrium::class, inversedBy="userAgenceService")
     * @ORM\JoinColumn(name="agence_utilisateur", referencedColumnName="id")
     */
    private $agenceServiceIrium;

    /**
     * @ORM\ManyToMany(targetEntity=Service::class, inversedBy="userServiceAutoriser")
     * @ORM\JoinTable(name="users_service")
     */
    private $serviceAutoriser;

    /**
     * @ORM\Column(type="string", length=10, name="num_tel")
     *
     * @var string 
     */
    private ?string $numTel;

    /**
     * @ORM\Column(type="string", length=50, name="poste")
     *
     * @var string
     */
    private ?string $poste;

    /**
     * @ORM\ManyToMany(targetEntity=Profil::class, inversedBy="users")
     * @ORM\JoinTable(name="users_profils")
     */
    private Collection $profils;

    /**
     * @ORM\OneToMany(targetEntity=AgenceServiceDefautSociete::class, mappedBy="user")
     */
    private Collection $agenceServiceDefautSocietes;

    //=================================================================================================================================

    public function __construct()
    {
        $this->applications = new ArrayCollection();
        $this->serviceAutoriser = new ArrayCollection();
        $this->profils = new ArrayCollection();
        $this->agenceServiceDefautSocietes = new ArrayCollection();
    }


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

    public function getNomUtilisateur(): string
    {
        return $this->nom_utilisateur;
    }


    public function setNomUtilisateur(string $nom_utilisateur): self
    {
        $this->nom_utilisateur = $nom_utilisateur;

        return $this;
    }


    public function getMatricule(): int
    {
        return $this->matricule;
    }


    public function setMatricule($matricule): self
    {
        $this->matricule = $matricule;

        return $this;
    }


    public function getMail()
    {
        return $this->mail;
    }


    public function setMail($mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getProfils(): Collection
    {
        return $this->profils;
    }

    public function addProfil(Profil $profil): self
    {
        if (!$this->profils->contains($profil)) {
            $this->profils[] = $profil;
            $profil->addUser($this);
        }

        return $this;
    }

    public function removeProfil(Profil $profil): self
    {
        if ($this->profils->contains($profil)) {
            $this->profils->removeElement($profil);
            if ($profil->getUsers()->contains($this)) {
                $profil->removeUser($this);
            }
        }

        return $this;
    }

    public function getAgenceServiceDefautSocietes(): Collection
    {
        return $this->agenceServiceDefautSocietes;
    }

    public function addAgenceServiceDefautSociete(AgenceServiceDefautSociete $agenceServiceDefautSociete): self
    {
        if (!$this->agenceServiceDefautSocietes->contains($agenceServiceDefautSociete)) {
            $this->agenceServiceDefautSocietes[] = $agenceServiceDefautSociete;
            $agenceServiceDefautSociete->setUser($this);
        }

        return $this;
    }

    public function removeAgenceServiceDefautSociete(AgenceServiceDefautSociete $agenceServiceDefautSociete): self
    {
        if ($this->agenceServiceDefautSocietes->contains($agenceServiceDefautSociete)) {
            $this->agenceServiceDefautSocietes->removeElement($agenceServiceDefautSociete);
            if ($agenceServiceDefautSociete->getUser() === $this) {
                $agenceServiceDefautSociete->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Application[]
     */
    public function getApplications(): Collection
    {
        return $this->applications;
    }

    public function addApplication(Application $application): self
    {
        if (!$this->applications->contains($application)) {
            $this->applications[] = $application;
        }

        return $this;
    }

    public function removeApplication(Application $application): self
    {
        if ($this->applications->contains($application)) {
            $this->applications->removeElement($application);
        }

        return $this;
    }

    public function getPersonnels()
    {
        return $this->personnels;
    }


    public function setPersonnels($personnel): self
    {
        $this->personnels = $personnel;

        return $this;
    }

    public function getAgenceServiceIrium()
    {
        return $this->agenceServiceIrium;
    }

    public function setAgenceServiceIrium($agenceServiceIrium)
    {
        $this->agenceServiceIrium = $agenceServiceIrium;

        return $this;
    }

    public function getServiceAutoriser(): Collection
    {
        return $this->serviceAutoriser;
    }

    public function addServiceAutoriser(Service $serviceAutoriser): self
    {
        if (!$this->serviceAutoriser->contains($serviceAutoriser)) {
            $this->serviceAutoriser[] = $serviceAutoriser;
        }

        return $this;
    }

    public function removeServiceAutoriser(Service $serviceAutoriser): self
    {
        if ($this->serviceAutoriser->contains($serviceAutoriser)) {
            $this->serviceAutoriser->removeElement($serviceAutoriser);
        }

        return $this;
    }

    /**
     * RECUPERE LES id du service Autoriser
     */
    public function getServiceAutoriserIds(): array
    {
        return $this->serviceAutoriser->map(function ($serviceAutorise) {
            return $serviceAutorise->getId();
        })->toArray();
    }

    /**
     * RECUPERE LES id de l'application
     *
     * @return array
     */
    public function getApplicationsIds(): array
    {
        return $this->applications->map(function ($app) {
            return $app->getId();
        })->toArray();
    }

    /**
     * Get the value of numTel
     *
     * @return  string
     */
    public function getNumTel()
    {
        return $this->numTel;
    }

    /**
     * Set the value of numTel
     *
     * @param  string  $numTel
     *
     * @return  self
     */
    public function setNumTel(string $numTel)
    {
        $this->numTel = $numTel;

        return $this;
    }

    /**
     * Get the value of poste
     *
     * @return  string
     */
    public function getPoste()
    {
        return $this->poste;
    }

    /**
     * Set the value of poste
     *
     * @param  string  $poste
     *
     * @return  self
     */
    public function setPoste(string $poste)
    {
        $this->poste = $poste;

        return $this;
    }

    /** 
     * ========================================
     * Fonction utilitaire sur l'entité User
     * ========================================
     */
    public function getFirstName(): string
    {
        return $this->personnels->getPrenoms();
    }

    public function getLastName(): string
    {
        return $this->personnels->getNom();
    }

    public function getFullName(): string
    {
        return $this->getLastName() . ' ' . $this->getFirstName();
    }

    public function getRoles() {}
    public function getPassword() {}
    public function getSalt() {}
    public function eraseCredentials() {}
    public function getUsername() {}
    public function getUserIdentifier() {}
}
