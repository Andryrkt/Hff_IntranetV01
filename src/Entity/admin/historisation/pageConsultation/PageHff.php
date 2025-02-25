<?php

namespace App\Entity\admin\historisation\pageConsultation;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\admin\historisation\pageConsultation\UserLogger;
use App\Repository\admin\historisation\pageConsultation\PageHffRepository;

/** 
 * @ORM\Entity(repositoryClass=PageHffRepository::class)
 * @ORM\Table(name="Hff_pages")
 */
class PageHff
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
    private string $nom;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $lien;

    /**
     * @ORM\Column(type="string", length=255, nullable=false, name="nom_route")
     */
    private string $nomRoute;

    /** 
     * @ORM\OneToMany(targetEntity=UserLogger::class, mappedBy="page", cascade={"persist", "remove"})
     */
    private $userLoggers;

    //===================================================================================================

    public function __construct()
    {
        $this->userLoggers = new ArrayCollection();
    }

    /**
     * Get the value of lien
     */
    public function getLien()
    {
        return $this->lien;
    }

    /**
     * Set the value of lien
     *
     * @return  self
     */
    public function setLien($lien)
    {
        $this->lien = $lien;

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
     *
     * @return  self
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

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
     * Get the value of nomRoute
     */
    public function getNomRoute()
    {
        return $this->nomRoute;
    }

    /**
     * Set the value of nomRoute
     *
     * @return  self
     */
    public function setNomRoute($nomRoute)
    {
        $this->nomRoute = $nomRoute;

        return $this;
    }

    /**
     * Get the value of userLoggers
     */
    public function getUserLoggers(): ArrayCollection
    {
        return $this->userLoggers;
    }

    /**
     * Add value to userLoggers
     *
     * @return  self
     */
    public function addUserLogger(UserLogger $userLogger): self
    {
        $this->userLoggers[] = $userLogger;
        $userLogger->setPage($this); // Synchronisation inverse
        return $this;
    }

    /**
     * Set the value of userLoggers
     *
     * @return  self
     */
    public function setUserLoggers($userLoggers)
    {
        $this->userLoggers = $userLoggers;

        return $this;
    }
}
