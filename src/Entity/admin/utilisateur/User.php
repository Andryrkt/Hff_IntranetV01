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
use App\Entity\tik\DemandeSupportInformatique;
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
     * @ORM\ManyToOne(targetEntity=Fonction::class, inversedBy="users")
     * @ORM\JoinColumn(name="fonctions_id", referencedColumnName="id")
     */
    private  $fonction;

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
     * @ORM\ManyToMany(targetEntity=Permission::class, inversedBy="users")
     * @ORM\JoinTable(name="users_permission")
     */
    private $permissions;


    /**
     * @ORM\OneToMany(targetEntity=CommentaireDitOr::class, mappedBy="utilisateurId")
     */
    private $commentaireDitOr;

    /**
     * @ORM\OneToMany(targetEntity=DemandeAppro::class, mappedBy="user")
     */
    private $demandeApproUser;

    /**
     * @ORM\OneToMany(targetEntity=DemandeApproParent::class, mappedBy="user")
     */
    private $demandeApproParentUser;

    /**
     * @ORM\OneToOne(targetEntity=DemandeAppro::class, mappedBy="validateur")
     */
    private $demandeApproValidateur;

    /**
     * @ORM\OneToMany(targetEntity=DemandeSupportInformatique::class, mappedBy="userId")
     */
    private $supportInfoUser;

    /**
     * @ORM\OneToMany(targetEntity=DemandeSupportInformatique::class, mappedBy="intervenant")
     */
    private $supportInfoIntervenant;

    /**
     * @ORM\OneToMany(targetEntity=DemandeSupportInformatique::class, mappedBy="validateur")
     */
    private $supportInfoValidateur;

    /**
     * @ORM\OneToMany(targetEntity=TkiPlanning::class, mappedBy="userId")
     */
    private $tikPlanningUser;

    /**
     * @ORM\OneToMany(targetEntity=TkiReplannification::class, mappedBy="user")
     */
    private $replanificationUser;

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
        $this->permissions = new ArrayCollection();
        $this->commentaireDitOr = new ArrayCollection();
        $this->supportInfoUser = new ArrayCollection();
        $this->demandeApproUser = new ArrayCollection();
        $this->demandeApproParentUser = new ArrayCollection();
        $this->supportInfoIntervenant = new ArrayCollection();
        $this->tikPlanningUser = new ArrayCollection();
        $this->userLoggers = new ArrayCollection();
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
     * Get the value of demandeInterventions
     */
    public function getCommentaireDitOrs()
    {
        return $this->commentaireDitOr;
    }

    public function addCommentaireDitOr(CommentaireDitOr $commentaireDitOr): self
    {
        if (!$this->commentaireDitOr->contains($commentaireDitOr)) {
            $this->commentaireDitOr[] = $commentaireDitOr;
            $commentaireDitOr->setUtilisateurId($this);
        }

        return $this;
    }

    public function removeCommentaireDitOr(CommentaireDitOr $commentaireDitOr): self
    {
        if ($this->commentaireDitOr->contains($commentaireDitOr)) {
            $this->casiers->removeElement($commentaireDitOr);
            if ($commentaireDitOr->getUtilisateurId() === $this) {
                $commentaireDitOr->setUtilisateurId(null);
            }
        }

        return $this;
    }

    public function setCommentaireDitOrs($commentaireDitOr)
    {
        $this->commentaireDitOr = $commentaireDitOr;

        return $this;
    }


    /**
     * Get the value of demandeInterventions
     */
    public function getSupportInfoUser()
    {
        return $this->supportInfoUser;
    }

    public function addSupportInfoUser(DemandeSupportInformatique $supportInfoUser): self
    {
        if (!$this->supportInfoUser->contains($supportInfoUser)) {
            $this->supportInfoUser[] = $supportInfoUser;
            $supportInfoUser->setUserId($this);
        }

        return $this;
    }

    public function removeSupportInfoUser(DemandeSupportInformatique $supportInfoUser): self
    {
        if ($this->supportInfoUser->contains($supportInfoUser)) {
            $this->supportInfoUser->removeElement($supportInfoUser);
            if ($supportInfoUser->getUserId() === $this) {
                $supportInfoUser->setUserId(null);
            }
        }

        return $this;
    }

    /**
     * Get the value of supportInfoIntervenant
     */
    public function getSupportInfoIntervenant()
    {
        return $this->supportInfoIntervenant;
    }

    /**
     * Set the value of supportInfoIntervenant
     *
     * @return  self
     */
    public function setSupportInfoIntervenant($supportInfoIntervenant)
    {
        $this->supportInfoIntervenant = $supportInfoIntervenant;

        return $this;
    }

    /**
     * Get the value of demandeInterventions
     */
    public function getTikPlanningUser()
    {
        return $this->tikPlanningUser;
    }

    public function addTikPlanningUser(TkiPlanning $tikPlanningUser): self
    {
        if (!$this->tikPlanningUser->contains($tikPlanningUser)) {
            $this->tikPlanningUser[] = $tikPlanningUser;
            $tikPlanningUser->setUser($this);
        }

        return $this;
    }

    public function removeTikPlanningUser(TkiPlanning $tikPlanningUser): self
    {
        if ($this->tikPlanningUser->contains($tikPlanningUser)) {
            $this->tikPlanningUser->removeElement($tikPlanningUser);
            if ($tikPlanningUser->getUser() === $this) {
                $tikPlanningUser->setUser(null);
            }
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

    /**
     * Get the value of replanificationUser
     */
    public function getReplanificationUser()
    {
        return $this->replanificationUser;
    }

    /**
     * Set the value of replanificationUser
     *
     * @return  self
     */
    public function setReplanificationUser($replanificationUser)
    {
        $this->replanificationUser = $replanificationUser;

        return $this;
    }

    /**
     * Get the value of demandeApproUser
     */
    public function getDemandeApproUser()
    {
        return $this->demandeApproUser;
    }

    public function addDemandeApproUser(DemandeAppro $demandeApproUser): self
    {
        if (!$this->demandeApproUser->contains($demandeApproUser)) {
            $this->demandeApproUser[] = $demandeApproUser;
            $demandeApproUser->setUser($this);
        }

        return $this;
    }

    public function removeDemandeApproUser(DemandeAppro $demandeApproUser): self
    {
        if ($this->demandeApproUser->contains($demandeApproUser)) {
            $this->demandeApproUser->removeElement($demandeApproUser);
            if ($demandeApproUser->getUser() === $this) {
                $demandeApproUser->setUser(null);
            }
        }

        return $this;
    }

    /**
     * Set the value of demandeApproUser
     *
     * @return  self
     */
    public function setDemandeApproUser($demandeApproUser)
    {
        $this->demandeApproUser = $demandeApproUser;

        return $this;
    }

    /**
     * Get the value of demandeApproParentUser
     */
    public function getDemandeApproParentUser()
    {
        return $this->demandeApproParentUser;
    }

    public function addDemandeApproParentUser(DemandeApproParent $demandeApproParentUser): self
    {
        if (!$this->demandeApproParentUser->contains($demandeApproParentUser)) {
            $this->demandeApproParentUser[] = $demandeApproParentUser;
            $demandeApproParentUser->setUser($this);
        }

        return $this;
    }

    public function removeDemandeApproParentUser(DemandeApproParent $demandeApproParentUser): self
    {
        if ($this->demandeApproParentUser->contains($demandeApproParentUser)) {
            $this->demandeApproParentUser->removeElement($demandeApproParentUser);
            if ($demandeApproParentUser->getUser() === $this) {
                $demandeApproParentUser->setUser(null);
            }
        }

        return $this;
    }

    /**
     * Get the value of demandeApproValidateur
     */
    public function getDemandeApproValidateur()
    {
        return $this->demandeApproValidateur;
    }

    /**
     * Set the value of demandeApproValidateur
     *
     * @return  self
     */
    public function setDemandeApproValidateur($demandeApproValidateur)
    {
        $this->demandeApproValidateur = $demandeApproValidateur;

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
