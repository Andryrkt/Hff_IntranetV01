<?php

namespace App\Entity\admin\utilisateur;

use App\Repository\admin\utilisateur\ContactAgenceAteRepository;


/**
 * @ORM\Entity(repositoryClass=ContactAgenceAteRepository::class)
 * @ORM\Table(name="contact_agence_ate")
 */
class ContactAgenceAte
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=11 name="matricule")
     *
     * @var string
     */
    private string $agence;

    /**
     * @ORM\Column(type="string", length=5 name="matricule")
     *
     * @var string
     */
    private string $matricule;

    /**========================================================
     * GETTERS & SETTERS
     *=========================================================*/


    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of agence
     *
     * @return  string
     */ 
    public function getAgence()
    {
        return $this->agence;
    }

    /**
     * Set the value of agence
     *
     * @param  string  $agence
     *
     * @return  self
     */ 
    public function setAgence(string $agence)
    {
        $this->agence = $agence;

        return $this;
    }

    /**
     * Get the value of matricule
     *
     * @return  string
     */ 
    public function getMatricule()
    {
        return $this->matricule;
    }

    /**
     * Set the value of matricule
     *
     * @param  string  $matricule
     *
     * @return  self
     */ 
    public function setMatricule(string $matricule)
    {
        $this->matricule = $matricule;

        return $this;
    }
}