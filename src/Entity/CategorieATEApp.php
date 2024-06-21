<?php

namespace App\Entity;


use App\Traits\DateTrait;
use App\Entity\Application;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategorieAteAppRepository")
 * @ORM\Table(name="categorie_ate_app")
 * @ORM\HasLifecycleCallbacks
 */

class CategorieAteApp
{
    use DateTrait;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, name="libelle_categorie_ate_app")
     */
    private string $libelleCategorieAteApp;

    /**
     * @ORM\OneToMany(targetEntity="DemandeIntervention", mappedBy="categorieDemande")
     */
    private $demandeInterventions;
    

       /**
     * @ORM\ManyToMany(targetEntity=Application::class, inversedBy="categorieAtes")
     * @ORM\JoinTable(name="categorieAteApp_applications")
     */
    private $applications;



    public function __construct()
    {
        
        $this->demandeInterventions = new ArrayCollection();
        $this->applications = new ArrayCollection();
        
    }

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of libelleCategorieAteApp
     */ 
    public function getLibelleCategorieAteApp()
    {
        return $this->libelleCategorieAteApp;
    }

    /**
     * Set the value of libelleCategorieAteApp
     *
     * @return  self
     */ 
    public function setLibelleCategorieAteApp($libelleCategorieAteApp)
    {
        $this->libelleCategorieAteApp = $libelleCategorieAteApp;

        return $this;
    }

    

   
    public function getDemandeInterventions(): Collection
    {
        return $this->demandeInterventions;
    }

    public function addDemandeIntervention(User $demandeIntervention): self
    {
        if (!$this->demandeInterventions->contains($demandeIntervention)) {
            $this->demandeInterventions[] = $demandeIntervention;
            $demandeIntervention->setRole($this);
        }

        return $this;
    }

    public function removeDemandeIntervention(User $demandeIntervention): self
    {
        if ($this->demandeInterventions->contains($demandeIntervention)) {
            $this->demandeInterventions->removeElement($demandeIntervention);
            if ($demandeIntervention->getRole() === $this) {
                $demandeIntervention->setRole(null);
            }
        }
        
        return $this;
    }

    public function setDemandeInterventions($demandeIntervention): self
    {
        $this->demandeInterventions = $demandeIntervention;

        return $this;
    }


     
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
}
