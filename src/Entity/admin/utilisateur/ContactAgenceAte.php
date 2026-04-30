<?php

namespace App\Entity\admin\utilisateur;

use App\Entity\admin\Agence;
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
     */
    private ?string $agenceString;

    /**
     * @ORM\Column(type="string", length=5, name="matricule")
     */
    private ?string $matriculeString;

    /**
     * @ORM\Column(type="string", length=100, name="nom")
     */
    private ?string $nomString = "";

    /**
     * @ORM\Column(type="string", length=200, name="prenom")
     */
    private ?string $prenom = "";

    /**
     * @ORM\Column(type="string", length=255, name="email")
     */
    private ?string  $emailString = "";

    /**
     * @ORM\Column(type="string", length=13, name="telephone")
     */
    private ?string $telephone = "";

    /**
     * @ORM\Column(type="string", length=10, name="atelier")
     */
    private ?string $atelier = "";

    private ?Agence $agence = null;

    private ?User $matricule = null;

    private string $nomPrenom = "";

    private ?user $email = null;

    private ?User $nom = null;

    private string $poste = "";


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


    public function setAgence($agence)
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

    public function setMatricule($matricule)
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
     * Get the value of agenceString
     */ 
    public function getAgenceString()
    {
        return $this->agenceString;
    }

    /**
     * Set the value of agenceString
     *
     * @return  self
     */ 
    public function setAgenceString($agenceString)
    {
        $this->agenceString = $agenceString;

        return $this;
    }

    /**
     * Get the value of matriculeString
     */ 
    public function getMatriculeString()
    {
        return $this->matriculeString;
    }

    /**
     * Set the value of matriculeString
     *
     * @return  self
     */ 
    public function setMatriculeString($matriculeString)
    {
        $this->matriculeString = $matriculeString;

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

    /**
     * Get the value of emailString
     */ 
    public function getEmailString()
    {
        return $this->emailString;
    }

    /**
     * Set the value of emailString
     *
     * @return  self
     */ 
    public function setEmailString($emailString)
    {
        $this->emailString = $emailString;

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
     * Get the value of nomString
     */ 
    public function getNomString()
    {
        return $this->nomString;
    }

    /**
     * Set the value of nomString
     *
     * @return  self
     */ 
    public function setNomString($nomString)
    {
        $this->nomString = $nomString;

        return $this;
    }

    /**
     * Get the value of prenom
     */ 
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set the value of prenom
     *
     * @return  self
     */ 
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get the value of atelier
     */ 
    public function getAtelier()
    {
        return $this->atelier;
    }

    /**
     * Set the value of atelier
     *
     * @return  self
     */ 
    public function setAtelier($atelier)
    {
        $this->atelier = $atelier;

        return $this;
    }
}