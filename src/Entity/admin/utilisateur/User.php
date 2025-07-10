<?php

namespace App\Entity\admin\utilisateur;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\admin\Societte;
use App\Entity\admin\Personnel;
use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\Application;
use App\Entity\admin\utilisateur\Role;
use App\Entity\admin\AgenceServiceIrium;
use App\Entity\admin\utilisateur\Fonction;
use Doctrine\Common\Collections\Collection;
use App\Entity\admin\utilisateur\Permission;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\admin\utilisateur\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\admin\historisation\pageConsultation\UserLogger;

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
     * @ORM\ManyToMany(targetEntity=Role::class, inversedBy="users", cascade={"remove"})
     * @ORM\JoinTable(name="user_roles")
     */
    private $roles;


    /**
     * @ORM\ManyToMany(targetEntity=Application::class, inversedBy="users", cascade={"remove"})
     * @ORM\JoinTable(name="users_applications")
     */
    private $applications;

    /**
     * @ORM\ManyToOne(targetEntity=Societte::class, inversedBy="users",  cascade={"remove"})
     * @ORM\JoinColumn(name="societe_id", referencedColumnName="id")
     */
    private ?Societte $societtes;


    /**
     * @ORM\ManyToOne(targetEntity=Personnel::class, inversedBy="users",  cascade={"remove"})
     * @ORM\JoinColumn(name="personnel_id", referencedColumnName="id")
     */
    private $personnels;


    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $superieurs = [];



    /**
     * @ORM\ManyToOne(targetEntity=Fonction::class, inversedBy="users",  cascade={"remove"})
     * @ORM\JoinColumn(name="fonctions_id", referencedColumnName="id")
     */
    private  $fonction;

    /**
     * @ORM\ManyToOne(targetEntity=AgenceServiceIrium::class, inversedBy="userAgenceService",  cascade={"remove"})
     * @ORM\JoinColumn(name="agence_utilisateur", referencedColumnName="id")
     */
    private $agenceServiceIrium;

    /**
     * @ORM\ManyToMany(targetEntity=Agence::class, inversedBy="usersAutorises",  cascade={"remove"})
     * @ORM\JoinTable(name="agence_user", 
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="agence_id", referencedColumnName="id")}
     * )
     */
    private $agencesAutorisees;


    /**
     * @ORM\ManyToMany(targetEntity=Service::class, inversedBy="userServiceAutoriser",  cascade={"remove"})
     * @ORM\JoinTable(name="users_service")
     */
    private $serviceAutoriser;



    /**
     * @ORM\ManyToMany(targetEntity=Permission::class, inversedBy="users",  cascade={"remove"})
     * @ORM\JoinTable(name="users_permission")
     */
    private $permissions;



    /**
     * @ORM\OneToMany(targetEntity=UserLogger::class, mappedBy="user", cascade={"persist", "remove"})
     */
    private $userLoggers;

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
    //=================================================================================================================================

    public function __construct()
    {
        $this->applications = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->agencesAutorisees = new ArrayCollection();
        $this->serviceAutoriser = new ArrayCollection();
        $this->permissions = new ArrayCollection();
        $this->userLoggers = new ArrayCollection();
    }


    public function getId()
    {
        return $this->id;
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
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
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



    public function getSociettes()
    {
        return $this->societtes;
    }

    public function setSociettes(?Societte $societtes): self
    {
        $this->societtes = $societtes;
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

    public function getSuperieurs(): array
    {
        if ($this->superieurs !== null) {
            return $this->superieurs;
        } else {
            return [];
        }
    }

    public function setSuperieurs(array $superieurs): self
    {
        $this->superieurs = $superieurs;

        return $this;
    }

    public function addSuperieur(User $superieurId): self
    {

        $superieurIds[] = $superieurId->getId();

        if ($this->superieurs === null) {
            $this->superieurs = [];
        }

        if (!in_array($superieurIds, $this->superieurs, true)) {
            $this->superieurs[] = $superieurId;
        }

        return $this;
    }

    public function removeSuperieur(User $superieurId): self
    {
        $superieurIds[] = $superieurId->getId();

        if (($key = array_search($superieurId, $this->superieurs, true)) !== false) {
            unset($this->superieurs[$key]);
            $this->superieurs = array_values($this->superieurs);
        }

        return $this;
    }


    public function getFonction()
    {
        return $this->fonction;
    }


    public function setFonction($fonction): self
    {
        $this->fonction = $fonction;

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

    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    public function addPermisssion(Permission $permissions): self
    {
        if (!$this->permissions->contains($permissions)) {
            $this->permissions[] = $permissions;
        }

        return $this;
    }

    public function removePermission(Permission $permissions): self
    {
        if ($this->permissions->contains($permissions)) {
            $this->permissions->removeElement($permissions);
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


    public function getPassword() {}


    public function getSalt() {}


    public function eraseCredentials() {}


    public function getUsername() {}

    public function getUserIdentifier() {}

    /**
     * Get the value of userLoggers
     */
    public function getUserLoggers(): Collection
    {
        return $this->userLoggers;
    }

    /**
     * Add value to userLoggers
     *
     * @return self
     */
    public function addUserLogger(UserLogger $userLogger): self
    {
        $this->userLoggers[] = $userLogger;
        $userLogger->setUser($this); // Synchronisation inverse
        return $this;
    }

    /**
     * Set the value of userLoggers
     *
     * @return  self
     */
    public function setUserLoggers($userLoggers)
    {
        $this->userLoggers = $userLoggers;

        return $this;
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
}
