<?php

namespace App\Entity\inventaire;

class InventaireSearch
{
    private $agence;
    private $dateDebut;
    private $dateFin;
    private $stock;
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
     * Get the value of stock
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * Set the value of stock
     *
     * @return  self
     */
    public function setStock($stock)
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * Transforme l'objet en tableau
     */
    public function toArray(): array
    {
        return [
            'agence' => $this->agence,
            'dateDebut' => $this->dateDebut,
            'dateFin' => $this->dateFin,
            'stock' => $this->stock,
        ];
    }

    /**
     * Transforme un tableau en objet
     */
    public function arrayToObjet(array $criteriaTab)
    {
        $this
            ->setAgence($criteriaTab['agence'])
            ->setDateDebut($criteriaTab['dateDebut'])
            ->setDateFin($criteriaTab['dateFin'])
            ->setStock($criteriaTab['stock'])
        ;
    }
}
