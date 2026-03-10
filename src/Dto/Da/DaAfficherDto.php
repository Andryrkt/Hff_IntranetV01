<?php

namespace App\Dto\Da;

use App\Constants\da\StatutConstant;

class DaAfficherDto
{
    public $id;
    public $objet;
    public $urlDetail;
    public $numDaParent;
    public $numeroDemandeAppro;
    public $datype;
    public $daTypeIcon;
    public $niveauUrgence;
    public $numeroFournisseur;
    public $nomFournisseur;
    public $envoyeFrn;
    public $dateFinSouhaite;
    public $artConstp;
    public $artRefp;
    public $artDesi;
    public $qteDem;
    public $qteEnAttent;
    public $qteDispo;
    public $qteLivrer;
    public $dateLivraisonPrevue;
    public $joursDispo;
    public $styleJoursDispo;
    public $demandeur;

    // OR
    public $numeroOr;
    public $datePlannigOr;
    public $statutOr;

    //Cde
    public $statutCde;
    public $numeroCde;
    public $positionBc;

    // DAL
    public $statutDal;

    //DIT
    public $numeroDemandeDit;

    // html
    public $tdNumCdeAttributes;
    public $styleClickableCell;
    public $tdCheckboxAttributes;
    public $aDtLivPrevAttributes;

    public function getStyleStatutDA(): string
    {
        if (!$this->statutDal) {
            return '';
        }

        return StatutConstant::getCssClassDa($this->statutDal);
    }

    public function getStatutOrCssClass(): string
    {
        if (!$this->statutOr) {
            return '';
        }

        return StatutConstant::getCssClassOr($this->statutOr);
    }

    public function getStyleStatutBC(): string
    {
        if (!$this->statutCde) {
            return '';
        }

        return StatutConstant::getCssClassBc($this->statutCde);
    }
}
