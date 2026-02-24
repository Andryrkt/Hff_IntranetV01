<?php

namespace App\Dto\Dom;

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
    public string  $classeStatut;
    public string  $styleStatut;
    public bool    $showTropPercuAction;

    public static array $classeStatutArray = [
        'OUVERT'                     => 'bg-warning bg-gradient text-center',
        'PAYE'                       => 'bg-success bg-gradient',
        'ATTENTE PAIEMENT'           => 'bg-success',
        'CONTROLE SERVICE'           => 'bg-info',
        'A VALIDER SERVICE EMETTEUR' => 'bg-primary',
        'VALIDE'                     => 'bg-success',
        'VALIDE COMPTABILITE'        => 'bg-success',
        'VALIDATION RH'              => 'bg-success',
        'VALIDATION DG'              => 'bg-success',
        'ANNULE CHEF DE SERVICE'     => 'bg-danger',
        'ANNULE COMPTABILITE'        => 'bg-danger',
        'ANNULE SECRETARIAT RH'      => 'bg-danger',
        'ANNULE'                     => 'bg-danger',
    ];
    public static array $styleStatutArray = [
        'ATTENTE PAIEMENT'           => '--bs-bg-opacity: .5;',
        'A VALIDER SERVICE EMETTEUR' => '--bs-bg-opacity: .5;',
    ];
}
