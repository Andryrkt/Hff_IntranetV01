<?php
namespace App\Entity\planning;
class PlanningDetail{
    private $numOr;
    private $Intv;
    private $cst;
    private $ref;
    private $desi;
    private $QteReliquat;
    private $Qteliv;
    private $QteAll;
    private $rmq;
    private $numrmq;
    private $typeP;
    private $numligne;
    private $grp;
    private $numeroCmd;
    private $Statut;
    private $date_statut;
   

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
     * Get the value of Intv
     */ 
    public function getIntv()
    {
        return $this->Intv;
    }

    /**
     * Set the value of Intv
     *
     * @return  self
     */ 
    public function setIntv($Intv)
    {
        $this->Intv = $Intv;

        return $this;
    }

    /**
     * Get the value of cst
     */ 
    public function getCst()
    {
        return $this->cst;
    }

    /**
     * Set the value of cst
     *
     * @return  self
     */ 
    public function setCst($cst)
    {
        $this->cst = $cst;

        return $this;
    }

    /**
     * Get the value of ref
     */ 
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Set the value of ref
     *
     * @return  self
     */ 
    public function setRef($ref)
    {
        $this->ref = $ref;

        return $this;
    }

    /**
     * Get the value of desi
     */ 
    public function getDesi()
    {
        return $this->desi;
    }

    /**
     * Set the value of desi
     *
     * @return  self
     */ 
    public function setDesi($desi)
    {
        $this->desi = $desi;

        return $this;
    }

    /**
     * Get the value of QteReliquat
     */ 
    public function getQteReliquat()
    {
        return $this->QteReliquat;
    }

    /**
     * Set the value of QteReliquat
     *
     * @return  self
     */ 
    public function setQteReliquat($QteReliquat)
    {
        $this->QteReliquat = $QteReliquat;

        return $this;
    }

    /**
     * Get the value of Qteliv
     */ 
    public function getQteliv()
    {
        return $this->Qteliv;
    }

    /**
     * Set the value of Qteliv
     *
     * @return  self
     */ 
    public function setQteliv($Qteliv)
    {
        $this->Qteliv = $Qteliv;

        return $this;
    }

    /**
     * Get the value of QteAll
     */ 
    public function getQteAll()
    {
        return $this->QteAll;
    }

    /**
     * Set the value of QteAll
     *
     * @return  self
     */ 
    public function setQteAll($QteAll)
    {
        $this->QteAll = $QteAll;

        return $this;
    }

    /**
     * Get the value of rmq
     */ 
    public function getRmq()
    {
        return $this->rmq;
    }

    /**
     * Set the value of rmq
     *
     * @return  self
     */ 
    public function setRmq($rmq)
    {
        $this->rmq = $rmq;

        return $this;
    }

    /**
     * Get the value of numrmq
     */ 
    public function getNumrmq()
    {
        return $this->numrmq;
    }

    /**
     * Set the value of numrmq
     *
     * @return  self
     */ 
    public function setNumrmq($numrmq)
    {
        $this->numrmq = $numrmq;

        return $this;
    }

    /**
     * Get the value of typeP
     */ 
    public function getTypeP()
    {
        return $this->typeP;
    }

    /**'
     * Set the value of typeP
     *
     * @return  self
     */ 
    public function setTypeP($typeP)
    {
        $this->typeP = $typeP;

        return $this;
    }

    /**
     * Get the value of numligne
     */ 
    public function getNumligne()
    {
        return $this->numligne;
    }

    /**
     * Set the value of numligne
     *
     * @return  self
     */ 
    public function setNumligne($numligne)
    {
        $this->numligne = $numligne;

        return $this;
    }

    /**
     * Get the value of grp
     */ 
    public function getGrp()
    {
        return $this->grp;
    }

    /**
     * Set the value of grp
     *
     * @return  self
     */ 
    public function setGrp($grp)
    {
        $this->grp = $grp;

        return $this;
    }

    /**
     * Get the value of numeroCmd
     */ 
    public function getNumeroCmd()
    {
        return $this->numeroCmd;
    }

    /**
     * Set the value of numeroCmd
     *
     * @return  self
     */ 
    public function setNumeroCmd($numeroCmd)
    {
        $this->numeroCmd = $numeroCmd;

        return $this;
    }

    /**
     * Get the value of Statut
     */ 
    public function getStatut()
    {
        return $this->Statut;
    }

    /**
     * Set the value of Statut
     *
     * @return  self
     */ 
    public function setStatut($Statut)
    {
        $this->Statut = $Statut;

        return $this;
    }

    /**
     * Get the value of date_statut
     */ 
    public function getDate_statut()
    {
        return $this->date_statut;
    }

    /**
     * Set the value of date_statut
     *
     * @return  self
     */ 
    public function setDate_statut($date_statut)
    {
        $this->date_statut = $date_statut;

        return $this;
    }
}