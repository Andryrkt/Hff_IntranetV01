<?php

namespace App\Entity;


use App\Entity\Agence;
use App\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\AgenceRepository;
use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="services")
 * @ORM\Entity(repositoryClass=ServiceRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Service
{
    use DateTrait;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column("string", name="code_service")
     *
     * @var string
     */
    private string $codeService;

    /**
     * @ORM\Column("string", name="libelle_service")
     *
     * @var string
     */
    private string $libelleService;

/**
     * @ORM\ManyToMany(targetEntity=Agence::class, mappedBy="services")
     
     */
    private Collection $agences;

    public function __construct()
    {
        $this->agences = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }



    public function getCodeService()
    {
        return $this->codeService;
    }

  
    public function setCodeService($codeService): self
    {
        $this->codeService = $codeService;

        return $this;
    }


    public function getLibelleService()
    {
        return $this->libelleService;
    }

  
    public function setLibelleService(string $libelleService): self
    {
        $this->libelleService = $libelleService;

        return $this;
    }


    
  
    public function getAgences(): Collection
    {
        return $this->agences;
    }

    public function addAgence(Agence $agence): self
    {
        if(!$this->agences->contains($agence)){
            $this->agences[] = $agence;
            $agence->addService($this);
        }
        return $this;
    }

    public function removeAgence(Agence $agence): self
    {
        if($this->agences->contains($agence)) {
            $this->agences->removeElement($agence);
          $agence->removeService($this);
        }
        return $this;
    }
}
