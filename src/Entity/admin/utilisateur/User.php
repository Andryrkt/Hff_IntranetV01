<?php

namespace App\Entity\admin\utilisateur;

use App\Entity\cas\Casier;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\da\DemandeAppro;
use App\Entity\tik\TkiPlanning;
use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\Application;
use App\Entity\dit\CommentaireDitOr;
use App\Entity\da\DemandeApproParent;
use App\Entity\admin\utilisateur\Role;
use Doctrine\Common\Collections\Collection;
use App\Entity\admin\utilisateur\Permission;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\admin\historisation\pageConsultation\UserLogger;
use App\Entity\admin\Personnel;
use App\Entity\tik\TkiReplannification;
use App\Entity\admin\AgenceServiceIrium;
use App\Entity\admin\utilisateur\Fonction;
use App\Repository\admin\utilisateur\UserRepository;

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
     * @ORM\ManyToMany(targetEntity=Role::class, inversedBy="users")
     * @ORM\JoinTable(name="user_roles")
     */
    private $roles;

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
     * @ORM\OneToMany(targetEntity=Casier::class, mappedBy="nomSessionUtilisateur",  cascade={"remove"})
     */
    private $casiers;

    /**
     * @ORM\ManyToOne(targetEntity=AgenceServiceIrium::class, inversedBy="userAgenceService")
     * @ORM\JoinColumn(name="agence_utilisateur", referencedColumnName="id")
     */
    private $agenceServiceIrium;

    /**
     * @ORM\ManyToMany(targetEntity=Agence::class, inversedBy="usersAutorises")
     * @ORM\JoinTable(name="agence_user", 
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="agence_id", referencedColumnName="id")}
     * )
     */
    private $agencesAutorisees;

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

    //=================================================================================================================================

    public function __construct()
    {
        $this->applications = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->casiers = new ArrayCollection();
        $this->agencesAutorisees = new ArrayCollection();
        $this->serviceAutoriser = new ArrayCollection();
        $this->profils = new ArrayCollection();
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

    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        if ($this->roles->removeElement($role)) {
            $role->removeUser($this);
        }

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

    /**
     * Get the value of demandeInterventions
     */
    public function getCasiers()
    {
        return $this->casiers;
    }

    public function addCasier(Casier $casier): self
    {
        if (!$this->casiers->contains($casier)) {
            $this->casiers[] = $casier;
            $casier->setNomSessionUtilisateur($this);
        }

        return $this;
    }

    public function removeCasier(Casier $casier): self
    {
        if ($this->casiers->contains($casier)) {
            $this->casiers->removeElement($casier);
            if ($casier->getNomSessionUtilisateur() === $this) {
                $casier->setNomSessionUtilisateur(null);
            }
        }

        return $this;
    }

    public function setCasiers($casier)
    {
        $this->casiers = $casier;

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

    public function getAgencesAutorisees(): Collection
    {
        return $this->agencesAutorisees;
    }

    public function addAgenceAutorise(Agence $agence): self
    {
        if (!$this->agencesAutorisees->contains($agence)) {
            $this->agencesAutorisees[] = $agence;
        }

        return $this;
    }

    public function removeAgenceAutorise(Agence $agence): self
    {
        if ($this->agencesAutorisees->contains($agence)) {
            $this->agencesAutorisees->removeElement($agence);
        }

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
     * RECUPERE LES id de role de l'User sous forme de tableau
     */
    public function getRoleIds(): array
    {
        return $this->roles->map(function ($role) {
            return $role->getId();
        })->toArray();
    }

    /**
     * RECUPERE LES noms de role de l'User sous forme de tableau
     */
    public function getRoleNames(): array
    {
        return $this->roles->map(function ($role) {
            return $role->getRoleName();
        })->toArray();
    }


    /**
     * RECUPERE LES id de l'agence Autoriser
     */
    public function getAgenceAutoriserIds(): array
    {
        return $this->agencesAutorisees->map(function ($agenceAutorise) {
            return $agenceAutorise->getId();
        })->toArray();
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
     * RECUPERE LES codes de l'agence Autoriser
     */
    public function getAgenceAutoriserCode(): array
    {
        return $this->agencesAutorisees->map(function ($agenceAutorise) {
            return $agenceAutorise->getCodeAgence();
        })->toArray();
    }


    /**
     * RECUPERE LES code du service Autoriser
     */
    public function getServiceAutoriserCode(): array
    {
        return $this->serviceAutoriser->map(function ($serviceAutorise) {
            return $serviceAutorise->getCodeService();
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

    public function getCodeAgenceUser()
    {
        return $this->agenceServiceIrium ? $this->agenceServiceIrium->getAgenceIps() : null;
    }

    public function getCodeServiceUser()
    {
        return $this->agenceServiceIrium ? $this->agenceServiceIrium->getServiceIps() : null;
    }

    public function getChefService()
    {
        if ($this->agenceServiceIrium && method_exists($this->agenceServiceIrium, '__load')) {
            $this->agenceServiceIrium->__load();
        }

        return $this->agenceServiceIrium ? $this->agenceServiceIrium->getChefServiceId() : null;
    }

    public function getPassword() {}


    public function getSalt() {}


    public function eraseCredentials() {}


    public function getUsername() {}

    public function getUserIdentifier() {}

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
}
