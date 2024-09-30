<?php

namespace App\Entity\dom;

class DomSearch
{
    private $statut;
    private $sousTypeDocument;
    private $numDom;
    private $matricule;
    private $dateDebut;
    private $dateFin;
    private $dateMissionDebut;
    private $dateMissionFin;

    

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
     * Get the value of sousTypeDocument
     */ 
    public function getSousTypeDocument()
    {
        return $this->sousTypeDocument;
    }

    /**
     * Set the value of sousTypeDocument
     *
     * @return  self
     */ 
    public function setSousTypeDocument($sousTypeDocument)
    {
        $this->sousTypeDocument = $sousTypeDocument;

        return $this;
    }

    /**
     * Get the value of numDom
     */ 
    public function getNumDom()
    {
        return $this->numDom;
    }

    /**
     * Set the value of numDom
     *
     * @return  self
     */ 
    public function setNumDom($numDom)
    {
        $this->numDom = $numDom;

        return $this;
    }

    /**
     * Get the value of matricule
     */ 
    public function getMatricule()
    {
        return $this->matricule;
    }

    /**
     * Set the value of matricule
     *
     * @return  self
     */ 
    public function setMatricule($matricule)
    {
        $this->matricule = $matricule;

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
     * Get the value of dateMissionDebut
     */ 
    public function getDateMissionDebut()
    {
        return $this->dateMissionDebut;
    }

    /**
     * Set the value of dateMissionDebut
     *
     * @return  self
     */ 
    public function setDateMissionDebut($dateMissionDebut)
    {
        $this->dateMissionDebut = $dateMissionDebut;

        return $this;
    }

    /**
     * Get the value of dateMissionFin
     */ 
    public function getDateMissionFin()
    {
        return $this->dateMissionFin;
    }

    /**
     * Set the value of dateMissionFin
     *
     * @return  self
     */ 
    public function setDateMissionFin($dateMissionFin)
    {
        $this->dateMissionFin = $dateMissionFin;

        return $this;
    }
}