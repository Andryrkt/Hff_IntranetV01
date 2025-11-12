<?php

namespace App\Entity\planningMagasin;

use App\Entity\admin\dit\WorNiveauUrgence;

class PlanningMagasinSearch
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
    private ?WorNiveauUrgence $niveauUrgence = null;
    private $section;
    private $months;
    private ?bool $orBackOrder = false;
    private $typeDocument;
    private $reparationRealise;
    private $orNonValiderDw;
    private $commercial;
    private $refCde;

    public function getNiveauUrgence()
    {
        return $this->niveauUrgence;
    }

    
    public function setNiveauUrgence($niveauUrgence): self
    {
        $this->niveauUrgence = $niveauUrgence;

        return $this;
    }

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

    /**
     * Get the value of section
     */ 
    public function getSection()
    {
        return $this->section;
    }

    /**
     * Set the value of section
     *
     * @return  self
     */ 
    public function setSection($section)
    {
        $this->section = $section;

        return $this;
    }


    /**
     * Get the value of months
     */ 
    public function getMonths()
    {
        return $this->months;
    }

    /**
     * Set the value of months
     *
     * @return  self
     */ 
    public function setMonths($months)
    {
        $this->months = $months;

        return $this;
    }

    /**
     * Get the value of orBackOrder
     */ 
    public function getOrBackOrder()
    {
        return $this->orBackOrder;
    }

    /**
     * Set the value of orBackOrder
     *
     * @return  self
     */ 
    public function setOrBackOrder($orBackOrder)
    {
        $this->orBackOrder = $orBackOrder;

        return $this;
    }

    /**
     * Get the value of typeDocument
     */ 
    public function getTypeDocument()
    {
        return $this->typeDocument;
    }

    /**
     * Set the value of typeDocument
     *
     * @return  self
     */ 
    public function setTypeDocument($typeDocument)
    {
        $this->typeDocument = $typeDocument;

        return $this;
    }

    /**
     * Get the value of reparationRealise
     */ 
    public function getReparationRealise()
    {
        return $this->reparationRealise;
    }

    /**
     * Set the value of reparationRealise
     *
     * @return  self
     */ 
    public function setReparationRealise($reparationRealise)
    {
        $this->reparationRealise = $reparationRealise;

        return $this;
    }

    /**
     * Get the value of orNonValiderDw
     */ 
    public function getOrNonValiderDw()
    {
        return $this->orNonValiderDw;
    }

    /**
     * Set the value of orNonValiderDw
     *
     * @return  self
     */ 
    public function setOrNonValiderDw($orNonValiderDw)
    {
        $this->orNonValiderDw = $orNonValiderDw;

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
            'typeligne' => $this->typeligne,
            'orBackOrder' => $this->orBackOrder,
            'orNonValiderDw' => $this->orNonValiderDw,
            'commercial'=> $this->commercial,
            'refClient'=>$this->refCde
        ];
    }

    /**
     * Get the value of commercial
     */ 
    public function getCommercial()
    {
        return $this->commercial;
    }

    /**
     * Set the value of commercial
     *
     * @return  self
     */ 
    public function setCommercial($commercial)
    {
        $this->commercial = $commercial;

        return $this;
    }
    /**
     * Get the value of refcde
     */ 
    public function getRefcde()
    {
        return $this->refCde;
    }

    /**
     * Set the value of commercial
     *
     * @return  self
     */ 
    public function setRefCde($refCde)
    {
        $this->refCde = $refCde;

        return $this;
    }
}