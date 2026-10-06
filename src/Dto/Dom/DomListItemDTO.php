<?php

namespace App\Dto\Dom;

use App\Constants\dom\StatutDomConstant;

class DomListItemDTO
{
    public int     $id;
    public string  $numeroOrdreMission;
    public string  $statutDescription;
    public string  $codeSousType;
    public string  $dateDemande;
    public string  $motifDeplacement;
    public string  $matricule;
    public string  $libelleCodeAgenceService;
    public string  $dateDebut;
    public string  $dateFin;
    public string  $client;
    public string  $lieuIntervention;
    public string  $totalGeneralPayer;
    public string  $devis;
    public bool    $showTropPercuAction;

    public function getStatutClass(): string
    {
        return $this->statutDescription ? StatutDomConstant::getCssClass($this->statutDescription) : '';
    }
}
