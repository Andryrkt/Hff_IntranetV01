<?php

namespace App\Constants\da;

use App\Entity\da\DaSoumissionBc;
use App\Entity\da\DemandeAppro;
use App\Entity\dit\DitOrsSoumisAValidation;

class StatutConstant
{
    public const CSS_CLASS_MAP_STATUT_DA = [
        DemandeAppro::STATUT_VALIDE               => 'bg-bon-achat-valide',
        DemandeAppro::STATUT_CLOTUREE             => 'bg-bon-achat-valide',
        DemandeAppro::STATUT_TERMINER             => 'bg-primary text-white',
        DemandeAppro::STATUT_SOUMIS_ATE           => 'bg-proposition-achat',
        DemandeAppro::STATUT_DW_A_VALIDE          => 'bg-soumis-validation',
        DemandeAppro::STATUT_SOUMIS_APPRO         => 'bg-demande-achat',
        DemandeAppro::STATUT_REFUSE_APPRO         => 'bg-refuse-appro',
        DemandeAppro::STATUT_DEMANDE_DEVIS        => 'bg-demande-devis',
        DemandeAppro::STATUT_DEVIS_A_RELANCER     => 'bg-devis-a-relancer',
        DemandeAppro::STATUT_EN_COURS_CREATION    => 'bg-en-cours-creation',
        DemandeAppro::STATUT_AUTORISER_EMETTEUR   => 'bg-creation-demande-initiale',
        DemandeAppro::STATUT_EN_COURS_PROPOSITION => 'bg-en-cours-proposition',
    ];

    public const CSS_CLASS_MAP_STATUT_OR = [
        'OR - ' .DitOrsSoumisAValidation::STATUT_VALIDE                     => 'bg-or-valide',
        'OR - ' .DitOrsSoumisAValidation::STATUT_A_RESOUMETTRE_A_VALIDATION => 'bg-a-resoumettre-a-validation',
        'OR - ' .DitOrsSoumisAValidation::STATUT_A_VALIDER_CA               => 'bg-or-valider-ca',
        'OR - ' .DitOrsSoumisAValidation::STATUT_A_VALIDER_DT               => 'bg-or-valider-dt',
        'OR - ' .DitOrsSoumisAValidation::STATUT_A_VALIDER_CLIENT           => 'bg-or-valider-client',
        'OR - ' .DitOrsSoumisAValidation::STATUT_MODIF_DEMANDE_PAR_CA       => 'bg-modif-demande-ca',
        'OR - ' .DitOrsSoumisAValidation::STATUT_MODIF_DEMANDE_PAR_CLIENT   => 'bg-modif-demande-client',
        'OR - ' .DitOrsSoumisAValidation::STATUT_REFUSE_CA                  => 'bg-or-non-valide',
        'OR - ' .DitOrsSoumisAValidation::STATUT_REFUSE_CLIENT              => 'bg-or-non-valide',
        'OR - ' .DitOrsSoumisAValidation::STATUT_REFUSE_DT                  => 'bg-or-non-valide',
        'OR - ' .DitOrsSoumisAValidation::STATUT_SOUMIS_A_VALIDATION        => 'bg-or-soumis-validation',
        DemandeAppro::STATUT_DW_A_VALIDE                           => 'bg-or-soumis-validation',
        DemandeAppro::STATUT_DW_VALIDEE                            => 'bg-or-valide',
        DemandeAppro::STATUT_DW_A_MODIFIER                         => 'bg-modif-demande-client',
        DemandeAppro::STATUT_DW_REFUSEE                            => 'bg-or-non-valide',
    ];

    public const CSS_CLASS_MAP_STATUT_BC = [
        DaSoumissionBc::STATUT_A_EDITER                 => 'bg-bc-a-editer',
        DaSoumissionBc::STATUT_A_GENERER                => 'bg-bc-a-generer',
        DaSoumissionBc::STATUT_A_SOUMETTRE_A_VALIDATION => 'bg-bc-a-soumettre-a-validation',
        DaSoumissionBc::STATUT_A_ENVOYER_AU_FOURNISSEUR => 'bg-bc-a-envoyer-au-fournisseur',
        DaSoumissionBc::STATUT_SOUMISSION               => 'bg-bc-soumission',
        DaSoumissionBc::STATUT_A_VALIDER_DA             => 'bg-bc-a-valider-da',
        DaSoumissionBc::STATUT_NON_DISPO                => 'bg-bc-non-dispo',
        DaSoumissionBc::STATUT_VALIDE                   => 'bg-bc-valide',
        DaSoumissionBc::STATUT_CLOTURE                  => 'bg-bc-cloture',
        DaSoumissionBc::STATUT_REFUSE                   => 'bg-bc-refuse',
        DaSoumissionBc::STATUT_BC_ENVOYE_AU_FOURNISSEUR => 'bg-bc-envoye-au-fournisseur',
        DaSoumissionBc::STATUT_PAS_DANS_OR              => 'bg-bc-pas-dans-or',
        'Non validé'                                    => 'bg-bc-non-valide',
        //statut pour DA Reappro
        DaSoumissionBc::STATUT_CESSION_A_GENERER        => 'bg-bc-cession-a-generer',
        DaSoumissionBc::STATUT_EN_COURS_DE_PREPARATION  => 'bg-bc-en-cours-de-preparation',
        //statut pour DA Reappro, DA direct, DA via OR
        DaSoumissionBc::STATUT_TOUS_LIVRES              => 'tout-livre',
        DaSoumissionBc::STATUT_PARTIELLEMENT_LIVRE      => 'partiellement-livre',
        DaSoumissionBc::STATUT_PARTIELLEMENT_DISPO      => 'partiellement-dispo',
        DaSoumissionBc::STATUT_COMPLET_NON_LIVRE        => 'complet-non-livre',
    ];

    public static function getCssClassDa(string $statut): string
    {
        return self::CSS_CLASS_MAP_STATUT_DA[$statut] ?? '';
    }

    public static function getCssClassOr(string $statut): string
    {
        return self::CSS_CLASS_MAP_STATUT_OR[$statut] ?? '';
    }

    public static function getCssClassBc(string $statut): string
    {
        return self::CSS_CLASS_MAP_STATUT_BC[$statut] ?? '';
    }
}
