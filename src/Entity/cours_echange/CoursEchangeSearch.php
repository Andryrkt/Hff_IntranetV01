<?php
namespace App\Entity\cours_echange;

class CoursEchangeSearch{

    private $dateHisto ;

    /**
     * Get the value of dateHisto
     */ 
    public function getDateHisto()
    {
        return $this->dateHisto;
    }

    /**
     * Set the value of dateHisto
     *
     * @return  self
     */ 
    public function setDateHisto($dateHisto)
    {
        $this->dateHisto = $dateHisto;

        return $this;
    }
}