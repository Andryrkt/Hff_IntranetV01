<?php

namespace App\Entity\planning;

use App\Entity\admin\dit\WorNiveauUrgence;

class PlanningMateriel
{
    private $commercial;
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
    private $qteCdm;
    private $qteLiv;
    private $qteAll;
    public $moisDetails = [];
    private $numDit;
    private $migration;
    private $pos;
    private $numeroOr;
    private $commentaire;
    private $plan;
    private $back;


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



    /**
     * Get the value of qteCdm
     */
    public function getQteCdm()
    {
        return $this->qteCdm;
    }

    /**
     * Set the value of qteCdm
     *
     * @return  self
     */
    public function setQteCdm($qteCdm)
    {
        $this->qteCdm = $qteCdm;

        return $this;
    }

    /**
     * Get the value of qteliv
     */
    public function getQteLiv()
    {
        return $this->qteLiv;
    }

    /**
     * Set the value of qteliv
     *
     * @return  self
     */
    public function setQteLiv($qteLiv)
    {
        $this->qteLiv = $qteLiv;

        return $this;
    }

    /**
     * Get the value of qteAll
     */
    public function getQteAll()
    {
        return $this->qteAll;
    }

    /**
     * Set the value of qteAll
     *
     * @return  self
     */
    public function setQteAll($qteAll)
    {
        $this->qteAll = $qteAll;

        return $this;
    }

    public function addMoisDetail($mois, $annee, $orIntv, $qteCdm, $qteLiv, $qteAll, $numDit, $migration, $commentaire, $back)
    {
        $this->moisDetails[] = [
            'mois' => $mois,
            'annee' => $annee,
            'orIntv' => $orIntv,
            'qteCdm' => $qteCdm,
            'qteLiv' => $qteLiv,
            'qteAll' => $qteAll,
            'numDit' => $numDit,
            'migration' => $migration,
            'commentaire' => $commentaire,
            'back' => $back
        ];
    }

    public function addMoisDetailMagasin($mois, $annee, $orIntv, $qteCdm, $qteLiv, $qteAll, $commentaire,$back)
    {
        $this->moisDetails[] = [
            'mois' => $mois,
            'annee' => $annee,
            'orIntv' => $orIntv,
            'qteCdm' => $qteCdm,
            'qteLiv' => $qteLiv,
            'qteAll' => $qteAll,
            'commentaire' => $commentaire,
            'back' => $back
        ];
    }
    /**
     * Get the value of moisDetails
     */
    public function getMoisDetails()
    {
        return $this->moisDetails;
    }

    /**
     * Set the value of moisDetails
     *
     * @return  self
     */
    public function setMoisDetails($moisDetails)
    {
        $this->moisDetails = $moisDetails;

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
     * Get the value of migration
     */
    public function getMigration()
    {
        return $this->migration;
    }

    /**
     * Set the value of migration
     *
     * @return  self
     */
    public function setMigration($migration)
    {
        $this->migration = $migration;

        return $this;
    }

    /**
     * Get the value of pos
     */
    public function getPos()
    {
        return $this->pos;
    }

    /**
     * Set the value of pos
     *
     * @return  self
     */
    public function setPos($pos)
    {
        $this->pos = $pos;

        return $this;
    }

    /**
     * Get the value of numeroOr
     */
    public function getNumeroOr()
    {
        return $this->numeroOr;
    }

    /**
     * Set the value of numeroOr
     *
     * @return  self
     */
    public function setNumeroOr($numeroOr)
    {
        $this->numeroOr = $numeroOr;

        return $this;
    }

    /**
     * Get the value of commentaire
     */
    public function getCommentaire()
    {
        return $this->commentaire;
    }

    /**
     * Set the value of commentaire
     *
     * @return  self
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Get the value of plan
     */
    public function getPlan()
    {
        return $this->plan;
    }

    /**
     * Set the value of plan
     *
     * @return  self
     */
    public function setPlan($plan)
    {
        $this->plan = $plan;

        return $this;
    }

    /**
     * Get the value of back
     */
    public function getBack()
    {
        return $this->back;
    }

    /**
     * Set the value of back
     *
     * @return  self
     */
    public function setBack($back)
    {
        $this->back = $back;

        return $this;
    }

    /**
     * Get the value of commercial
     */
    public function getCommercial()
    {
        return $this->commercial;
    }

    /**
     * Set the value of commercial
     */
    public function setCommercial($commercial): self
    {
        $this->commercial = $commercial;

        return $this;
    }
}
