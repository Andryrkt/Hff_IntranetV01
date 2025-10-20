<?php

namespace App\Factory\magasin\devis;

use App\Factory\Traits\ArrayableTrait;

class ListeDevisSearchDto
{
    use ArrayableTrait;

    private ?string $numeroDevis = null;
    private ?string $codeClient = null;
    private ?string $Operateur = null;
    private ?string $statutDw = null;
    private ?string $statutIps = null;
    private ?array $emetteur = [];
    private ?array $dateCreation = [];

    /** ============================================================
     * getter and setter
     *============================================================*/

    /**
     * Transforme l'objet en tableau en filtrant les propriétés nulles ou vides
     */
    public function toArrayFilter(): array
    {
        // Utilise le trait avec des paramètres spécifiques si besoin
        return $this->toArray(); // Toutes les propriétés
        // ou return $this->toArray(['numeroDevis', 'codeClient']); // Seulement certaines
        // ou return $this->toArray([], ['password']); // Toutes sauf certaines
    }

    /**
     * Get the value of numeroDevis
     */
    public function getNumeroDevis()
    {
        return $this->numeroDevis;
    }

    /**
     * Set the value of numeroDevis
     *
     * @return  self
     */
    public function setNumeroDevis($numeroDevis)
    {
        $this->numeroDevis = $numeroDevis;

        return $this;
    }

    /**
     * Get the value of codeClient
     */
    public function getCodeClient()
    {
        return $this->codeClient;
    }

    /**
     * Set the value of codeClient
     *
     * @return  self
     */
    public function setCodeClient($codeClient)
    {
        $this->codeClient = $codeClient;

        return $this;
    }

    /**
     * Get the value of Operateur
     */
    public function getOperateur()
    {
        return $this->Operateur;
    }

    /**
     * Set the value of Operateur
     *
     * @return  self
     */
    public function setOperateur($Operateur)
    {
        $this->Operateur = $Operateur;

        return $this;
    }

    /**
     * Get the value of statutDw
     */
    public function getStatutDw()
    {
        return $this->statutDw;
    }

    /**
     * Set the value of statutDw
     *
     * @return  self
     */
    public function setStatutDw($statutDw)
    {
        $this->statutDw = $statutDw;

        return $this;
    }

    /**
     * Get the value of statutIps
     */
    public function getStatutIps()
    {
        return $this->statutIps;
    }

    /**
     * Set the value of statutIps
     *
     * @return  self
     */
    public function setStatutIps($statutIps)
    {
        $this->statutIps = $statutIps;

        return $this;
    }

    /**
     * Get the value of emetteur
     */
    public function getEmetteur()
    {
        return $this->emetteur;
    }

    /**
     * Set the value of emetteur
     *
     * @return  self
     */
    public function setEmetteur($emetteur)
    {
        $this->emetteur = $emetteur;

        return $this;
    }

    /**
     * Get the value of dateCreation
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set the value of dateCreation
     *
     * @return  self
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }
}
