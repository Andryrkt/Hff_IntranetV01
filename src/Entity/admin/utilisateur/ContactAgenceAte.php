<?php

namespace App\Entity\admin\utilisateur;

use Doctrine\ORM\Mapping as ORM;
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
     * @ORM\Column(type="string", length=11, name="agence")
     *
     * @var string
     */
    private string $agence;

    /**
     * @ORM\Column(type="string", length=5, name="matricule")
     *
     * @var string
     */
    private string $matricule;

    private string $nomPrenom;

    private ?string $poste;

    private string $email;

    private string $telephone;

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

    /**
     * Get the value of nomPrenom
     */ 
    public function getNomPrenom()
    {
        return $this->nomPrenom;
    }

    /**
     * Set the value of nomPrenom
     *
     * @return  self
     */ 
    public function setNomPrenom($nomPrenom)
    {
        $this->nomPrenom = $nomPrenom;

        return $this;
    }

    /**
     * Get the value of poste
     */ 
    public function getPoste()
    {
        return $this->poste;
    }

    /**
     * Set the value of poste
     *
     * @return  self
     */ 
    public function setPoste($poste)
    {
        $this->poste = $poste;

        return $this;
    }

    /**
     * Get the value of email
     */ 
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */ 
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of telephone
     */ 
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * Set the value of telephone
     *
     * @return  self
     */ 
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;

        return $this;
    }
}