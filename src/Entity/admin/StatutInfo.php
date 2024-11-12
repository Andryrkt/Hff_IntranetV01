<?php

namespace App\Entity\admin;

use App\Entity\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="statutinfo")
 * @ORM\HasLifecycleCallbacks
 */

 class StatutInfo
 {
    use DateTrait;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id_statut_info")
     */
    private $id;
    /**
     * @ORM\Column(type="string", length=50, name="libelle_statut_info")
     */
    private string $libelleStatutInfo;
    /**
     * @ORM\Column(type="string", length=3, name="type_application")
     */
    private string $typeApplication;

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of libelleStatutInfo
     */ 
    public function getLibelleStatutInfo()
    {
        return $this->libelleStatutInfo;
    }

    /**
     * Set the value of libelleStatutInfo
     *
     * @return  self
     */ 
    public function setLibelleStatutInfo($libelleStatutInfo)
    {
        $this->libelleStatutInfo = $libelleStatutInfo;

        return $this;
    }

    /**
     * Get the value of typeApplication
     */ 
    public function getTypeApplication()
    {
        return $this->typeApplication;
    }

    /**
     * Set the value of typeApplication
     *
     * @return  self
     */ 
    public function setTypeApplication($typeApplication)
    {
        $this->typeApplication = $typeApplication;

        return $this;
    }
 }