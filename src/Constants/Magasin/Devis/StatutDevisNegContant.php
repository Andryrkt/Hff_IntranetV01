<?php

namespace App\Constants\Magasin\Devis;


class StatutDevisNegContant
{
    public const PRIX_A_CONFIRMER = 'Prix à confirmer';
    public const PRIX_VALIDER_TANA = 'Prix validé - devis à envoyer au client';
    public const PRIX_VALIDER_AGENCE = 'Prix validé - devis à soumettre';
    public const PRIX_MODIFIER_TANA = 'Prix modifié - devis à envoyer au client';
    public const PRIX_MODIFIER_AGENCE = 'Prix modifié - devis à soumettre';
    public const DEMANDE_REFUSE_PAR_PM = 'Demande refusée par le PM';
    public const A_VALIDER_CHEF_AGENCE = "A valider chef d'agence";
    public const VALIDE_AGENCE = 'Validé - à envoyer au client';
    public const ENVOYER_CLIENT = 'Envoyé au client';
    public const CLOTURER_A_MODIFIER = 'Cloturé - A modifier';
    public const A_TRAITER = 'A traiter';
    public const STATUT_A_VALIDER_CHEF_AGENCE = "A valider chef d'agence";

    // Transformer le tableau en format de choix pour le formulaire (statut DW)
    public const STATUTS_DW = [
        self::A_TRAITER             => self::A_TRAITER,
        self::PRIX_A_CONFIRMER      => self::PRIX_A_CONFIRMER,
        self::PRIX_VALIDER_TANA     => self::PRIX_VALIDER_TANA,
        self::PRIX_VALIDER_AGENCE   => self::PRIX_VALIDER_AGENCE,
        self::PRIX_MODIFIER_TANA    => self::PRIX_MODIFIER_TANA,
        self::PRIX_MODIFIER_AGENCE  => self::PRIX_MODIFIER_AGENCE,
        self::DEMANDE_REFUSE_PAR_PM => self::DEMANDE_REFUSE_PAR_PM,
        self::A_VALIDER_CHEF_AGENCE => self::A_VALIDER_CHEF_AGENCE,
        self::VALIDE_AGENCE         => self::VALIDE_AGENCE,
        self::ENVOYER_CLIENT        => self::ENVOYER_CLIENT,
        self::CLOTURER_A_MODIFIER   => self::CLOTURER_A_MODIFIER,
    ];

    public const statutIPS = [
        "--"  => "En cours",
        "AC"  => "Accepté",
        "DE"  => "Edité",
        "RE"  => "Refusé",
        "TR"  => "Transferé",
    ];

    public const CSS_CLASS_MAP_STATUT_DW = [
        self::A_TRAITER             => 'bg-a-traiter',
        self::PRIX_A_CONFIRMER      => 'bg-prix-a-confirmer',
        self::PRIX_VALIDER_TANA     => 'bg-prix-valider-tana',
        self::PRIX_VALIDER_AGENCE   => 'bg-prix-valider-agence',
        self::PRIX_MODIFIER_TANA    => 'bg-prix-modifier-magasin',
        self::PRIX_MODIFIER_AGENCE  => 'bg-prix-modifier-agence',
        self::DEMANDE_REFUSE_PAR_PM => 'bg-demande-refuse-par-pm',
        self::A_VALIDER_CHEF_AGENCE => 'bg-a-valider-chef-agence',
        self::VALIDE_AGENCE         => 'bg-valide-agence',
        self::ENVOYER_CLIENT        => 'bg-envoyer-client',
        self::CLOTURER_A_MODIFIER   => 'bg-cloturer-a-modifier',
    ];


    public static function getCssClassDW(string $statut): string
    {
        return self::CSS_CLASS_MAP_STATUT_DW[$statut] ?? '';
    }
}
