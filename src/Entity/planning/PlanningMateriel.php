<?php
namespace App\Entity\planning;

class PlanningMateriel{
    private $codeSuc;
    private $libsuc;
    private $codeServ;
    private $libServ;
    private $idMat;
    private $marqueMat;
    private $typeMat;
    private $numSerie;
    private $numParc;
    private $casier;
    private $annee;
    private $mois;
    private $orIntv;

    /**
     * Get the value of codeSuc
     */ 
    public function getCodeSuc()
    {
        return $this->codeSuc;
    }

    /**
     * Set the value of codeSuc
     *
     * @return  self
     */ 
    public function setCodeSuc($codeSuc)
    {
        $this->codeSuc = $codeSuc;

        return $this;
    }

    /**
     * Get the value of libsuc
     */ 
    public function getLibsuc()
    {
        return $this->libsuc;
    }

    /**
     * Set the value of libsuc
     *
     * @return  self
     */ 
    public function setLibsuc($libsuc)
    {
        $this->libsuc = $libsuc;

        return $this;
    }

    /**
     * Get the value of codeServ
     */ 
    public function getCodeServ()
    {
        return $this->codeServ;
    }

    /**
     * Set the value of codeServ
     *
     * @return  self
     */ 
    public function setCodeServ($codeServ)
    {
        $this->codeServ = $codeServ;

        return $this;
    }

    /**
     * Get the value of libServ
     */ 
    public function getLibServ()
    {
        return $this->libServ;
    }

    /**
     * Set the value of libServ
     *
     * @return  self
     */ 
    public function setLibServ($libServ)
    {
        $this->libServ = $libServ;

        return $this;
    }

    /**
     * Get the value of idMat
     */ 
    public function getIdMat()
    {
        return $this->idMat;
    }

    /**
     * Set the value of idMat
     *
     * @return  self
     */ 
    public function setIdMat($idMat)
    {
        $this->idMat = $idMat;

        return $this;
    }

    /**
     * Get the value of marqueMat
     */ 
    public function getMarqueMat()
    {
        return $this->marqueMat;
    }

    /**
     * Set the value of marqueMat
     *
     * @return  self
     */ 
    public function setMarqueMat($marqueMat)
    {
        $this->marqueMat = $marqueMat;

        return $this;
    }

    /**
     * Get the value of typeMat
     */ 
    public function getTypeMat()
    {
        return $this->typeMat;
    }

    /**
     * Set the value of typeMat
     *
     * @return  self
     */ 
    public function setTypeMat($typeMat)
    {
        $this->typeMat = $typeMat;

        return $this;
    }

    /**
     * Get the value of numSerie
     */ 
    public function getNumSerie()
    {
        return $this->numSerie;
    }

    /**
     * Set the value of numSerie
     *
     * @return  self
     */ 
    public function setNumSerie($numSerie)
    {
        $this->numSerie = $numSerie;

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
     * Get the value of casier
     */ 
    public function getCasier()
    {
        return $this->casier;
    }

    /**
     * Set the value of casier
     *
     * @return  self
     */ 
    public function setCasier($casier)
    {
        $this->casier = $casier;

        return $this;
    }

    /**
     * Get the value of annee
     */ 
    public function getAnnee()
    {
        return $this->annee;
    }

    /**
     * Set the value of annee
     *
     * @return  self
     */ 
    public function setAnnee($annee)
    {
        $this->annee = $annee;

        return $this;
    }

    /**
     * Get the value of mois
     */ 
    public function getMois()
    {
        return $this->mois;
    }

    /**
     * Set the value of mois
     *
     * @return  self
     */ 
    public function setMois($mois)
    {
        $this->mois = $mois;

        return $this;
    }

    /**
     * Get the value of orIntv
     */ 
    public function getOrIntv()
    {
        return $this->orIntv;
    }

    /**
     * Set the value of orIntv
     *
     * @return  self
     */ 
    public function setOrIntv($orIntv)
    {
        $this->orIntv = $orIntv;

        return $this;
    }
}