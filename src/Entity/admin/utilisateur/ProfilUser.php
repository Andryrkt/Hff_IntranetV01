<?php

// src/Entity/ProfilUser.php

namespace App\Entity\admin\utilisateur;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\admin\utilisateur\ProfilUserRepository;

/**
 * @ORM\Entity
 * @ORM\Table(name="Profil_User")
 * @ORM\Entity(repositoryClass=ProfilUserRepository::class)
 */
class ProfilUser
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $utilisateur;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $profil;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $app;

    /**
     * @ORM\Column(type="integer")
     */
    private $matricule;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $mail;

      // Ajoutez ici les getters et setters pour chaque propriété
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    public function setUtilisateur($utilisateur)
    {
        $this->utilisateur = $utilisateur;
    }

    public function getProfil()
    {
        return $this->profil;
    }

    public function setProfil($profil)
    {
        $this->profil = $profil;
    }

    public function getApp()
    {
        return $this->app;
    }

    public function setApp($app)
    {
        $this->app = $app;
    }

    public function getMatricule()
    {
        return $this->matricule;
    }

    public function setMatricule($matricule)
    {
        $this->matricule = $matricule;
    }

    public function getMail()
    {
        return $this->mail;
    }

    public function setMail($mail)
    {
        $this->mail = $mail;
    }
}

// namespace App\Entity;

// class ProfilUserEntity
// {
//     // private $ID_Profil;
//     private $Utilisateur;
//     private $Profil;
//     private $App;
//     private $Matricule;
//     private $Mail;

//     // Ajoutez ici les getters et setters pour chaque propriété
//     // public function getIdProfil()
//     // {
//     //     return $this->ID_Profil;
//     // }

//     // public function setIdProfil($ID_Profil)
//     // {
//     //     $this->ID_Profil = $ID_Profil;
//     // }

//     public function getUtilisateur()
//     {
//         return $this->Utilisateur;
//     }

//     public function setUtilisateur($Utilisateur)
//     {
//         $this->Utilisateur = $Utilisateur;
//     }

//     public function getProfil()
//     {
//         return $this->Profil;
//     }

//     public function setProfil($Profil)
//     {
//         $this->Profil = $Profil;
//     }

//     public function getApp()
//     {
//         return $this->App;
//     }

//     public function setApp($App)
//     {
//         $this->App = $App;
//     }

//     public function getMatricule()
//     {
//         return $this->Matricule;
//     }

//     public function setMatricule($Matricule)
//     {
//         $this->Matricule = $Matricule;
//     }

//     public function getMail()
//     {
//         return $this->Mail;
//     }

//     public function setMail($Mail)
//     {
//         $this->Mail = $Mail;
//     }

// }