<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 * @ORM\HasLifecycleCallbacks
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Role", inversedBy="users")
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     */
    private $role;

    /**
     * @ORM\Column(type="string", length="255")
     *
     * @var [type]
     */
    private $nom_utilisateur;

    /**
     * @ORM\Column(type="integer")
     *
     * @var [type]
     */ 
    private $matricule;

    /**
     * @ORM\Column(type="string")
     *
     * @var [type]
     */
    private $mail;

    
    /**
     * @ORM\Column(type="date")
     *
     * @var [type]
     */
    private $date_creation;

    
    /**
     * @ORM\Column(type="date")
     *
     * @var [type]
     */
    private $date_modification;

    public function __construct()
    {
        $this->date_creation = new \DateTime();
    }

     /**
     * @ORM\PrePersist
     */
    public function onPrePersist(): void
    {
        $this->date_creation = new \DateTime();
        $this->date_modification = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function onPreUpdate(): void
    {
        $this->date_modification = new \DateTime();
    }

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of role
     */ 
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set the value of role
     *
     * @return  self
     */ 
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get the value of nom_utilisateur
     *
     * @return  [type]
     */ 
    public function getNomutilisateur()
    {
        return $this->nom_utilisateur;
    }

    /**
     * Set the value of nom_utilisateur
     *
     * @param  string  $nom_utilisateur
     *
     * @return  self
     */ 
    public function setNomutilisateur( $nom_utilisateur)
    {
        $this->nom_utilisateur = $nom_utilisateur;

        return $this;
    }

    /**
     * Get the value of matricule
     */ 
    public function getMatricule()
    {
        return $this->matricule;
    }

    /**
     * Set the value of matricule
     *
     * @return  self
     */ 
    public function setMatricule($matricule)
    {
        $this->matricule = $matricule;

        return $this;
    }

    
    public function getMail()
    {
        return $this->mail;
    }

    
    public function setMail( $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    public function getDatecreation()
    {
        return $this->date_creation;
    }


    public function setDatecreation( $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    /**
     * Get the value of date_modification
     *
     * @return  [type]
     */ 
    public function getDatemodification()
    {
        return $this->date_modification;
    }

    /**
     * Set the value of date_modification
     *
     * @param  [type]  $date_modification
     *
     * @return  self
     */ 
    public function setDatemodification( $date_modification)
    {
        $this->date_modification = $date_modification;

        return $this;
    }
}