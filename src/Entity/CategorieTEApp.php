<?php

namespace App\Entity;

use App\Entity\Role;
use App\Traits\DateTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="categorie_ate_app")
 * @ORM\HasLifecycleCallbacks
 */

class CategorieATEAPP
{
    use DateTrait;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id_categorie_ate_app")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, name="libelle_categorie_ate_app")
     */
    private string $libelleCategorieAteApp;
    /**
     * @ORM\Column(type="string", length=3, name= "type_application")
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
     * Get the value of libelleCategorieAteApp
     */ 
    public function getLibelleCategorieAteApp()
    {
        return $this->libelleCategorieAteApp;
    }

    /**
     * Set the value of libelleCategorieAteApp
     *
     * @return  self
     */ 
    public function setLibelleCategorieAteApp($libelleCategorieAteApp)
    {
        $this->libelleCategorieAteApp = $libelleCategorieAteApp;

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
