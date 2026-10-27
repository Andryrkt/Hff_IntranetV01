<?php

namespace App\Service\docuware;

use App\Service\historiqueOperation\suivi\SuiviOperationService;

/**
 * Journalise chaque copie de fichier vers DocuWare dans la table journal_copie_docuware.
 *
 * Appelé depuis GeneratePdf (instancié sans injection de dépendances), d'où l'accès
 * au conteneur global. L'insertion passe par la connexion DBAL pour ne pas interférer
 * avec l'unité de travail Doctrine de l'appelant. Une erreur de journalisation ne doit
 * jamais bloquer la copie : elle est seulement écrite dans le log PHP.
 */
class JournalCopieDocuwareService
{
    public static function enregistrer(string $source, string $destination, bool $succes, int $nbTentatives = 1, ?string $messageErreur = null): void
    {
        // une copie échouée fait passer en échec l'étape DocuWare de la soumission suivie
        $suivi = SuiviOperationService::courant();
        if (!$succes && $suivi !== null) {
            $suivi->signalerProbleme('Copie DocuWare échouée pour ' . basename($destination) . ($messageErreur ? ' : ' . $messageErreur : ''));
        }

        try {
            global $container;
            if (!$container || !$container->has('doctrine.orm.default_entity_manager')) {
                return;
            }

            $sourceExiste = is_file($source);

            $container->get('doctrine.orm.default_entity_manager')->getConnection()->insert('journal_copie_docuware', [
                'date_copie'       => (new \DateTime('now', new \DateTimeZone('Indian/Antananarivo')))->format('Y-m-d H:i:s'),
                'utilisateur'      => self::getUtilisateur(),
                'url'              => mb_substr($_SERVER['REQUEST_URI'] ?? 'cli', 0, 500),
                'fichier_source'   => mb_substr($source, 0, 500),
                'source_existe'    => $sourceExiste ? 1 : 0,
                'taille_source'    => $sourceExiste ? filesize($source) : null,
                'destination'      => mb_substr($destination, 0, 500),
                'dossier_docuware' => mb_substr(basename(dirname($destination)), 0, 150),
                'nom_fichier'      => mb_substr(basename($destination), 0, 255),
                'succes'           => $succes ? 1 : 0,
                'nb_tentatives'    => $nbTentatives,
                'message_erreur'   => $messageErreur !== null ? mb_substr($messageErreur, 0, 1000) : null,
                'est_vu'           => $succes ? 1 : 0, // seuls les échecs sont à consulter
            ]);
        } catch (\Throwable $e) {
            error_log("[JournalCopieDocuware] Echec de la journalisation de la copie $source -> $destination : " . $e->getMessage());
        }
    }

    /**
     * Nom de l'utilisateur connecté, lu dans la session sans la démarrer
     */
    private static function getUtilisateur(): ?string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return null;
        }

        return $_SESSION['_sf2_attributes']['user_info']['username'] ?? null;
    }
}
