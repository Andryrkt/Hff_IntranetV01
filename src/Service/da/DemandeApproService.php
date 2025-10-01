<?php

namespace App\Service\da;

use App\Entity\da\DemandeAppro;

class DemandeApproService
{
    /**
     * Règles de déverrouillage par rôle utilisateur.
     * Chaque clé est un rôle, chaque valeur est la liste des statuts de DA et statut OR ou DW modifiables.
     */
    private const PERMISSIONS = [
        'admin' => [
            DemandeAppro::STATUT_SOUMIS_ATE          => true,
            DemandeAppro::STATUT_SOUMIS_APPRO        => true,
            DemandeAppro::STATUT_EN_COURS_CREATION   => true,
            DemandeAppro::STATUT_AUTORISER_MODIF_ATE => true,
            DemandeAppro::STATUT_VALIDE              => [
                DemandeAppro::STATUT_DW_A_VALIDE => false,
                DemandeAppro::STATUT_DW_REFUSEE  => false,
            ],
        ],
        'appro' => [
            DemandeAppro::STATUT_SOUMIS_ATE          => true,
            DemandeAppro::STATUT_SOUMIS_APPRO        => true,
            DemandeAppro::STATUT_VALIDE              => [
                DemandeAppro::STATUT_DW_A_VALIDE => false,
                DemandeAppro::STATUT_DW_REFUSEE  => false,
            ],
        ],
        'atelier' => [
            DemandeAppro::STATUT_SOUMIS_ATE          => true,
            DemandeAppro::STATUT_EN_COURS_CREATION   => true,
            DemandeAppro::STATUT_AUTORISER_MODIF_ATE => true,
        ],
        'createur_da_directe' => [
            DemandeAppro::STATUT_SOUMIS_ATE          => true,
            DemandeAppro::STATUT_VALIDE              => [
                DemandeAppro::STATUT_DW_A_VALIDE => false,
                DemandeAppro::STATUT_DW_REFUSEE  => false,
                DemandeAppro::STATUT_DW_VALIDEE  => false,
            ],
        ],
    ];

    /**
     * Détermine si une Demande d'Approvisionnement (DA) doit être verrouillée
     * en fonction de son statut et des rôles de l'utilisateur.
     *
     * @param string   $statutDa Statut actuel de la DA
     * @param string   $statut   Statut complémentaire (OR ou DW)
     * @param string[] $roles    Liste des rôles de l'utilisateur (ex: ['admin', 'appro'])
     *
     * @return bool True si la DA doit être verrouillée, False sinon
     */
    public function isDemandeVerrouillee(string $statutDa, string $statut, array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->canRoleEditDa($role, $statutDa, $statut)) {
                return false; // déverrouillage si au moins un rôle est autorisé
            }
        }

        return true; // verrouillé par défaut
    }

    /**
     * Vérifie si un rôle donné peut modifier une DA selon son statut.
     *
     * @param string $role     Rôle utilisateur
     * @param string $statutDa Statut de la DA
     * @param string $statut   Statut complémentaire (OR ou DW)
     *
     * @return bool
     */
    private function canRoleEditDa(string $role, string $statutDa, string $statut): bool
    {
        // rôle non défini
        if (!isset(self::PERMISSIONS[$role])) {
            return false;
        }

        $roleRules = self::PERMISSIONS[$role];

        // statut DA non autorisé
        if (!isset($roleRules[$statutDa])) {
            return false;
        }

        $allowed = $roleRules[$statutDa];

        // Cas où la valeur est booléenne (true ou false)
        if (is_bool($allowed)) {
            return $allowed;
        }

        // Cas où la valeur est un tableau de statuts complémentaires
        return isset($allowed[$statut]) ? $allowed[$statut] : true;
    }
}
