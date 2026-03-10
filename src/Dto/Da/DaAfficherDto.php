<?php

namespace App\Dto\Da;

use App\Constants\da\StatutConstant;
use App\Entity\da\DemandeAppro;

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
    public $estDalr;
    public $verouille;
    public $estFicheTechnique;

    // OR
    public $numeroOr;
    public $datePlannigOr;
    public $statutOr;

    // Cde
    public $statutCde;
    public $numeroCde;
    public $positionBc;

    // DAL
    public $statutDal;

    // DIT
    public $numeroDemandeDit;

    // Actions & URLs
    public $urlCreation;
    public $urlDelete;
    public $urlDemandeDevis;
    public $ajouterDA;
    public $supprimable;
    public $demandeDevis;
    public $statutValide;
    public $centrale;

    // HTML Attributes
    public $tdNumCdeAttributes;
    public $styleClickableCell;
    public $tdCheckboxAttributes;
    public $aDtLivPrevAttributes;
    public $aArtDesiAttributes;

    public function getStyleStatutDA(): string
    {
        return $this->statutDal ? StatutConstant::getCssClassDa($this->statutDal) : '';
    }

    public function getStyleStatutOR(): string
    {
        return $this->statutOr ? StatutConstant::getCssClassOr($this->statutOr) : '';
    }

    public function getStyleStatutBC(): string
    {
        return $this->statutCde ? StatutConstant::getCssClassBc($this->statutCde) : '';
    }

    public function isStatutValide(): bool
    {
        return $this->statutDal === DemandeAppro::STATUT_VALIDE;
    }
}
