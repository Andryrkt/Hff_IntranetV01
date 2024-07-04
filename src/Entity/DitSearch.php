<?php

namespace App\Entity;

class DitSearch
{
      /**
     * Undocumented variable
     *
     * @var WorNiveauUrgence|null
     */
    private ?WorNiveauUrgence $niveauUrgence;

    /**
     * Undocumented variable
     *
     * @var StatutDemande|null
     */
    private ?StatutDemande $statut;

    /**
     * @var int|null
     */
    private ?int $idMateriel;

    /**
     * Undocumented variable
     *
     * @var WorTypeDocument|null
     */
    private ?WorTypeDocument $typeDocument;

    /**
     * Undocumented variable
     *
     * @var string|null
     */
    private ?string $internetExterne;

    
    /**
     * @var \DateTime|null
     */
    private ?\Datetime $dateDebut;

    /**
     * @var \DateTime|null
     */
    private ?\DateTime $dateFin;

    /**
     * @var string|null
     */
    private ?string $numParc = '';

    /**
     * @var string|null
     */
    private ?string $numSerie = '';


    /**
     * @var Agence|null
     */
    private ?Agence $agenceEmetteur = null;

    /**
     * Undocumented variable
     *
     * @var Service|null
     */
    private ?Service $serviceEmetteur = null;

    /**
     * Undocumented variable
     *
     * @var Agence|null
     */
    private ?Agence $agenceDebiteur = null;

    /**
     * Undocumented variable
     *
     * @var Service|null
     */
    private ?Service $serviceDebiteur = null;

  

    

    

    

    

    

    /**
     * Get undocumented variable
     *
     * @return  WorNiveauUrgence|null
     */ 
    public function getNiveauUrgence()
    {
        return $this->niveauUrgence;
    }

    /**
     * Set undocumented variable
     *
     * @param  WorNiveauUrgence|null  $niveauUrgence  Undocumented variable
     *
     * @return  self
     */ 
    public function setNiveauUrgence($niveauUrgence)
    {
        $this->niveauUrgence = $niveauUrgence;

        return $this;
    }

    /**
     * Get undocumented variable
     *
     * @return  StatutDemande|null
     */ 
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set undocumented variable
     *
     * @param  StatutDemande|null  $statutDemande  Undocumented variable
     *
     * @return  self
     */ 
    public function setStatut($statutDemande)
    {
        $this->statut = $statutDemande;

        return $this;
    }

    /**
     * Get the value of idMateriel
     *
     * @return  int|null
     */ 
    public function getIdMateriel()
    {
        return $this->idMateriel;
    }

    /**
     * Set the value of idMateriel
     *
     * @param  int|null  $idMateriel
     *
     * @return  self
     */ 
    public function setIdMateriel($idMateriel)
    {
        $this->idMateriel = $idMateriel;

        return $this;
    }

    /**
     * Get undocumented variable
     *
     * @return  WorTypeDocument|null
     */ 
    public function getTypeDocument()
    {
        return $this->typeDocument;
    }

    /**
     * Set undocumented variable
     *
     * @param  WorTypeDocument|null  $typeDocument  Undocumented variable
     *
     * @return  self
     */ 
    public function setTypeDocument($typeDocument)
    {
        $this->typeDocument = $typeDocument;

        return $this;
    }

    /**
     * Get undocumented variable
     *
     * @return  string|null
     */ 
    public function getInternetExterne()
    {
        return $this->internetExterne;
    }

    /**
     * Set undocumented variable
     *
     * @param  string|null  $interneExterne  Undocumented variable
     *
     * @return  self
     */ 
    public function setInternetExterne($interneExterne)
    {
        $this->internetExterne = $interneExterne;

        return $this;
    }

    /**
     * Get the value of dateDebut
     *
     * @return  \DateTime|null
     */ 
    public function getDateDebut()
    {
        return $this->dateDebut;
    }

    /**
     * Set the value of dateDebut
     *
     * @param  \DateTime|null  $dateDebut
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
     *
     * @return  \DateTime|null
     */ 
    public function getDateFin()
    {
        return $this->dateFin;
    }

    /**
     * Set the value of dateFin
     *
     * @param  \DateTime|null  $dateFin
     *
     * @return  self
     */ 
    public function setDateFin($dateFin)
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    /**
     * Get the value of numParc
     *
     * @return  string|null
     */ 
    public function getNumParc()
    {
        return $this->numParc;
    }

    /**
     * Set the value of numParc
     *
     * @param  string|null  $numParc
     *
     * @return  self
     */ 
    public function setNumParc($numParc)
    {
        $this->numParc = $numParc;

        return $this;
    }

    /**
     * Get the value of numSerie
     *
     * @return  string|null
     */ 
    public function getNumSerie()
    {
        return $this->numSerie;
    }

    /**
     * Set the value of numSerie
     *
     * @param  string|null  $numSerie
     *
     * @return  self
     */ 
    public function setNumSerie($numSerie)
    {
        $this->numSerie = $numSerie;

        return $this;
    }

    /**
     * Get the value of agenceEmetteur
     *
     * @return  Agence|null
     */ 
    public function getAgenceEmetteur()
    {
        return $this->agenceEmetteur;
    }

    /**
     * Set the value of agenceEmetteur
     *
     * @param  Agence|null  $agenceEmetteur
     *
     * @return  self
     */ 
    public function setAgenceEmetteur($agenceEmetteur)
    {
        $this->agenceEmetteur = $agenceEmetteur;

        return $this;
    }

    /**
     * Get undocumented variable
     *
     * @return  Service|null
     */ 
    public function getServiceEmetteur()
    {
        return $this->serviceEmetteur;
    }

    /**
     * Set undocumented variable
     *
     * @param  Service|null  $serviceEmetteur  Undocumented variable
     *
     * @return  self
     */ 
    public function setServiceEmetteur($serviceEmetteur)
    {
        $this->serviceEmetteur = $serviceEmetteur;

        return $this;
    }

    /**
     * Get undocumented variable
     *
     * @return  Agence|null
     */ 
    public function getAgenceDebiteur()
    {
        return $this->agenceDebiteur;
    }

    /**
     * Set undocumented variable
     *
     * @param  Agence|null  $agenceDebiteur  Undocumented variable
     *
     * @return  self
     */ 
    public function setAgenceDebiteur($agenceDebiteur)
    {
        $this->agenceDebiteur = $agenceDebiteur;

        return $this;
    }

    /**
     * Get undocumented variable
     *
     * @return  Service|null
     */ 
    public function getServiceDebiteur()
    {
        return $this->serviceDebiteur;
    }

    /**
     * Set undocumented variable
     *
     * @param  Service|null  $serviceDebiteur  Undocumented variable
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
            'typeDocument' => $this->typeDocument,
            'niveauUrgence' => $this->niveauUrgence,
            'statut' => $this->statut,
            'interneExterne' => $this->internetExterne,
            'dateDebut' => $this->dateDebut,
            'dateFin' => $this->dateFin,
            'idMateriel' => $this->idMateriel,
            'numParc' => $this->numParc,
            'numSerie' => $this->numSerie,
            'agenceEmetteur' => $this->agenceEmetteur,
            'serviceEmetteur' => $this->serviceEmetteur,
            'agenceDebiteur' => $this->agenceDebiteur,
            'serviceDebiteur' => $this->serviceDebiteur
        ];
    }
}

