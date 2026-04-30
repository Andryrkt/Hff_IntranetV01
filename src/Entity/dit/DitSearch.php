<?php

namespace App\Entity\dit;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\dit\WorTypeDocument;
use App\Entity\admin\dit\WorNiveauUrgence;

class DitSearch
{
    private ?WorNiveauUrgence $niveauUrgence = null;

    private ?StatutDemande $statut = null;

    private ?int $idMateriel = 0;

    private ?WorTypeDocument $typeDocument = null;

    private ?string $internetExterne = '';

    private ?\Datetime $dateDebut = null;

    private ?\DateTime $dateFin = null;

    private ?string $numParc = '';

    private ?string $numSerie = '';

    private ?int $agenceEmetteur = null;

    private ?int $serviceEmetteur = null;

    private ?int $agenceDebiteur = null;

    private ?int $serviceDebiteur = null;

    private ?string $numDit = '';

    private ?int $numOr = null;

    private ?string $statutOr = '';

    private ?bool $ditSansOr = false;

    private  $categorie;

    private ?string $utilisateur = '';

    private ?string $sectionAffectee = null;

    private ?string $sectionSupport1 = '';

    private ?string $sectionSupport2 = '';

    private ?string $sectionSupport3 = '';

    private ?string $etatFacture = '';

    private ?string $numDevis = '';

    private $reparationRealise;

    //-===============================================================================================================================
    public function getNiveauUrgence()
    {
        return $this->niveauUrgence;
    }


    public function setNiveauUrgence($niveauUrgence): self
    {
        $this->niveauUrgence = $niveauUrgence;

        return $this;
    }


    public function getStatut()
    {
        return $this->statut;
    }


    public function setStatut($statutDemande): self
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
     * @return  ?int
     */
    public function getAgenceEmetteur()
    {
        return $this->agenceEmetteur;
    }

    /**
     * Set the value of agenceEmetteur
     *
     * @param  ?int $agenceEmetteur
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
     * @return  ?int
     */
    public function getServiceEmetteur()
    {
        return $this->serviceEmetteur;
    }

    /**
     * Set undocumented variable
     *
     * @param  ?int  $serviceEmetteur  Undocumented variable
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
     * @return  ?int
     */
    public function getAgenceDebiteur()
    {
        return $this->agenceDebiteur;
    }

    /**
     * Set undocumented variable
     *
     * @param  ?int  $agenceDebiteur  Undocumented variable
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
     * @return  ?int
     */
    public function getServiceDebiteur()
    {
        return $this->serviceDebiteur;
    }

    /**
     * Set undocumented variable
     *
     * @param  ?int  $serviceDebiteur  Undocumented variable
     *
     * @return  self
     */
    public function setServiceDebiteur($serviceDebiteur)
    {
        $this->serviceDebiteur = $serviceDebiteur;

        return $this;
    }



    /**
     * Get the value of numDit
     */
    public function getNumDit()
    {
        return $this->numDit;
    }

    /**
     * Set the value of numDit
     *
     * @return  self
     */
    public function setNumDit($numDit)
    {
        $this->numDit = $numDit;

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
     * Get the value of statutOr
     */
    public function getStatutOr()
    {
        return $this->statutOr;
    }

    /**
     * Set the value of statutOr
     *
     * @return  self
     */
    public function setStatutOr($statutOr)
    {
        $this->statutOr = $statutOr;

        return $this;
    }

    /**
     * Get the value of ditSansOr
     */
    public function getDitSansOr()
    {
        return $this->ditSansOr;
    }

    /**
     * Set the value of ditSansOr
     *
     * @return  self
     */
    public function setDitSansOr($ditSansOr)
    {
        $this->ditSansOr = $ditSansOr;

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

    public function getSectionAffectee()
    {
        return $this->sectionAffectee;
    }

    public function setSectionAffectee($sectionAffectee): self
    {
        $this->sectionAffectee = $sectionAffectee;

        return $this;
    }

    /**
     * Get the value of sectionSupport1
     */
    public function getSectionSupport1()
    {
        return $this->sectionSupport1;
    }

    /**
     * Set the value of sectionSupport1
     *
     * @return  self
     */
    public function setSectionSupport1($sectionSupport1)
    {
        $this->sectionSupport1 = $sectionSupport1;

        return $this;
    }

    /**
     * Get the value of sectionSupport2
     */
    public function getSectionSupport2()
    {
        return $this->sectionSupport2;
    }

    /**
     * Set the value of sectionSupport2
     *
     * @return  self
     */
    public function setSectionSupport2($sectionSupport2)
    {
        $this->sectionSupport2 = $sectionSupport2;

        return $this;
    }


    /**
     * Get the value of sectionSupport3
     */
    public function getSectionSupport3()
    {
        return $this->sectionSupport3;
    }

    /**
     * Set the value of sectionSupport3
     *
     * @return  self
     */
    public function setSectionSupport3($sectionSupport3)
    {
        $this->sectionSupport3 = $sectionSupport3;

        return $this;
    }

    /**
     * Get the value of etatFacture
     */
    public function getEtatFacture()
    {
        return $this->etatFacture;
    }

    /**
     * Set the value of etatFacture
     *
     * @return  self
     */
    public function setEtatFacture($etatFacture)
    {
        $this->etatFacture = $etatFacture;

        return $this;
    }

    /**
     * Get the value of numDevis
     */
    public function getNumDevis()
    {
        return $this->numDevis;
    }

    /**
     * Set the value of numDevis
     *
     * @return  self
     */
    public function setNumDevis($numDevis)
    {
        $this->numDevis = $numDevis;

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
            'serviceDebiteur' => $this->serviceDebiteur,
            'numDit' => $this->numDit,
            'numOr' => $this->numOr,
            'statutOr' => $this->statutOr,
            'ditSansOr' => $this->ditSansOr,
            'categorie' => $this->categorie,
            'utilisateur' => $this->utilisateur,
            'sectionAffectee' => $this->sectionAffectee,
            'sectionSupport1' => $this->sectionSupport1,
            'sectionSupport2' => $this->sectionSupport2,
            'sectionSupport3' => $this->sectionSupport3,
            'etatFacture' => $this->etatFacture,
            'numDevis' => $this->numDevis,
            'reparationRealise' => $this->reparationRealise
        ];
    }
}
