<?php

namespace App\Service\historiqueOperation\suivi;

use Doctrine\DBAL\Connection;

/**
 * Transforme une opération au résultat autre que OK (ECHEC, INTERROMPU, ALERTE) en tâche de correction
 * (table suivi_operation_tache).
 *
 * Les opérations qui cassent pour la même raison partagent la même signature : type d'opération + étape +
 * message débarrassé de ce qui change d'une fois à l'autre (numéros, chemins, valeurs entre guillemets).
 * Une nouvelle occurrence incrémente le compteur de la tâche existante ; si la tâche était RESOLU,
 * elle repasse en A_FAIRE et est marquée comme régression.
 *
 * Appelée par SuiviOperationService avec sa connexion DBAL, dans son executer() qui ne propage jamais d'erreur.
 */
class TacheSuiviOperation
{
    public const STATUT_A_TRIER  = 'A_TRIER';
    public const STATUT_A_FAIRE  = 'A_FAIRE';
    public const STATUT_EN_COURS = 'EN_COURS';
    public const STATUT_RESOLU   = 'RESOLU';
    public const STATUT_IGNORE   = 'IGNORE';

    public const LIBELLES_STATUT = [
        self::STATUT_A_TRIER  => 'À trier',
        self::STATUT_A_FAIRE  => 'À faire',
        self::STATUT_EN_COURS => 'En cours',
        self::STATUT_RESOLU   => 'Résolu',
        self::STATUT_IGNORE   => 'Ignoré',
    ];

    public const EFFORT_RAPIDE = 'RAPIDE';
    public const EFFORT_MOYEN  = 'MOYEN';
    public const EFFORT_LONG   = 'LONG';

    public const LIBELLES_EFFORT = [
        self::EFFORT_RAPIDE => 'Rapide (< 30 min)',
        self::EFFORT_MOYEN  => 'Moyen (½ à 1 jour)',
        self::EFFORT_LONG   => 'Long (plusieurs jours)',
    ];

    public const PRIORITE_HAUTE   = 'HAUTE';
    public const PRIORITE_NORMALE = 'NORMALE';
    public const PRIORITE_BASSE   = 'BASSE';

    public const LIBELLES_PRIORITE = [
        self::PRIORITE_HAUTE   => 'Haute',
        self::PRIORITE_NORMALE => 'Normale',
        self::PRIORITE_BASSE   => 'Basse',
    ];

    private const TABLE = 'suivi_operation_tache';

    /**
     * Crée la tâche correspondant au problème, ou ajoute une occurrence à la tâche existante
     *
     * @param string      $statutOperation ECHEC, INTERROMPU ou ALERTE
     * @param string      $date            date de l'occurrence (Y-m-d H:i:s.v)
     */
    public static function enregistrer(
        Connection $connexion,
        string $typeOperation,
        string $etape,
        string $statutOperation,
        ?string $message,
        string $idSuivi,
        ?string $numeroDocument,
        string $date
    ): void {
        $etapeType   = mb_substr(self::normaliser($etape), 0, 100);
        $messageType = $message !== null && trim($message) !== '' ? mb_substr(self::normaliser($message), 0, 500) : null;
        $signature   = sha1($typeOperation . '|' . $etapeType . '|' . $messageType);

        $occurrence = [
            'derniere_occurrence'     => $date,
            'dernier_id_suivi'        => $idSuivi,
            'dernier_numero_document' => $numeroDocument !== null ? mb_substr($numeroDocument, 0, 50) : null,
            'dernier_message'         => $message !== null ? mb_substr($message, 0, 1000) : null,
            'statut_operation'        => $statutOperation,
        ];

        if (self::ajouterOccurrence($connexion, $signature, $occurrence) > 0) {
            return;
        }

        try {
            $connexion->insert(self::TABLE, $occurrence + [
                'signature'           => $signature,
                'type_operation'      => $typeOperation,
                'etape'               => $etapeType,
                'message_type'        => $messageType,
                'premiere_occurrence' => $date,
                'nb_occurrences'      => 1,
                'statut'              => self::STATUT_A_TRIER,
                'priorite'            => $statutOperation === SuiviOperationService::STATUT_ECHEC ? self::PRIORITE_HAUTE : self::PRIORITE_NORMALE,
            ]);
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            // la même tâche vient d'être créée par une requête simultanée
            self::ajouterOccurrence($connexion, $signature, $occurrence);
        }
    }

    /**
     * Retire d'un message ce qui varie d'une occurrence à l'autre, pour regrouper les mêmes problèmes
     */
    public static function normaliser(string $texte): string
    {
        $texte = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $texte)));
        // valeurs entre guillemets (dossier, valeur saisie, nom de fichier...) ; une apostrophe collée à une lettre (l'opération) n'ouvre pas de guillemet
        $texte = preg_replace('/«[^»]*»|"[^"]*"|(?<!\p{L})\'[^\']*\'/u', '?', $texte);
        // chemins Windows et réseau (lecteur:\... ou \\serveur\...)
        $texte = preg_replace('/(?:[a-z]:|\\\\)\\\\[^\s|)]+/u', '<chemin>', $texte);
        // numéros (document, ligne, id, date, heure, durée)
        $texte = preg_replace('/[a-z]*\d[\w.\-\/:]*/u', '#', $texte);

        return trim(preg_replace('/\s+/u', ' ', $texte));
    }

    /**
     * Ajoute une occurrence à la tâche de cette signature (réouverte si elle était résolue)
     *
     * @return int nombre de lignes modifiées (0 si la tâche n'existe pas encore)
     */
    private static function ajouterOccurrence(Connection $connexion, string $signature, array $occurrence): int
    {
        // SQL Server évalue tous les SET avec les valeurs d'avant la mise à jour : les deux CASE voient l'ancien statut
        return $connexion->executeStatement(
            'UPDATE ' . self::TABLE . ' SET
                nb_occurrences = nb_occurrences + 1,
                derniere_occurrence = ?,
                dernier_id_suivi = ?,
                dernier_numero_document = ?,
                dernier_message = ?,
                statut_operation = ?,
                est_regression = CASE WHEN statut = ? THEN 1 ELSE est_regression END,
                statut = CASE WHEN statut = ? THEN ? ELSE statut END
            WHERE signature = ?',
            [
                $occurrence['derniere_occurrence'],
                $occurrence['dernier_id_suivi'],
                $occurrence['dernier_numero_document'],
                $occurrence['dernier_message'],
                $occurrence['statut_operation'],
                self::STATUT_RESOLU,
                self::STATUT_RESOLU,
                self::STATUT_A_FAIRE,
                $signature,
            ]
        );
    }
}
