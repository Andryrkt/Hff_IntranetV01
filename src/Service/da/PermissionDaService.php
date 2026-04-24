<?php

namespace App\Service\da;

use App\Constants\da\StatutDaConstant;
use App\Entity\da\DemandeAppro;

class PermissionDaService
{
    private const ALL_DA_TYPES = [
        DemandeAppro::TYPE_DA_AVEC_DIT,
        DemandeAppro::TYPE_DA_DIRECT,
        DemandeAppro::TYPE_DA_REAPPRO_MENSUEL,
        DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL,
        DemandeAppro::TYPE_DA_PARENT,
    ];

    /**
     * Règles de déverrouillage par rôle utilisateur.
     *
     * Chaque clé du tableau correspond à un rôle utilisateur.
     * Chaque valeur est un tableau associatif où :
     *   - La clé est un statut de DemandeAppro.
     *   - La valeur peut être :
     *       - `true` si le statut est modifiable,
     *       - `false` si le statut n’est pas modifiable,
     *       - un tableau associatif de sous-statuts pour des règles plus fines.
     *
     * @var array<string,array<int|string,bool|array<int|string,bool>>> Règles d'accès par rôle
     */
    private const PERMISSIONS = [
        'admin' => [
            'daType' => self::ALL_DA_TYPES,
            'statuts' => [
                StatutDaConstant::STATUT_SOUMIS_ATE           => true,
                StatutDaConstant::STATUT_SOUMIS_APPRO         => true,
                StatutDaConstant::STATUT_EN_COURS_CREATION    => true,
                StatutDaConstant::STATUT_DEMANDE_DEVIS        => true,
                StatutDaConstant::STATUT_DEVIS_A_RELANCER     => true,
                StatutDaConstant::STATUT_AUTORISER_EMETTEUR   => true,
                StatutDaConstant::STATUT_EN_COURS_PROPOSITION => true,
                StatutDaConstant::STATUT_VALIDE               => [
                    StatutDaConstant::STATUT_DW_A_VALIDE => false,
                    StatutDaConstant::STATUT_DW_REFUSEE  => false,
                ],
            ]
        ],
        'appro' => [
            'daType' => self::ALL_DA_TYPES,
            'statuts' => [
                StatutDaConstant::STATUT_SOUMIS_ATE           => true,
                StatutDaConstant::STATUT_SOUMIS_APPRO         => true,
                StatutDaConstant::STATUT_DEMANDE_DEVIS        => true,
                StatutDaConstant::STATUT_DEVIS_A_RELANCER     => true,
                StatutDaConstant::STATUT_EN_COURS_PROPOSITION => true,
                StatutDaConstant::STATUT_VALIDE               => [
                    StatutDaConstant::STATUT_DW_A_VALIDE => false,
                    StatutDaConstant::STATUT_DW_REFUSEE  => false,
                ],
            ]
        ],
        'atelier' => [
            'daType' => [
                DemandeAppro::TYPE_DA_AVEC_DIT
            ],
            'statuts' => [
                StatutDaConstant::STATUT_SOUMIS_ATE          => true,
                StatutDaConstant::STATUT_EN_COURS_CREATION   => true,
                StatutDaConstant::STATUT_AUTORISER_EMETTEUR => true,
            ]
        ],
        'createur_da_directe' => [
            'daType' => [
                DemandeAppro::TYPE_DA_DIRECT,
                DemandeAppro::TYPE_DA_REAPPRO_PONCTUEL,
            ],
            'statuts' => [
                StatutDaConstant::STATUT_SOUMIS_ATE          => true,
                StatutDaConstant::STATUT_EN_COURS_CREATION   => true,
                StatutDaConstant::STATUT_AUTORISER_EMETTEUR => true,
                StatutDaConstant::STATUT_VALIDE              => [
                    StatutDaConstant::STATUT_DW_A_VALIDE => false,
                    StatutDaConstant::STATUT_DW_REFUSEE  => false,
                    StatutDaConstant::STATUT_DW_VALIDEE  => false,
                ],
            ]
        ],
    ];


    /**
     * Détermine si une Demande d'Approvisionnement (DA) doit être verrouillée
     * en fonction de son statut et du profil utilisateur.
     *
     * @param int         $daType    Type de la DA
     * @param string      $statutDa  Statut actuel de la DA
     * @param string|null $statut    Statut complémentaire (OR ou DW)
     * @param bool        $estAdmin  Vrai si l'utilisateur est administrateur
     * @param bool        $estAppro  Vrai si l'utilisateur est approvisionneur
     * @param bool        $estAtelier Vrai si l'utilisateur est membre de l'atelier
     * @param bool        $estCreateurDaDirecte Vrai si l'utilisateur est le créateur d'une DA directe
     *
     * @return bool True si la DA doit être verrouillée, False sinon
     */
    public function estDaVerrouillee(int $daType, string $statutDa, ?string $statut, bool $estAdmin, bool $estAppro, bool $estAtelier, bool $estCreateurDaDirecte): bool
    {
        $roles = [];

        if ($estAdmin) $roles[] = 'admin';
        if ($estAppro) $roles[] = 'appro';
        if ($estAtelier) $roles[] = 'atelier';
        if ($estCreateurDaDirecte) $roles[] = 'createur_da_directe';

        foreach ($roles as $role) {
            if ($this->canRoleEditDa($role, $daType, $statutDa, $statut)) return false; // déverrouillage si au moins un rôle est autorisé
        }

        return true; // verrouillé par défaut
    }

    /**
     * Vérifie si un rôle donné peut modifier une DA selon son statut.
     *
     * @param string      $role     Rôle utilisateur
     * @param int         $daType   Type de la DA
     * @param string      $statutDa Statut de la DA
     * @param string|null $statut   Statut complémentaire (OR ou DW)
     *
     * @return bool
     */
    private function canRoleEditDa(string $role, int $daType, string $statutDa, ?string $statut): bool
    {
        // Rôle non défini
        if (!isset(self::PERMISSIONS[$role])) return false;

        $roleRules = self::PERMISSIONS[$role];

        // ✅ Vérification du type de DA autorisé pour ce rôle
        if (!in_array($daType, $roleRules['daType'], true)) return false;

        // ✅ Vérification du statut DA
        if (!isset($roleRules['statuts'][$statutDa])) return false;

        $allowed = $roleRules['statuts'][$statutDa];

        // Cas booléen direct
        if (is_bool($allowed)) return $allowed;

        // Cas avec sous-statut complémentaire (DW / OR)
        return $allowed[$statut] ?? true;
    }
}
