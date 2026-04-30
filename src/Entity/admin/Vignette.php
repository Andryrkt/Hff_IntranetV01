<?php

namespace App\Entity\admin;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\Application;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\admin\appStructure\VignetteRepository;

/**
 * @ORM\Entity(repositoryClass=VignetteRepository::class)
 * @ORM\Table(name="vignette")
 * @ORM\HasLifecycleCallbacks
 */
class Vignette
{
    use DateTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", name="ref_vignette", length=10)
     */
    private $reference;

    /**
     * @ORM\Column(type="string", name="nom_vignette", length=100)
     */
    private $nom;

    /**
     * @ORM\OneToMany(targetEntity=Application::class, mappedBy="vignette", cascade={"persist"})
     */
    private Collection $applications;

    public function __construct()
    {
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
     * Set the value of id
     */
    public function setId($id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of reference
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set the value of reference
     */
    public function setReference($reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get the value of nom
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set the value of nom
     */
    public function setNom($nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get the value of applications
     */
    public function getApplications(): Collection
    {
        return $this->applications;
    }

    /**
     * Add Application
     */
    public function addApplication(Application $application): self
    {
        if (!$this->applications->contains($application)) {
            $this->applications[] = $application;
            $application->setVignette($this);
        }

        return $this;
    }

    /**
     * Remove Application
     */
    public function removeApplication(Application $application): self
    {
        if ($this->applications->contains($application)) {
            $this->applications->removeElement($application);
            if ($application->getVignette() === $this) {
                $application->setVignette(null);
            }
        }

        return $this;
    }

    /**
     * Set the value of applications
     */
    public function setApplications(Collection $applications): self
    {
        $this->applications = $applications;

        return $this;
    }
}
