<?php

namespace App\Entity\admin;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\Application;
use App\Entity\admin\utilisateur\Profil;

/**
 * @ORM\Entity
 * @ORM\Table(name="application_profil")
 */
class ApplicationProfil
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Application::class, inversedBy="applicationProfils")
     * @ORM\JoinColumn(name="application_id", referencedColumnName="id", nullable=false)
     */
    private $application;

    /**
     * @ORM\ManyToOne(targetEntity=Profil::class, inversedBy="applicationProfils")
     * @ORM\JoinColumn(name="profil_id", referencedColumnName="id", nullable=false)
     */
    private $profil;

    public function __construct(?Profil $profil = null, ?Application $application = null)
    {
        $this->profil = $profil;
        $this->application = $application;
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
     * Get the value of application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Set the value of application
     */
    public function setApplication($application): self
    {
        $this->application = $application;

        return $this;
    }

    /**
     * Get the value of profil
     */
    public function getProfil()
    {
        return $this->profil;
    }

    /**
     * Set the value of profil
     */
    public function setProfil($profil): self
    {
        $this->profil = $profil;

        return $this;
    }
}
