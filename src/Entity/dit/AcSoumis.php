<?php

namespace App\Entity\dit;

class AcSoumis
{
    private $dateCreation;

    private $nomClient;

    private $emailClient;

    private $numeroBc;

    private $dateBc;

    private $descriptionBc = '';

    private $numeroDevis;

    private $statutDevis;

    private $dateDevis;

    private $montantDevis;
    
    private $numeroDit;

    private $emailContactHff = '';

    private $telephoneContactHff = '';

    private $pieceJoint01;

    private $dateExpirationDevis;

    private $devise = '';

    private $numeroVersion = 0;

    /** ===================================================================================================================
     * 
     * GETTER and SETTER
     * 
     *===============================================================================================================*/


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
     * Get the value of numeroBc
     */ 
    public function getNumeroBc()
    {
        return $this->numeroBc;
    }

    /**
     * Set the value of numeroBc
     *
     * @return  self
     */ 
    public function setNumeroBc($numeroBc)
    {
        $this->numeroBc = $numeroBc;

        return $this;
    }

    /**
     * Get the value of dateBc
     */ 
    public function getDateBc()
    {
        return $this->dateBc;
    }

    /**
     * Set the value of dateBc
     *
     * @return  self
     */ 
    public function setDateBc($dateBc)
    {
        $this->dateBc = $dateBc;

        return $this;
    }

    /**
     * Get the value of descriptionBc
     */ 
    public function getDescriptionBc()
    {
        return $this->descriptionBc;
    }

    /**
     * Set the value of descriptionBc
     *
     * @return  self
     */ 
    public function setDescriptionBc($descriptionBc)
    {
        $this->descriptionBc = $descriptionBc;

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
     * Get the value of numeroDit
     */ 
    public function getNumeroDit()
    {
        return $this->numeroDit;
    }

    /**
     * Set the value of numeroDit
     *
     * @return  self
     */ 
    public function setNumeroDit($numeroDit)
    {
        $this->numeroDit = $numeroDit;

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
     * Get the value of telephoneContactHff
     */ 
    public function getTelephoneContactHff()
    {
        return $this->telephoneContactHff;
    }

    /**
     * Set the value of telephoneContactHff
     *
     * @return  self
     */ 
    public function setTelephoneContactHff($telephoneContactHff)
    {
        $this->telephoneContactHff = $telephoneContactHff;

        return $this;
    }

    /**
     * Get the value of pieceJoint01
     */ 
    public function getPieceJoint01()
    {
        return $this->pieceJoint01;
    }

    /**
     * Set the value of pieceJoint01
     *
     * @return  self
     */ 
    public function setPieceJoint01($pieceJoint01)
    {
        $this->pieceJoint01 = $pieceJoint01;

        return $this;
    }

    /**
     * Get the value of dateExpirationDevis
     */ 
    public function getDateExpirationDevis()
    {
        return $this->dateExpirationDevis;
    }

    /**
     * Set the value of dateExpirationDevis
     *
     * @return  self
     */ 
    public function setDateExpirationDevis($dateExpirationDevis)
    {
        $this->dateExpirationDevis = $dateExpirationDevis;

        return $this;
    }

    /**
     * Get the value of devise
     */ 
    public function getDevise()
    {
        return $this->devise;
    }

    /**
     * Set the value of devise
     *
     * @return  self
     */ 
    public function setDevise($devise)
    {
        $this->devise = $devise;

        return $this;
    }

    /**
     * Get the value of numeroVersion
     */ 
    public function getNumeroVersion()
    {
        return $this->numeroVersion;
    }

    /**
     * Set the value of numeroVersion
     *
     * @return  self
     */ 
    public function setNumeroVersion($numeroVersion)
    {
        $this->numeroVersion = $numeroVersion;

        return $this;
    }
}