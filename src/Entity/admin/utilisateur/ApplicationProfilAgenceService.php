<?php

namespace App\Entity\admin\utilisateur;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\AgenceService;
use App\Entity\admin\ApplicationProfil;

/**
 * @ORM\Entity
 * @ORM\Table(name="application_profil_agence_service")
 */
class ApplicationProfilAgenceService
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=ApplicationProfil::class)
     * @ORM\JoinColumn(name="application_profil_id", referencedColumnName="id", nullable=false)
     */
    private ApplicationProfil $applicationProfil;

    /**
     * @ORM\ManyToOne(targetEntity=AgenceService::class)
     * @ORM\JoinColumn(name="agence_service_id", referencedColumnName="id", nullable=false)
     */
    private AgenceService $agenceService;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApplicationProfil(): ?ApplicationProfil
    {
        return $this->applicationProfil;
    }

    public function setApplicationProfil(ApplicationProfil $applicationProfil): self
    {
        $this->applicationProfil = $applicationProfil;

        return $this;
    }

    public function getAgenceService(): ?AgenceService
    {
        return $this->agenceService;
    }

    public function setAgenceService(AgenceService $agenceService): self
    {
        $this->agenceService = $agenceService;

        return $this;
    }
}
