<?php

namespace App\Traits;


trait CaracteristiqueMaterielTrait
{
    
   private $constructeur;

   private $designation;

   private $modele;

   private $casier;

   private $numParc;

   private $numSerie;

   private $km;

   private $heure;

   
   public function getConstructeur()
   {
      return $this->constructeur;
   }

   public function setConstructeur($constructeur): self
   {
      $this->constructeur = $constructeur;

      return $this;
   }

  
   public function getDesignation()
   {
      return $this->designation;
   }

   public function setDesignation($designation): self
   {
      $this->designation = $designation;

      return $this;
   }

   
   public function getModele()
   {
      return $this->modele;
   }


   public function setModele($modele): self
   {
      $this->modele = $modele;

      return $this;
   }

 
   public function getCasier()
   {
      return $this->casier;
   }

   
   public function setCasier($casier): self
   {
      $this->casier = $casier;

      return $this;
   }

   public function getNumParc()
   {
      return $this->numParc;
   }


   public function setNumParc($numParc): self
   {
      $this->numParc = $numParc;

      return $this;
   }


   public function getNumSerie()
   {
      return $this->numSerie;
   }

 
   public function setNumSerie($numSerie): self
   {
      $this->numSerie = $numSerie;

      return $this;
   }

   
   public function getKm()
   {
      return $this->km;
   }


   public function setKm($km): self
   {
      $this->km = $km;

      return $this;
   }

  
   public function getHeure()
   {
      return $this->heure;
   }

   
   public function setHeure($heure): self
   {
      $this->heure = $heure;

      return $this;
   }
}