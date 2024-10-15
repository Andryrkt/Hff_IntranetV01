<?php

namespace App\Entity\dw;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\dw\DwArmoireRepository;


/**
 * @ORM\Entity(repositoryClass=DwArmoireRepository::class)
 * @ORM\Table(name="DW_Armoire")
 * @ORM\HasLifecycleCallbacks
 */
class DwArmoire
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id")
     */
    private int $id;


    /**
     * @ORM\Column(type="string", length=255,name="Nom")
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=50,name="Couleur")
     */
    private $couleur;

    /**
 * @ORM\Column(type="boolean", name="ParDefaut")
 */
    private $parDefaut;

    /** ===========================================================================
 * getteur and setteur
 *
 * ================================================================================
 */

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
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
     * Get the value of couleur
     */ 
    public function getCouleur()
    {
        return $this->couleur;
    }

    /**
     * Set the value of couleur
     *
     * @return  self
     */ 
    public function setCouleur($couleur)
    {
        $this->couleur = $couleur;

        return $this;
    }

    /**
     * Get the value of parDefaut
     */ 
    public function getParDefaut()
    {
        return $this->parDefaut;
    }

    /**
     * Set the value of parDefaut
     *
     * @return  self
     */ 
    public function setParDefaut($parDefaut)
    {
        $this->parDefaut = $parDefaut;

        return $this;
    }
}