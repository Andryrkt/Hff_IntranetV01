<?php

namespace App\Constants\da;

use App\Entity\da\DemandeAppro;

class RouteConstant
{
    // constante pour les routes de création
    public const CREATION = [
        DemandeAppro::TYPE_DA_AVEC_DIT        => 'da_new_avec_dit',
        DemandeAppro::TYPE_DA_REAPPRO_MENSUEL => 'da_new_reappro_mensuel',
        DemandeAppro::TYPE_DA_PARENT          => 'da_new_achat'
    ];

    // constantes pour les routes de détails
    public const DETAIL = [
        DemandeAppro::TYPE_DA_AVEC_DIT         => 'da_detail_avec_dit',
        DemandeAppro::TYPE_DA_DIRECT           => 'da_detail_direct',
        DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => 'da_detail_reappro',
        DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => 'da_detail_reappro',
    ];

    // constantes pour les routes de suppression
    public const DELETE = [
        DemandeAppro::TYPE_DA_AVEC_DIT  => 'da_delete_line_avec_dit',
        DemandeAppro::TYPE_DA_DIRECT    => 'da_delete_line_direct',
    ];

    // constantes pour les routes de propositions
    public const PROPOSITION = [
        DemandeAppro::TYPE_DA_AVEC_DIT        => 'da_proposition_ref_avec_dit',
        DemandeAppro::TYPE_DA_DIRECT          => 'da_proposition_direct',
        DemandeAppro::TYPE_DA_PARENT          => 'da_affectation_achat',
        DemandeAppro::TYPE_DA_REAPPRO_MENSUEL => 'da_validate_reappro_mensuel',
    ];
}
