<?php

namespace App\Entity\mutation;

class MutationSearch
{
    private $statut;
    private $numMut;
    private $matricule;
    private $dateDemandeDebut;
    private $dateDemandeFin;
    private $dateMutationDebut;
    private $dateMutationFin;
    private $agenceEmetteur;
    private $serviceEmetteur;
    private $agenceDebiteur;
    private $serviceDebiteur;


    public function toArray(): array
    {
        return [
            'statut'            => $this->statut,
            'numMut'            => $this->numMut,
            'matricule'         => $this->matricule,
            'dateDemandeDebut'  => $this->dateDemandeDebut,
            'dateDemandeFin'    => $this->dateDemandeFin,
            'dateMutationDebut' => $this->dateMutationDebut,
            'dateMutationFin'   => $this->dateMutationFin,
            'agenceEmetteur'    => $this->agenceEmetteur,
            'serviceEmetteur'   => $this->serviceEmetteur,
            'agenceDebiteur'    => $this->agenceDebiteur,
            'serviceDebiteur'   => $this->serviceDebiteur,
        ];
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
     * Get the value of numMut
     */
    public function getNumMut()
    {
        return $this->numMut;
    }

    /**
     * Set the value of numMut
     *
     * @return  self
     */
    public function setNumMut($numMut)
    {
        $this->numMut = $numMut;

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
     * Get the value of dateDemandeDebut
     */
    public function getDateDemandeDebut()
    {
        return $this->dateDemandeDebut;
    }

    /**
     * Set the value of dateDemandeDebut
     *
     * @return  self
     */
    public function setDateDemandeDebut($dateDemandeDebut)
    {
        $this->dateDemandeDebut = $dateDemandeDebut;

        return $this;
    }

    /**
     * Get the value of dateDemandeFin
     */
    public function getDateDemandeFin()
    {
        return $this->dateDemandeFin;
    }

    /**
     * Set the value of dateDemandeFin
     *
     * @return  self
     */
    public function setDateDemandeFin($dateDemandeFin)
    {
        $this->dateDemandeFin = $dateDemandeFin;

        return $this;
    }

    /**
     * Get the value of dateMutationDebut
     */
    public function getDateMutationDebut()
    {
        return $this->dateMutationDebut;
    }

    /**
     * Set the value of dateMutationDebut
     *
     * @return  self
     */
    public function setDateMutationDebut($dateMutationDebut)
    {
        $this->dateMutationDebut = $dateMutationDebut;

        return $this;
    }

    /**
     * Get the value of dateMutationFin
     */
    public function getDateMutationFin()
    {
        return $this->dateMutationFin;
    }

    /**
     * Set the value of dateMutationFin
     *
     * @return  self
     */
    public function setDateMutationFin($dateMutationFin)
    {
        $this->dateMutationFin = $dateMutationFin;

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
}
