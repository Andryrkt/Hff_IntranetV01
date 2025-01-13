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

    private $agenceEmetteur;
    private $serviceEmetteur;
    private $agenceDebiteur;
    private $serviceDebiteur;

    

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

    /**
     * Get the value of agenceEmetteur
     */ 
    public function getAgenceEmetteur()
    {
        return $this->agenceEmetteur;
    }

    /**
     * Set the value of agenceEmetteur
     *
     * @return  self
     */ 
    public function setAgenceEmetteur($agenceEmetteur)
    {
        $this->agenceEmetteur = $agenceEmetteur;

        return $this;
    }

    /**
     * Get the value of serviceEmetteur
     */ 
    public function getServiceEmetteur()
    {
        return $this->serviceEmetteur;
    }

    /**
     * Set the value of serviceEmetteur
     *
     * @return  self
     */ 
    public function setServiceEmetteur($serviceEmetteur)
    {
        $this->serviceEmetteur = $serviceEmetteur;

        return $this;
    }

    /**
     * Get the value of agenceDebiteur
     */ 
    public function getAgenceDebiteur()
    {
        return $this->agenceDebiteur;
    }

    /**
     * Set the value of agenceDebiteur
     *
     * @return  self
     */ 
    public function setAgenceDebiteur($agenceDebiteur)
    {
        $this->agenceDebiteur = $agenceDebiteur;

        return $this;
    }

    /**
     * Get the value of serviceDebiteur
     */ 
    public function getServiceDebiteur()
    {
        return $this->serviceDebiteur;
    }

    /**
     * Set the value of serviceDebiteur
     *
     * @return  self
     */ 
    public function setServiceDebiteur($serviceDebiteur)
    {
        $this->serviceDebiteur = $serviceDebiteur;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'sousTypeDocument' => $this->sousTypeDocument,
            'statut' => $this->statut,
            'dateDebut' => $this->dateDebut,
            'dateFin' => $this->dateFin,
            'matricule' => $this->matricule,
            'dateMissionDebut' => $this->dateMissionDebut,
            'dateMissionFin' => $this->dateMissionFin,
            'agenceEmetteur' => $this->agenceEmetteur,
            'serviceEmetteur' => $this->serviceEmetteur,
            'agenceDebiteur' => $this->agenceDebiteur,
            'serviceDebiteur' => $this->serviceDebiteur,
            'numDom' => $this->numDom,
        ];
    }
}