<?php

namespace App\Entity\badm;

use App\Entity\admin\Agence;
use App\Entity\admin\Service;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\badm\TypeMouvement;

class BadmSearch
{
    private ?StatutDemande $statut = null;

    private ?int $idMateriel = null;

    private ?TypeMouvement $typeMouvement = null;

    private ?\Datetime $dateDebut = null;

    private ?\DateTime $dateFin = null;

    private ?string $numParc = '';

    private ?string $numSerie = '';

    private ?int $agenceEmetteur = null;

    private ?int $serviceEmetteur = null;

    private ?int $agenceDebiteur = null;

    private ?int $serviceDebiteur = null;

    private ?string $numBadm = '';


    //-===============================================================================================================================

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
     * Get the value of typeMouvement
     */
    public function getTypeMouvement()
    {
        return $this->typeMouvement;
    }

    /**
     * Set the value of typeMouvement
     *
     * @return  self
     */
    public function setTypeMouvement($typeMouvement)
    {
        $this->typeMouvement = $typeMouvement;

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
     * @param  ?int  $agenceEmetteur
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



    public function getNumBadm()
    {
        return $this->numBadm;
    }


    public function setNumBadm($numBadm): self
    {
        $this->numBadm = $numBadm;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'typeMouvement'   => $this->typeMouvement,
            'statut'          => $this->statut,
            'dateDebut'       => $this->dateDebut,
            'dateFin'         => $this->dateFin,
            'idMateriel'      => $this->idMateriel,
            'numParc'         => $this->numParc,
            'numSerie'        => $this->numSerie,
            'agenceEmetteur'  => $this->agenceEmetteur,
            'serviceEmetteur' => $this->serviceEmetteur,
            'agenceDebiteur'  => $this->agenceDebiteur,
            'serviceDebiteur' => $this->serviceDebiteur,
            'numBadm'         => $this->numBadm,
        ];
    }
}
