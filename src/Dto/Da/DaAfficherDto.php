<?php

namespace App\Dto\Da;

use App\Constants\da\StatutBcConstant;
use App\Constants\da\StatutDaConstant;
use App\Constants\da\StatutOrConstant;
use App\Constants\ddp\StatutConstants;

class DaAfficherDto
{
    public $id;
    public $objet;
    public $numeroLigne;
    public $numDaParent;
    public $numeroDemandeAppro;
    public $daTypeIcon;
    public $allIcons;
    public $niveauUrgence;
    public $dateFinSouhaite;
    public $artConstp;
    public $artRefp;
    public $artDesi;
    public $dateLivraisonPrevue;
    public $estAppro = false;

    public $estDalr;
    public $verouille;
    public $estFicheTechnique;
    // jour dispo
    public $joursDispo;
    public $styleJoursDispo;
    // type de DA
    public $datype;
    public $daViaOR;
    public $daDirect;
    public $daReappro;
    public $daParent;
    // Demandeur
    public $demandeur;
    public $dateDemande;
    // Consultateur
    public $codeAgenceUser; // code agence de l'utilisateur qui consulte la liste
    public $codeServiceUser; // code service de l'utilisateur qui consuler la liste
    // Qte
    public $qteDem;
    public $qteEnAttent;
    public $qteDispo;
    public $qteLivrer;
    // Fournisseur
    public $numeroFournisseur;
    public $nomFournisseur;
    public $envoyeFrn;
    // OR
    public $numeroOr;
    public $datePlannigOr;
    public $statutOr;
    // Cde
    public $statutCde;
    public $statutDaSoumissionBc;
    public $numeroCde;
    public $positionBc;
    // DAL
    public $statutDal;
    // DIT
    public $numeroDemandeDit;
    // Actions & URLs
    public $urlCreation;
    public $urlDetail;
    public $urlDelete;
    public $urlProposition;
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
    public $ddpCloture;
    // DDP
    public $statutCompta; //?dernière statut du demande de paiement liée à la DA

    public function getStyleStatutDA(): string
    {
        return $this->statutDal ? StatutDaConstant::getCssClassDa($this->statutDal) : '';
    }

    public function getStyleStatutOR(): string
    {
        return $this->statutOr ? StatutOrConstant::getCssClassOr($this->statutOr) : '';
    }

    public function getStyleStatutBC(): string
    {
        return $this->statutCde ? StatutBcConstant::getCssClassBc($this->statutCde) : '';
    }

    public function getStyleStatutCompta(): string
    {
        return $this->statutCompta ? StatutConstants::getCssClass($this->statutCompta) : '';
    }

    public function isStatutValide(): bool
    {
        return $this->statutDal === StatutDaConstant::STATUT_VALIDE;
    }
}
