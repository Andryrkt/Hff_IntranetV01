<?php

namespace App\Dto\Magasin\Devis;

use App\Constants\Magasin\Devis\PointageRelanceStatutConstant;
use App\Constants\Magasin\Devis\StatutBcNegConstant;
use App\Constants\Magasin\Devis\StatutDevisNegContant;

class DevisNegDto
{
    public ?string $statutDw = null;
    public ?string $statutBc = null;
    public string $numeroDevis;
    public string $dateCreation;
    public ?string $emetteur = null;
    public ?string $client = null;
    public ?string $referenceClient = null;
    public ?float $montantDevis = 0.00;
    public $dateEnvoiDevisAuClient = null;
    public ?string $positionIps = null;
    public ?string $numeroPo = null;
    public ?string $urlPo = null;
    public ?string $utilisateurCreateurDevis = null;
    public ?string $soumisPar = null;
    public $constructeur = null;

    // Styles CSS calculés
    public string $styleStatutDw = '';
    public string $styleStatutBc = '';
    public string $styleStatutPR1 = '';
    public string $styleStatutPR2 = '';
    public string $styleStatutPR3 = '';

    // Url
    public $url = [];

    // Relance
    public ?string $statutRelance1 = null;
    public ?string $statutRelance2 = null;
    public ?string $statutRelance3 = null;

    // blocage de soumission
    public bool $pointagedevis = false;
    public bool $relanceClient = false;

    public string $devise = '';
    public ?int $numeroVersion = 0;
    public int $nombreLignes = 0;
    public $dateMajStatut;
    public string $utilisateur = '';
    public bool $cat = false;
    public bool $nonCat = false;
    public string $nomFichier = '';
    public int $sommeNumeroLignes;
    public $datePointage = null;
    public ?string $relance = '';
    public $dateBc = null;
    public $pieceJoint01;
    public $pieceJoint2;
    public ?string $pieceJointExcel = null;
    public ?bool $migration = false;
    public ?string $statutTemp = '';
    public ?string $statutRelance = null;
    public $stopProgressionGlobal;
    public $dateStopGlobal;
    public $motifStopGlobal;
    public $dateRepriseManuel;



    public function styleStatutDw(): string
    {
        return $this->statutDw ? StatutDevisNegContant::getCssClassDW($this->statutDw) : '';
    }

    public function styleStatutBc(): string
    {
        return $this->statutBc ? StatutBcNegConstant::getCssClassBC($this->statutBc) : '';
    }

    public function styleStatutPR1(): string
    {
        return $this->statutRelance1 ? PointageRelanceStatutConstant::getCssClassPR1($this->statutRelance1) : '';
    }

    public function styleStatutPR2(): string
    {
        return $this->statutRelance2 ? PointageRelanceStatutConstant::getCssClassPR2($this->statutRelance2) : '';
    }

    public function styleStatutPR3(): string
    {
        return $this->statutRelance3 ? PointageRelanceStatutConstant::getCssClassPR3($this->statutRelance3) : '';
    }
}
