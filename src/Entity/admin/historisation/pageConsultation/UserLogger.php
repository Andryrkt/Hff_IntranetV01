<?php

namespace App\Entity\admin\historisation\pageConsultation;

use DateTime;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\admin\utilisateur\User;
use App\Entity\admin\historisation\pageConsultation\PageHff;
use App\Repository\admin\historisation\pageConsultation\UserLoggerRepository;

/** 
 * @ORM\Entity(repositoryClass=UserLoggerRepository::class)
 * @ORM\Table(name="Log_utilisateur")
 * @ORM\HasLifecycleCallbacks
 */
class UserLogger
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $utilisateur;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $nom_page;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $params = [];

    /**
     * @ORM\Column(type="datetime", name="date_heure_consultation")
     */
    private $dateConsultation;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="userLoggers")
     * @ORM\JoinColumn(name="id_utilisateur", referencedColumnName="id")
     * 
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=PageHff::class, inversedBy="userLoggers")
     * @ORM\JoinColumn(name="id_page", nullable=false)
     */
    private $page;

    /**
     * @ORM\Column(type="string", name="machine_utilisateur",length=255, nullable=false)
     */
    private string $machineUser;
    //============================================================================================

    public function __construct()
    {
        $this->dateConsultation = new DateTime();
    }

    /**
     * Get the value of dateConsultation
     */
    public function getDateConsultation()
    {
        return $this->dateConsultation;
    }

    /**
     * Set the value of dateConsultation
     *
     * @return  self
     */
    public function setDateConsultation($dateConsultation)
    {
        $this->dateConsultation = $dateConsultation;

        return $this;
    }

    /**
     * Get the value of params
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set the value of params
     *
     * @return  self
     */
    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Get the value of nom_page
     */
    public function getNom_page()
    {
        return $this->nom_page;
    }

    /**
     * Set the value of nom_page
     *
     * @return  self
     */
    public function setNom_page($nom_page)
    {
        $this->nom_page = $nom_page;

        return $this;
    }

    /**
     * Get the value of utilisateur
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * Set the value of utilisateur
     *
     * @return  self
     */
    public function setUtilisateur($utilisateur)
    {
        $this->utilisateur = $utilisateur;

        return $this;
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
     *
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist(): void
    {
        $timezone = new DateTimeZone('Indian/Antananarivo');
        $this->dateConsultation = new DateTime('now', $timezone);
    }

    /**
     * Get the value of user
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @return  self
     */
    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the value of page
     */
    public function getPage(): ?PageHff
    {
        return $this->page;
    }

    /**
     * Set the value of page
     *
     * @return  self
     */
    public function setPage(PageHff $page): self
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get the value of machineUser
     */
    public function getMachineUser()
    {
        return $this->machineUser;
    }

    /**
     * Set the value of machineUser
     *
     * @return  self
     */
    public function setMachineUser($machineUser)
    {
        $this->machineUser = $machineUser;

        return $this;
    }
}
