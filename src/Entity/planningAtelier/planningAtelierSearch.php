<?php

namespace App\Entity\planningAtelier;

class planningAtelierSearch
{
    private  $numeroSemaine = null;
    private  $dateDebut = null;
    private  $dateFin = null;
    private $agenceEm;
    private $agenceDebite;
    private $serviceDebite;
    private $numOr;
    private $resource;
    private $section;
   

    /**
     * Get the value of numeroSemaine
     */ 
    public function getNumeroSemaine()
    {
        return $this->numeroSemaine;
    }

    /**
     * Set the value of numeroSemaine
     *
     * @return  self
     */ 
    public function setNumeroSemaine($numeroSemaine)
    {
        $this->numeroSemaine = $numeroSemaine;

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
     * Get the value of agenceEm
     */ 
    public function getAgenceEm()
    {
        return $this->agenceEm;
    }

    /**
     * Set the value of agenceEm
     *
     * @return  self
     */ 
    public function setAgenceEm($agenceEm)
    {
        $this->agenceEm = $agenceEm;

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
     * Get the value of resource
     */ 
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Set the value of resource
     *
     * @return  self
     */ 
    public function setResource($resource)
    {
        $this->resource = $resource;

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
}
