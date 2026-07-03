<?php

namespace App\Entity\atelierRealise;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Repository\atelierRealise\AtelierRealiseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AtelierRealiseRepository::class)
 * @ORM\Table(name="agence_atelier_realise")
 */
class AtelierRealise
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=100, name="code_agence")
     */
    private string $codeAgence;

    /**
     * @ORM\Column(type="string", length=100, name="code_atelier")
     */
    private string $codeAtelier;

    /**
     * @ORM\ManyToOne(targetEntity=Agence::class)
     * @ORM\JoinColumn(name="agence_id", referencedColumnName="id")
     */
    private Agence $agence;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class)
     * @ORM\JoinColumn(name="service_id", referencedColumnName="id")
     */
    private Service $service;

    public function getId(): ?int
    {
        return $this->id;
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
     *
     * @return  self
     */
    public function setCodeAgence($codeAgence)
    {
        $this->codeAgence = $codeAgence;

        return $this;
    }

    /**
     * Get the value of codeAtelier
     */
    public function getCodeAtelier()
    {
        return $this->codeAtelier;
    }

    /**
     * Set the value of codeAtelier
     *
     * @return  self
     */
    public function setCodeAtelier($codeAtelier)
    {
        $this->codeAtelier = $codeAtelier;

        return $this;
    }

    /**
     * Get the value of agence
     */
    public function getAgence(): Agence
    {
        return $this->agence;
    }

    /**
     * Set the value of agence
     */
    public function setAgence(Agence $agence): self
    {
        $this->agence = $agence;

        return $this;
    }

    /**
     * Get the value of service
     */
    public function getService(): Service
    {
        return $this->service;
    }

    /**
     * Set the value of service
     */
    public function setService(Service $service): self
    {
        $this->service = $service;

        return $this;
    }
}
