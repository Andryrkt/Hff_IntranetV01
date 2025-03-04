<?php

namespace App\Entity\admin\historisation\pageConsultation;

class PageConsultationSearch
{
    private ?string $utilisateur = '';

    private ?string $nom_page = '';

    private ?string $machineUser = '';

    private ?\Datetime $dateDebut = null;

    private ?\Datetime $dateFin = null;

    /**=====================================================================================
     * GETTERS and SETTERS
     * =====================================================================================*/

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

    /**
     * Get the value of nom_page
     */
    public function getNomPage()
    {
        return $this->nom_page;
    }

    /**
     * Set the value of nom_page
     *
     * @return  self
     */
    public function setNomPage($nom_page)
    {
        $this->nom_page = $nom_page;

        return $this;
    }

    /**
     * Get the value of machineUser
     */
    public function getMachineUser()
    {
        return $this->machineUser;
    }

    /**
     * Set the value of machineUser
     *
     * @return  self
     */
    public function setMachineUser($machineUser)
    {
        $this->machineUser = $machineUser;

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

    public function toArray(): array
    {
        return [
            'utilisateur'    => $this->utilisateur,
            'nom_page'       => $this->nom_page,
            'machineUser'    => $this->machineUser,
            'dateDebut'      => $this->dateDebut,
            'dateFin'        => $this->dateFin,
        ];
    }
}
