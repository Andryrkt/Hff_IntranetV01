<?php

namespace App\Constants\da;

use App\Entity\da\DemandeAppro;

class RouteConstant
{
    public const ROUTE_DETAIL_NAMES = [
        DemandeAppro::TYPE_DA_DIRECT           => 'da_detail_direct',
        DemandeAppro::TYPE_DA_AVEC_DIT         => 'da_detail_avec_dit',
        DemandeAppro::TYPE_DA_REAPPRO_MENSUEL  => 'da_detail_reappro',
        DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL => 'da_detail_reappro',
    ];
}
