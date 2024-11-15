<?php

namespace App\Entity\admin\utilisateur;

use App\Entity\cas\Casier;
use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\admin\Societte;
use App\Entity\admin\Personnel;
use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\Application;
use App\Entity\dit\CommentaireDitOr;
use App\Entity\admin\utilisateur\Role;
use App\Entity\admin\AgenceServiceIrium;
use App\Entity\admin\utilisateur\Fonction;
use Doctrine\Common\Collections\Collection;
use App\Entity\admin\utilisateur\Permission;
use App\Entity\tik\DemandeSupportInformatique;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\admin\utilisateur\UserRepository;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="users")
 * @ORM\HasLifecycleCallbacks
 */
class User implements UserInterface
{
    use DateTrait;

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
     * @ORM\ManyToMany(targetEntity=Societte::class, inversedBy="users", cascade={"remove"})
     * @ORM\JoinTable(name="users_societe")
     */
    private $societtes;


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
     * @ORM\OneToMany(targetEntity=Casier::class, mappedBy="nomSessionUtilisateur",  cascade={"remove"})
     */
    private $casiers;

    /**
     * @ORM\ManyToOne(targetEntity=Fonction::class, inversedBy="users",  cascade={"remove"})
     * @ORM\JoinColumn(name="fonctions_id", referencedColumnName="id")
     */
    private  $fonction ;

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
     * @ORM\OneToMany(targetEntity=CommentaireDitOr::class, mappedBy="utilisateurId")
     */
    private $commentaireDitOr;

    /**
     * @ORM\OneToMany(targetEntity=DemandeSupportInformatique::class, mappedBy="userId")
     */
    private $supportInfoUser;

    //=================================================================================================================================

    public function __construct()
    {
        $this->applications = new ArrayCollection();
        $this->societtes = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->casiers = new ArrayCollection();
        $this->agencesAutorisees = new ArrayCollection();
        $this->serviceAutoriser = new ArrayCollection();
        $this->permissions = new ArrayCollection();
        $this->commentaireDitOr = new ArrayCollection();
        $this->supportInfoUser = new ArrayCollection();
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

    
    public function setNomUtilisateur( string $nom_utilisateur): self
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

    
    public function setMail( $mail): self
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


    
    public function getSociettes(): Collection
    {
        return $this->societtes;
    }

    public function addSociette(Societte $societte): self
    {
        if (!$this->societtes->contains($societte)) {
            $this->societtes[] = $societte;
        }

        return $this;
    }

    public function removeSociette(Societte $societte): self
    {
        if ($this->societtes->contains($societte)) {
            $this->societtes->removeElement($societte);
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

    public function getSuperieurs(): array
    {
        if($this->superieurs !== null){
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

    public function addSuperieur( User $superieurId): self
    {
        
        $superieurIds[] = $superieurId->getId();

        if($this->superieurs === null ){
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
     * RECUPERE LES id de role
     */
    public function getRoleIds(): array
    {
        return $this->roles->map(function($role) {
            return $role->getId();
        })->toArray();
    }


    /**
     * RECUPERE LES id de l'agence Autoriser
     */
    public function getAgenceAutoriserIds(): array
    {
        return $this->agencesAutorisees->map(function($agenceAutorise) {
            return $agenceAutorise->getId();
        })->toArray();
    }


    /**
     * RECUPERE LES id du service Autoriser
     */
    public function getServiceAutoriserIds(): array
    {
        return $this->serviceAutoriser->map(function($serviceAutorise) {
            return $serviceAutorise->getId();
        })->toArray();
    }

   

    
    public function getPassword(){}

   
    public function getSalt(){}

 
    public function eraseCredentials(){}

    
    public function getUsername(){}

    public function getUserIdentifier(){}
}