<?php

namespace App\Entity\planning;

class PlanningSearch

{
    private $agence;
    private $annee;
    private $interneExterne;
    private $facture;
    private $plan;
    private $dateDebut;
    private $dateFin;
    private $numOr;
    private $numSerie;
    private $idMat;
    private $numParc;
    private $agenceDebite;
    private $serviceDebite;
    private $typeligne;
    private $casier;
    

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
     * Get the value of annee
     */ 
    public function getAnnee()
    {
        return $this->annee;
    }

    /**
     * Set the value of annee
     *
     * @return  self
     */ 
    public function setAnnee($annee)
    {
        $this->annee = $annee;

        return $this;
    }

    /**
     * Get the value of interneExterne
     */ 
    public function getInterneExterne()
    {
        return $this->interneExterne;
    }

    /**
     * Set the value of interneExterne
     *
     * @return  self
     */ 
    public function setInterneExterne($interneExterne)
    {
        $this->interneExterne = $interneExterne;

        return $this;
    }

    /**
     * Get the value of facture
     */ 
    public function getFacture()
    {
        return $this->facture;
    }

    /**
     * Set the value of facture
     *
     * @return  self
     */ 
    public function setFacture($facture)
    {
        $this->facture = $facture;

        return $this;
    }

    /**
     * Get the value of plan
     */ 
    public function getPlan()
    {
        return $this->plan;
    }

    /**
     * Set the value of plan
     *
     * @return  self
     */ 
    public function setPlan($plan)
    {
        $this->plan = $plan;

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
     * Get the value of numOr
     */ 
    public function getNumOr()
    {
        return $this->numOr;
    }

    /**
     * Set the value of numOr
     *
     * @return  self
     */ 
    public function setNumOr($numOr)
    {
        $this->numOr = $numOr;

        return $this;
    }

    /**
     * Get the value of numSerie
     */ 
    public function getNumSerie()
    {
        return $this->numSerie;
    }

    /**
     * Set the value of numSerie
     *
     * @return  self
     */ 
    public function setNumSerie($numSerie)
    {
        $this->numSerie = $numSerie;

        return $this;
    }

    /**
     * Get the value of idMat
     */ 
    public function getIdMat()
    {
        return $this->idMat;
    }

    /**
     * Set the value of idMat
     *
     * @return  self
     */ 
    public function setIdMat($idMat)
    {
        $this->idMat = $idMat;

        return $this;
    }

    /**
     * Get the value of numParc
     */ 
    public function getNumParc()
    {
        return $this->numParc;
    }

    /**
     * Set the value of numParc
     *
     * @return  self
     */ 
    public function setNumParc($numParc)
    {
        $this->numParc = $numParc;

        return $this;
    }

    /**
     * Get the value of agenceDebite
     */ 
    public function getAgenceDebite()
    {
        return $this->agenceDebite;
    }

    /**
     * Set the value of agenceDebite
     *
     * @return  self
     */ 
    public function setAgenceDebite($agenceDebite)
    {
        $this->agenceDebite = $agenceDebite;

        return $this;
    }

    /**
     * Get the value of serviceDebite
     */ 
    public function getServiceDebite()
    {
        return $this->serviceDebite;
    }

    /**
     * Set the value of serviceDebite
     *
     * @return  self
     */ 
    public function setServiceDebite($serviceDebite)
    {
        $this->serviceDebite = $serviceDebite;

        return $this;
    }

    
    /**
     * Get the value of typeLigne
     */ 
    public function getTypeLigne()
    {
        return $this->typeligne;
    }

    /**
     * Set the value of typeLigne
     *
     * @return  self
     */ 
    public function setTypeLigne($typeligne)
    {
        $this->typeligne = $typeligne;

        return $this;
    }
    public function toArray(): array
    {
        return [
            'agence' => $this->agence,
            'annee' => $this->annee,
            'interneExterne' => $this->interneExterne,
            'facture' => $this->facture,
            'plan' => $this->plan,
            'dateDebut' => $this->dateDebut,
            'dateFin' => $this->dateFin,
            'numOr' => $this->numOr,
            'numSerie' => $this->numSerie,
            'idMat' => $this->idMat,
            'numParc' => $this->numParc,
            'agenceDebite' => $this->agenceDebite,
            'serviceDebite' => $this->serviceDebite,
            'typeligne' => $this->typeligne
           
        ];
    }

    /**
     * Get the value of casier
     */ 
    public function getCasier()
    {
        return $this->casier;
    }

    /**
     * Set the value of casier
     *
     * @return  self
     */ 
    public function setCasier($casier)
    {
        $this->casier = $casier;

        return $this;
    }
}