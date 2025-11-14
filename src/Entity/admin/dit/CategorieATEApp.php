<?php

namespace App\Entity\admin\dit;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\Application;
use App\Entity\dit\DemandeIntervention;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\admin\dit\CategorieAteAppRepository;

/**
 * @ORM\Entity(repositoryClass=CategorieAteAppRepository::class)
 * @ORM\Table(name="categorie_ate_app")
 * @ORM\HasLifecycleCallbacks
 */

class CategorieAteApp
{
    use DateTrait;

    public const REPARATION = 7;
    
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
     * @ORM\OneToMany(targetEntity=DemandeIntervention::class, mappedBy="categorieDemande")
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

    
    public function getId()
    {
        return $this->id;
    }

    public function getLibelleCategorieAteApp()
    {
        return $this->libelleCategorieAteApp;
    }

  
    public function setLibelleCategorieAteApp($libelleCategorieAteApp): self
    {
        $this->libelleCategorieAteApp = $libelleCategorieAteApp;

        return $this;
    }

    

   
    public function getDemandeInterventions(): Collection
    {
        return $this->demandeInterventions;
    }

    public function addDemandeIntervention(DemandeIntervention $demandeIntervention): self
    {
        if (!$this->demandeInterventions->contains($demandeIntervention)) {
            $this->demandeInterventions[] = $demandeIntervention;
            $demandeIntervention->setCategorieDemande($this);
        }

        return $this;
    }

    public function removeDemandeIntervention(DemandeIntervention $demandeIntervention): self
    {
        if ($this->demandeInterventions->contains($demandeIntervention)) {
            $this->demandeInterventions->removeElement($demandeIntervention);
            if ($demandeIntervention->getCategorieDemande() === $this) {
                $demandeIntervention->setCategorieDemande(null);
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

    public function __toString(): string
    {
        return $this->libelleCategorieAteApp ?? 'Unknown'; 
    }

}
