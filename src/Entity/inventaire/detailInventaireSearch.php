<?php
namespace App\Entity\inventaire;
class DetailInventaireSearch{
    private $agence;
    private ?\DateTimeInterface $dateDebut = null;
    private ?\DateTimeInterface $dateFin = null;
    private $inventaireDispo;

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
     * Get the value of inventaireDispo
     */ 
    public function getInventaireDispo()
    {
        return $this->inventaireDispo;
    }

    /**
     * Set the value of inventaireDispo
     *
     * @return  self
     */ 
    public function setInventaireDispo($inventaireDispo)
    {
        $this->inventaireDispo = $inventaireDispo;

        return $this;
    }
    public function toArray(): array
    {
        return [
            'agence' => $this->agence,
            'dateDebut' => $this->dateDebut,
            'dateFin' => $this->dateFin,
            'inventaireDispo' => $this->inventaireDispo,
        ];
    }
    public function arrayToObjet(array $criteriaTab)
    {
        $this
            ->setAgence($criteriaTab['agence'])
            ->setDateDebut($criteriaTab['dateDebut'])
            ->setDateFin($criteriaTab['dateFin'])
            ->setInventaireDispo($criteriaTab['inventaireDispo'])
        ;
    }
}