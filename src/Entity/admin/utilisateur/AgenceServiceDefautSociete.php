<?php

namespace App\Entity\admin\utilisateur;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\admin\Societte;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\admin\utilisateur\AgenceServiceDefautSocieteRepository;

/**
 * @ORM\Entity
 * @ORM\Table(name="agence_service_defaut_societe")
 * @ORM\Entity(repositoryClass=AgenceServiceDefautSocieteRepository::class)
 */
class AgenceServiceDefautSociete
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="agenceServiceDefautSocietes")
     * @ORM\JoinColumn(name="id_user", nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", name="code_sage", nullable=true)
     */
    private $codeSage;

    /**
     * @ORM\ManyToOne(targetEntity=Societte::class)
     * @ORM\JoinColumn(name="id_societe", nullable=false)
     */
    private $societe;

    /**
     * @ORM\Column(type="string", name="code_agence", nullable=true)
     */
    private $codeAgence;

    /**
     * @ORM\Column(type="string", name="code_service", nullable=true)
     */
    private $codeService;

    /**
     * @ORM\ManyToOne(targetEntity=Agence::class)
     * @ORM\JoinColumn(name="id_agence", nullable=false)
     */
    private $agence;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class)
     * @ORM\JoinColumn(name="id_service", nullable=false)
     */
    private $service;

    public function getId()
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCodeSage(): ?string
    {
        return $this->codeSage;
    }

    public function setCodeSage(?string $codeSage): self
    {
        $this->codeSage = $codeSage;

        return $this;
    }

    public function getSociete(): ?Societte
    {
        return $this->societe;
    }

    public function setSociete(?Societte $societe): self
    {
        $this->societe = $societe;

        return $this;
    }

    public function getAgence(): ?Agence
    {
        return $this->agence;
    }

    public function setAgence(?Agence $agence): self
    {
        $this->agence = $agence;

        return $this;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get the value of codeAgence
     */
    public function getCodeAgence()
    {
        return $this->codeAgence;
    }

    /**
     * Set the value of codeAgence
     */
    public function setCodeAgence($codeAgence): self
    {
        $this->codeAgence = $codeAgence;

        return $this;
    }

    /**
     * Get the value of codeService
     */
    public function getCodeService()
    {
        return $this->codeService;
    }

    /**
     * Set the value of codeService
     */
    public function setCodeService($codeService): self
    {
        $this->codeService = $codeService;

        return $this;
    }
}
