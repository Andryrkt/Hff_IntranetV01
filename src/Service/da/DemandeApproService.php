<?php

namespace App\Service\da;

use App\Entity\da\DemandeAppro;

class DemandeApproService
{
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
            DemandeAppro::STATUT_SOUMIS_ATE           => true,
            DemandeAppro::STATUT_SOUMIS_APPRO         => true,
            DemandeAppro::STATUT_EN_COURS_CREATION    => true,
            DemandeAppro::STATUT_DEMANDE_DEVIS        => true,
            DemandeAppro::STATUT_DEVIS_A_RELANCER     => true,
            DemandeAppro::STATUT_AUTORISER_EMETTEUR   => true,
            DemandeAppro::STATUT_EN_COURS_PROPOSITION => true,
            DemandeAppro::STATUT_VALIDE               => [
                DemandeAppro::STATUT_DW_A_VALIDE => false,
                DemandeAppro::STATUT_DW_REFUSEE  => false,
            ],
        ],
        'appro' => [
            DemandeAppro::STATUT_SOUMIS_ATE           => true,
            DemandeAppro::STATUT_SOUMIS_APPRO         => true,
            DemandeAppro::STATUT_DEMANDE_DEVIS        => true,
            DemandeAppro::STATUT_DEVIS_A_RELANCER     => true,
            DemandeAppro::STATUT_EN_COURS_PROPOSITION => true,
            DemandeAppro::STATUT_VALIDE               => [
                DemandeAppro::STATUT_DW_A_VALIDE => false,
                DemandeAppro::STATUT_DW_REFUSEE  => false,
            ],
        ],
        'atelier' => [
            DemandeAppro::STATUT_SOUMIS_ATE          => true,
            DemandeAppro::STATUT_EN_COURS_CREATION   => true,
            DemandeAppro::STATUT_AUTORISER_EMETTEUR => true,
        ],
        'createur_da_directe' => [
            DemandeAppro::STATUT_SOUMIS_ATE          => true,
            DemandeAppro::STATUT_EN_COURS_CREATION   => true,
            DemandeAppro::STATUT_AUTORISER_EMETTEUR => true,
            DemandeAppro::STATUT_VALIDE              => [
                DemandeAppro::STATUT_DW_A_VALIDE => false,
                DemandeAppro::STATUT_DW_REFUSEE  => false,
                DemandeAppro::STATUT_DW_VALIDEE  => false,
            ],
        ],
    ];


    /**
     * Détermine si une Demande d'Approvisionnement (DA) doit être verrouillée
     * en fonction de son statut et du profil utilisateur.
     *
     * @param string      $statutDa  Statut actuel de la DA
     * @param string|null $statut    Statut complémentaire (OR ou DW)
     * @param bool        $estAdmin  Vrai si l'utilisateur est administrateur
     * @param bool        $estAppro  Vrai si l'utilisateur est approvisionneur
     * @param bool        $estAtelier Vrai si l'utilisateur est membre de l'atelier
     * @param bool        $estCreateurDaDirecte Vrai si l'utilisateur est le créateur d'une DA directe
     *
     * @return bool True si la DA doit être verrouillée, False sinon
     */
    public function estDaVerrouillee(string $statutDa, ?string $statut, bool $estAdmin, bool $estAppro, bool $estAtelier, bool $estCreateurDaDirecte): bool
    {
        $roles = [];

        if ($estAdmin) $roles[] = 'admin';
        if ($estAppro) $roles[] = 'appro';
        if ($estAtelier) $roles[] = 'atelier';
        if ($estCreateurDaDirecte) $roles[] = 'createur_da_directe';

        return $this->isDemandeVerrouillee($statutDa, $statut, $roles);
    }

    /**
     * Détermine si une Demande d'Approvisionnement (DA) doit être verrouillée
     * en fonction de son statut et des rôles de l'utilisateur.
     *
     * @param string        $statutDa Statut actuel de la DA
     * @param string|null   $statut   Statut complémentaire (OR ou DW)
     * @param string[]      $roles    Liste des rôles de l'utilisateur (ex: ['admin', 'appro'])
     *
     * @return bool True si la DA doit être verrouillée, False sinon
     */
    private function isDemandeVerrouillee(string $statutDa, ?string $statut, array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->canRoleEditDa($role, $statutDa, $statut)) return false; // déverrouillage si au moins un rôle est autorisé
        }

        return true; // verrouillé par défaut
    }

    /**
     * Vérifie si un rôle donné peut modifier une DA selon son statut.
     *
     * @param string      $role     Rôle utilisateur
     * @param string      $statutDa Statut de la DA
     * @param string|null $statut   Statut complémentaire (OR ou DW)
     *
     * @return bool
     */
    private function canRoleEditDa(string $role, string $statutDa, ?string $statut): bool
    {
        // rôle non défini
        if (!isset(self::PERMISSIONS[$role])) return false;

        $roleRules = self::PERMISSIONS[$role];

        // statut DA non autorisé
        if (!isset($roleRules[$statutDa])) return false;

        $allowed = $roleRules[$statutDa];

        // Cas où la valeur est booléenne (true ou false)
        if (is_bool($allowed)) return $allowed;

        // Cas où la valeur est un tableau de statuts complémentaires
        return isset($allowed[$statut]) ? $allowed[$statut] : true;
    }
}
