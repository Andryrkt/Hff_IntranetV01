<?php

namespace App\Entity\admin\ddp;

class DdpSearch
{
    private $agence;
    private $service;
    private $typeDemande;
    private $numDdp;
    private $numCommande;
    private $numFacture;
    private $utilisateur;
    private $dateDebut;
    private $dateFin;
    private $statut;
    private $fournisseur;

    /**
     * Get the value of agence
     */
    public function getAgence()
    {
        return $this->agence;
    }

    /**
     * Set the value of agence
     *
     * @return  self
     */
    public function setAgence($agence)
    {
        $this->agence = $agence;

        return $this;
    }

    /**
     * Get the value of service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set the value of service
     *
     * @return  self
     */
    public function setService($service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get the value of typeDemande
     */
    public function getTypeDemande()
    {
        return $this->typeDemande;
    }

    /**
     * Set the value of typeDemande
     *
     * @return  self
     */
    public function setTypeDemande($typeDemande)
    {
        $this->typeDemande = $typeDemande;

        return $this;
    }

    /**
     * Get the value of numDdp
     */
    public function getNumDdp()
    {
        return $this->numDdp;
    }

    /**
     * Set the value of numDdp
     *
     * @return  self
     */
    public function setNumDdp($numDdp)
    {
        $this->numDdp = $numDdp;

        return $this;
    }

    /**
     * Get the value of numCommande
     */
    public function getNumCommande()
    {
        return $this->numCommande;
    }

    /**
     * Set the value of numCommande
     *
     * @return  self
     */
    public function setNumCommande($numCommande)
    {
        $this->numCommande = $numCommande;

        return $this;
    }

    /**
     * Get the value of numFacture
     */
    public function getNumFacture()
    {
        return $this->numFacture;
    }

    /**
     * Set the value of numFacture
     *
     * @return  self
     */
    public function setNumFacture($numFacture)
    {
        $this->numFacture = $numFacture;

        return $this;
    }

    /**
     * Get the value of utilisateur
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }

    /**
     * Set the value of utilisateur
     *
     * @return  self
     */
    public function setUtilisateur($utilisateur)
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * Get the value of dateDebut
     */
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * Set the value of dateDebut
     *
     * @return  self
     */
    public function setDateDebut($dateDebut)
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    /**
     * Get the value of dateFin
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    /**
     * Set the value of dateFin
     *
     * @return  self
     */
    public function setDateFin($dateFin)
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    /**
     * Get the value of statut
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set the value of statut
     *
     * @return  self
     */
    public function setStatut($statut)
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * Get the value of fournisseur
     */ 
    public function getFournisseur()
    {
        return $this->fournisseur;
    }

    /**
     * Set the value of fournisseur
     *
     * @return  self
     */ 
    public function setFournisseur($fournisseur)
    {
        $this->fournisseur = $fournisseur;

        return $this;
    }
}
