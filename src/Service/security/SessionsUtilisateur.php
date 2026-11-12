<?php

namespace App\Service\security;

/**
 * Déconnexion forcée : supprime les fichiers de session PHP d'un utilisateur.
 * À sa requête suivante, la session est vide et il est renvoyé vers la page de connexion.
 */
class SessionsUtilisateur
{
    /** Dossier des fichiers de session (session.save_path peut être au format "N;chemin") */
    public static function dossier(): string
    {
        $chemin = (string) session_save_path();
        if (strpos($chemin, ';') !== false) {
            $chemin = substr($chemin, strrpos($chemin, ';') + 1);
        }

        return $chemin !== '' ? $chemin : sys_get_temp_dir();
    }

    /**
     * Supprime les sessions dont user_info.id vaut $idUtilisateur, sauf la session $idSessionExclue (celle de l'admin)
     *
     * @return array{supprimees: int, echecs: int}
     */
    public static function fermer(int $idUtilisateur, string $dossier, ?string $idSessionExclue = null): array
    {
        $resultat = ['supprimees' => 0, 'echecs' => 0];

        foreach (glob(rtrim($dossier, '/\\') . DIRECTORY_SEPARATOR . 'sess_*') ?: [] as $fichier) {
            if ($idSessionExclue !== null && basename($fichier) === 'sess_' . $idSessionExclue) {
                continue;
            }
            if (self::idUtilisateur((string) @file_get_contents($fichier)) !== $idUtilisateur) {
                continue;
            }
            // un fichier verrouillé par une requête en cours ne peut pas être supprimé sous Windows
            @unlink($fichier) ? $resultat['supprimees']++ : $resultat['echecs']++;
        }

        return $resultat;
    }

    /** user_info.id d'un contenu de session (format "nom|valeur sérialisée" des sessions Symfony) */
    public static function idUtilisateur(string $contenu): ?int
    {
        $position = strpos($contenu, '_sf2_attributes|');
        if ($position === false) {
            return null;
        }
        // unserialize s'arrête à la fin de la valeur ; aucun objet n'est recréé
        $attributs = @unserialize(substr($contenu, $position + strlen('_sf2_attributes|')), ['allowed_classes' => false]);

        return isset($attributs['user_info']['id']) ? (int) $attributs['user_info']['id'] : null;
    }
}
