<?php

namespace App\Entity;


use App\Entity\Service;
use App\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\AgenceRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="agences")
 * @ORM\Entity(repositoryClass=AgenceRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Agence
{
    use DateTrait;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column("integer", name="code_agence")
     *
     * @var integer
     */
    private int $codeAgence;

    /**
     * @ORM\Column("string", name="libelle_agence")
     *
     * @var string
     */
    private string $libelleAgence;

    /**
     * @ORM\ManyToMany(targetEntity=Service::class, inversedBy="agences")
     * @ORM\JoinTable(name="agence_service")
     */
    private $services;

    public function getId()
    {
        return $this->id;
    }


    public function getCodeAgence()
    {
        return $this->codeAgence;
    }

    public function setCodeAgence($codeAgence): self
    {
        $this->codeAgence = $codeAgence;

        return $this;
    }

 
    public function getLibelleAgence()
    {
        return $this->libelleAgence;
    }

    public function setLibelleAgence(string $libelleAgence): self
    {
        $this->libelleAgence = $libelleAgence;

        return $this;
    }

   
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services[] = $service;
        }

        return $this;
    }

    public function removeService(Service $service): self
    {
        if ($this->services->contains($service)) {
            $this->services->removeElement($service);
        }

        return $this;
    }
}
