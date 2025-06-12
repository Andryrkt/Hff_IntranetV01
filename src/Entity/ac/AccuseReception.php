<?php

namespace App\Entity\ac;

use DateTime;
use DateTimeZone;

class AccuseReception
{
    private DateTime $date;         // date du jour

    private string $numeroDIT;      // numero du DIT rattaché

    private string $nomClient;      // nom du client
    private string $emailClient;    // adresse email du client

    private string $numeroBC;       // numéro du Bon de commande
    private DateTime $dateBC;       // date du Bon de commande
    private string $descriptionBC;  // déscription du Bon de commande

    private string $numeroDevis;     // numéro du dernier devis soumis rattaché à la DIT 
    private string $statutDevis;     // statut du dernier devis soumis rattaché à la DIT 
    private DateTime $dateDevis;     // date du Devis dans IPS
    private float $montantDevis;     // montant total devis dans IPS


    private string $emailContactHff;  // adresse email chef d’atelier / chef d’agence
    private string $numTelContactHff; // numéro tel contact

    public function __construct()
    {
        $this->date = new DateTime('now', new DateTimeZone('Indian/Antananarivo'));
    }

    /**
     * Get the value of emailClient
     */
    public function getEmailClient()
    {
        return $this->emailClient;
    }

    /**
     * Set the value of emailClient
     *
     * @return  self
     */
    public function setEmailClient($emailClient)
    {
        $this->emailClient = $emailClient;

        return $this;
    }

    /**
     * Get the value of numTelContactHff
     */
    public function getNumTelContactHff()
    {
        return $this->numTelContactHff;
    }

    /**
     * Set the value of numTelContactHff
     *
     * @return  self
     */
    public function setNumTelContactHff($numTelContactHff)
    {
        $this->numTelContactHff = $numTelContactHff;

        return $this;
    }

    /**
     * Get the value of emailContactHff
     */
    public function getEmailContactHff()
    {
        return $this->emailContactHff;
    }

    /**
     * Set the value of emailContactHff
     *
     * @return  self
     */
    public function setEmailContactHff($emailContactHff)
    {
        $this->emailContactHff = $emailContactHff;

        return $this;
    }

    /**
     * Get the value of numeroDIT
     */
    public function getNumeroDIT()
    {
        return $this->numeroDIT;
    }

    /**
     * Set the value of numeroDIT
     *
     * @return  self
     */
    public function setNumeroDIT($numeroDIT)
    {
        $this->numeroDIT = $numeroDIT;

        return $this;
    }

    /**
     * Get the value of statutDevis
     */
    public function getStatutDevis()
    {
        return $this->statutDevis;
    }

    /**
     * Set the value of statutDevis
     *
     * @return  self
     */
    public function setStatutDevis($statutDevis)
    {
        $this->statutDevis = $statutDevis;

        return $this;
    }

    /**
     * Get the value of montantDevis
     */
    public function getMontantDevis()
    {
        return $this->montantDevis;
    }

    /**
     * Set the value of montantDevis
     *
     * @return  self
     */
    public function setMontantDevis($montantDevis)
    {
        $this->montantDevis = $montantDevis;

        return $this;
    }

    /**
     * Get the value of dateDevis
     */
    public function getDateDevis()
    {
        return $this->dateDevis;
    }

    /**
     * Set the value of dateDevis
     *
     * @return  self
     */
    public function setDateDevis($dateDevis)
    {
        $this->dateDevis = $dateDevis;

        return $this;
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
     * Get the value of descriptionBC
     */
    public function getDescriptionBC()
    {
        return $this->descriptionBC;
    }

    /**
     * Set the value of descriptionBC
     *
     * @return  self
     */
    public function setDescriptionBC($descriptionBC)
    {
        $this->descriptionBC = $descriptionBC;

        return $this;
    }

    /**
     * Get the value of dateBC
     */
    public function getDateBC()
    {
        return $this->dateBC;
    }

    /**
     * Set the value of dateBC
     *
     * @return  self
     */
    public function setDateBC($dateBC)
    {
        $this->dateBC = $dateBC;

        return $this;
    }

    /**
     * Get the value of numeroBC
     */
    public function getNumeroBC()
    {
        return $this->numeroBC;
    }

    /**
     * Set the value of numeroBC
     *
     * @return  self
     */
    public function setNumeroBC($numeroBC)
    {
        $this->numeroBC = $numeroBC;

        return $this;
    }

    /**
     * Get the value of nomClient
     */
    public function getNomClient()
    {
        return $this->nomClient;
    }

    /**
     * Set the value of nomClient
     *
     * @return  self
     */
    public function setNomClient($nomClient)
    {
        $this->nomClient = $nomClient;

        return $this;
    }

    /**
     * Get the value of date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set the value of date
     *
     * @return  self
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }
}
