<?php

namespace App\Entity;

class ProfilUserEntity
{

    private string $utilisateur;
    private string $profil;
    private string $app;
    private int $matricule;
    private string $mail;

    public function getUtilisateur(): string
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(string $utilisateur): self
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getProfil(): string
    {
        return $this->profil;
    }

    public function setProfil(string $profil): self
    {
        $this->profil = $profil;
        return $this;
    }

    public function getApp(): string
    {
        return $this->app;
    }

    public function setApp(string $app): self
    {
        $this->app = $app;
        return $this;
    }

    public function getMatricule(): int
    {
        return $this->matricule;
    }

    public function setMatricule(int $matricule): self
    {
    $this->matricule = $matricule;
    return $this;
    }

    public function getEmail(): string
    {
        return $this->mail;
    }

    public function setEmail(string $email): self
    {
        $this->mail = $email;
        return $this;
    }

}