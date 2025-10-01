<?php

namespace App\Service\da;

use App\Entity\da\DemandeAppro;

class DemandeApproService
{
    /**
     * Règles de déverrouillage par rôle utilisateur.
     * Chaque clé est un rôle, chaque valeur est la liste des statuts modifiables.
     */
    private const REGLES_DEVERROUILLAGE = [
        'admin' => [
            DemandeAppro::STATUT_EN_COURS_CREATION,
            DemandeAppro::STATUT_SOUMIS_APPRO,
            DemandeAppro::STATUT_VALIDE,
            DemandeAppro::STATUT_AUTORISER_MODIF_ATE,
            DemandeAppro::STATUT_SOUMIS_ATE,
        ],
        'appro' => [
            DemandeAppro::STATUT_VALIDE,
            DemandeAppro::STATUT_SOUMIS_ATE,
            DemandeAppro::STATUT_SOUMIS_APPRO,
        ],
        'atelier' => [
            DemandeAppro::STATUT_SOUMIS_ATE,
            DemandeAppro::STATUT_EN_COURS_CREATION,
            DemandeAppro::STATUT_AUTORISER_MODIF_ATE,
        ],
        'createur_da_directe' => [
            DemandeAppro::STATUT_SOUMIS_ATE,
            DemandeAppro::STATUT_DW_A_MODIFIER,
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
            if ($this->canRoleEditDa($role, $statutDa)) {
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
     *
     * @return bool
     */
    private function canRoleEditDa(string $role, string $statutDa): bool
    {
        return in_array($statutDa, self::REGLES_DEVERROUILLAGE[$role] ?? [], true);
    }
}
