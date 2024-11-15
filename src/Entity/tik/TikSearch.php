<?php

namespace App\Entity\tik;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\admin\dit\WorNiveauUrgence;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\tik\TkiAutresCategorie;
use App\Entity\admin\tik\TkiCategorie;
use App\Entity\admin\tik\TkiSousCategorie;

class TikSearch
{
    private ?string $numeroTicket = '';

    private ?string $demandeur = '';

    private ?string $numParc = '';

    private ?StatutDemande $statut = null;

    private ?WorNiveauUrgence $niveauUrgence = null;

    private ?\Datetime $dateDebut = null;

    private ?\Datetime $dateFin = null;

    private ?Agence $agenceEmetteur = null;

    private ?Service $serviceEmetteur = null;

    private ?Agence $agenceDebiteur = null;

    private ?Service $serviceDebiteur = null;

    private ?TkiCategorie $categorie = null;

    private ?TkiSousCategorie $sousCategorie = null;

    private ?TkiAutresCategorie $autresCategories = null;

    /**=====================================================================================
     * 
     * GETTERS and SETTERS
     *
    =====================================================================================*/
    
    /**
     * Get the value of numeroTicket
     */ 
    public function getNumeroTicket()
    {
        return $this->numeroTicket;
    }

    /**
     * Set the value of numeroTicket
     *
     * @return  self
     */ 
    public function setNumeroTicket($numeroTicket)
    {
        $this->numeroTicket = $numeroTicket;

        return $this;
    }

    /**
     * Get the value of demandeur
     */ 
    public function getDemandeur()
    {
        return $this->demandeur;
    }

    /**
     * Set the value of demandeur
     *
     * @return  self
     */ 
    public function setDemandeur($demandeur)
    {
        $this->demandeur = $demandeur;

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
     * Get the value of niveauUrgence
     */ 
    public function getNiveauUrgence()
    {
        return $this->niveauUrgence;
    }

    /**
     * Set the value of niveauUrgence
     *
     * @return  self
     */ 
    public function setNiveauUrgence($niveauUrgence)
    {
        $this->niveauUrgence = $niveauUrgence;

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

    /**
     * Get the value of categorie
     */ 
    public function getCategorie()
    {
        return $this->categorie;
    }

    /**
     * Set the value of categorie
     *
     * @return  self
     */ 
    public function setCategorie($categorie)
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * Get the value of sousCategorie
     */ 
    public function getSousCategorie()
    {
        return $this->sousCategorie;
    }

    /**
     * Set the value of sousCategorie
     *
     * @return  self
     */ 
    public function setSousCategorie($sousCategorie)
    {
        $this->sousCategorie = $sousCategorie;

        return $this;
    }

    /**
     * Get the value of autresCategories
     */ 
    public function getAutresCategories()
    {
        return $this->autresCategories;
    }

    /**
     * Set the value of autresCategories
     *
     * @return  self
     */ 
    public function setAutresCategories($autresCategories)
    {
        $this->autresCategories = $autresCategories;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'niveauUrgence' => $this->niveauUrgence,
            'statut' => $this->statut,
            'dateDebut' => $this->dateDebut,
            'dateFin' => $this->dateFin,
            'numParc' => $this->numParc,
            'agenceEmetteur' => $this->agenceEmetteur,
            'serviceEmetteur' => $this->serviceEmetteur,
            'agenceDebiteur' => $this->agenceDebiteur,
            'serviceDebiteur' => $this->serviceDebiteur,
        ];
    }
}