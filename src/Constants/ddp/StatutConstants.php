<?php

namespace App\Constants\ddp;

class StatutConstants
{
    public const DDPL_A_TRANSMETTRE = 'DDPL à transmettre';
    public const BAP_A_TRANSMETTRE = 'BAP à transmettre';
    public const DDPR_A_TRANSMETTRE = 'DDPR à transmettre';
    public const DDPA_A_TRANSMETTRE = 'DDPA à transmettre';
    public const SOUMIS_A_VALIDATION = 'Soumis à validation';
    public const BAP_A_VALIDER_DIR_ADMIN = 'BAP à valider par Dir Admin';
    public const REFUSE_PAR_CHEF_DE_SERVICE = 'Refusé par Chef de Service';
    public const REFUSE_COMPTA = 'Refusé Compta';
    public const VALIDE = 'Validé';

    public const STATUT_A_TRANSMETTRE = [
        self::DDPL_A_TRANSMETTRE,
        self::BAP_A_TRANSMETTRE,
        self::DDPR_A_TRANSMETTRE,
        self::DDPA_A_TRANSMETTRE
    ];

    public const CSS_CLASS_MAP = [
        self::DDPL_A_TRANSMETTRE => 'statut-ddpl-a-transmettre',
        self::BAP_A_TRANSMETTRE => 'statut-bap-a-transmettre',
        self::DDPR_A_TRANSMETTRE => 'statut-ddpr-a-transmettre',
        self::DDPA_A_TRANSMETTRE => 'statut-ddpa-a-transmettre',
        self::SOUMIS_A_VALIDATION => 'statut-soumis-a-validation',
        self::BAP_A_VALIDER_DIR_ADMIN => 'statut-bap-a-valider-dir-admin',
        self::REFUSE_PAR_CHEF_DE_SERVICE => 'statut-refuse-par-chef-de-service',
        self::REFUSE_COMPTA => 'statut-refuse-compta',
        self::VALIDE => 'statut-valide',
    ];

    public static function getCssClass(string $statut): string
    {
        return self::CSS_CLASS_MAP[$statut] ?? '';
    }
}
