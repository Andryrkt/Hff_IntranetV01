<?php

namespace App\Entity\admin;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity
 * @ORM\Table(name="agence_service")
 */
class AgenceService
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Agence::class, inversedBy="agenceServices")
     * @ORM\JoinColumn(name="agence_id", referencedColumnName="id", nullable=false)
     */
    private $agence;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="agenceServices")
     * @ORM\JoinColumn(name="service_id", referencedColumnName="id", nullable=false)
     */
    private $service;

    /**=====================================================================================
     * 
     * GETTERS and SETTERS
     *
    =====================================================================================*/

    public function __construct(?Agence $agence = null, ?Service $service = null)
    {
        $this->agence = $agence;
        $this->service = $service;
    }

    public function getId()
    {
        return $this->id;
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
}
