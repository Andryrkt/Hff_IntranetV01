<?php

namespace App\Service\historiqueOperation\suivi;

use Doctrine\DBAL\Connection;

/**
 * Transforme une opération au résultat autre que OK (ECHEC, INTERROMPU, ALERTE) en tâche de correction
 * (table suivi_operation_tache).
 *
 * Les opérations qui cassent pour la même raison partagent la même signature : type d'opération + étape +
 * message débarrassé de ce qui change d'une fois à l'autre (numéros, chemins, valeurs entre guillemets).
 * Une nouvelle occurrence incrémente le compteur de la tâche existante ; si la tâche était RESOLU ou DEPLOYE,
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
    public const STATUT_DEPLOYE  = 'DEPLOYE';
    public const STATUT_IGNORE   = 'IGNORE';

    public const LIBELLES_STATUT = [
        self::STATUT_A_TRIER  => 'À trier',
        self::STATUT_A_FAIRE  => 'À faire',
        self::STATUT_EN_COURS => 'En cours',
        self::STATUT_RESOLU   => 'Résolu',
        self::STATUT_DEPLOYE  => 'Déployé (terminé)',
        self::STATUT_IGNORE   => 'Ignoré',
    ];

    // corrigée (résolue, puis déployée en production) : date et auteur de résolution conservés
    public const STATUTS_CORRIGES = [self::STATUT_RESOLU, self::STATUT_DEPLOYE];

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
     * @param bool        $alerter         mail aux intervenants (nouvelle tâche haute, régression) ; false pour la reprise de l'historique
     *
     * @return int|null id de la tâche
     */
    public static function enregistrer(
        Connection $connexion,
        string $typeOperation,
        string $etape,
        string $statutOperation,
        ?string $message,
        string $idSuivi,
        ?string $numeroDocument,
        string $date,
        bool $alerter = true
    ): ?int {
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

        // statut avant l'occurrence : une tâche corrigée qui repasse "à faire" est une régression à signaler
        $ancienStatut = $alerter ? $connexion->fetchOne('SELECT statut FROM ' . self::TABLE . ' WHERE signature = ?', [$signature]) : false;

        if (self::ajouterOccurrence($connexion, $signature, $occurrence) > 0) {
            $id = self::idParSignature($connexion, $signature);
            if ($id !== null && in_array($ancienStatut, self::STATUTS_CORRIGES, true)
                && $connexion->fetchOne('SELECT statut FROM ' . self::TABLE . ' WHERE id = ?', [$id]) === self::STATUT_A_FAIRE) {
                AlerteTacheSuiviOperation::regression($connexion, $id, $typeOperation, $etape, $occurrence['dernier_message']);
            }

            return $id;
        }

        try {
            $priorite = $statutOperation === SuiviOperationService::STATUT_ECHEC ? self::PRIORITE_HAUTE : self::PRIORITE_NORMALE;
            $connexion->insert(self::TABLE, $occurrence + [
                'signature'           => $signature,
                'type_operation'      => $typeOperation,
                'etape'               => $etapeType,
                'message_type'        => $messageType,
                'premiere_occurrence' => $date,
                'nb_occurrences'      => 1,
                'statut'              => self::STATUT_A_TRIER,
                'priorite'            => $priorite,
            ]);

            $id = (int) $connexion->lastInsertId();
            if ($alerter && $priorite === self::PRIORITE_HAUTE) {
                AlerteTacheSuiviOperation::nouvelleTache($connexion, $id, $typeOperation, $etape, $occurrence['dernier_message']);
            }

            return $id;
        } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
            // la même tâche vient d'être créée par une requête simultanée
            self::ajouterOccurrence($connexion, $signature, $occurrence);

            return self::idParSignature($connexion, $signature);
        }
    }

    /**
     * Crée les tâches des opérations enregistrées avant la mise en place des tâches (étapes sans id_tache),
     * des plus anciennes aux plus récentes. Pour chaque opération en problème, retient la première étape
     * non OK terminée, comme le fait le suivi en direct.
     *
     * @return int nombre d'opérations rattachées à une tâche
     */
    public static function reprendreHistorique(Connection $connexion): int
    {
        $lignes = $connexion->fetchAllAssociative(
            "SELECT e.id_suivi, e.type_operation, e.etape, e.statut, e.message, e.numero_document,
                    CONVERT(VARCHAR(23), COALESCE(e.date_fin, e.date_debut), 121) AS date_occurrence
            FROM historique_operation_etape e
            WHERE e.id = (
                SELECT TOP 1 p.id FROM historique_operation_etape p
                WHERE p.id_suivi = e.id_suivi AND p.statut IN (?, ?, ?)
                ORDER BY COALESCE(p.date_fin, p.date_debut), p.ordre DESC
            )
            AND NOT EXISTS (SELECT 1 FROM historique_operation_etape t WHERE t.id_suivi = e.id_suivi AND t.id_tache IS NOT NULL)
            ORDER BY date_occurrence",
            [SuiviOperationService::STATUT_ECHEC, SuiviOperationService::STATUT_INTERROMPU, SuiviOperationService::STATUT_ALERTE]
        );

        foreach ($lignes as $ligne) {
            // le message enregistré contient aussi les informations notées et le résumé SQL : on ne garde que l'erreur
            $message = $ligne['message'] !== null ? trim(explode(' | ', $ligne['message'])[0]) : null;
            $idTache = self::enregistrer(
                $connexion,
                $ligne['type_operation'],
                $ligne['etape'],
                $ligne['statut'],
                $message,
                $ligne['id_suivi'],
                $ligne['numero_document'],
                $ligne['date_occurrence'],
                false // problèmes passés : pas d'alerte
            );
            if ($idTache !== null) {
                $connexion->update('historique_operation_etape', ['id_tache' => $idTache], ['id_suivi' => $ligne['id_suivi']]);
            }
        }

        return count($lignes);
    }

    private static function idParSignature(Connection $connexion, string $signature): ?int
    {
        $id = $connexion->fetchOne('SELECT id FROM ' . self::TABLE . ' WHERE signature = ?', [$signature]);

        return $id !== false ? (int) $id : null;
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
        // SQL Server évalue tous les SET avec les valeurs d'avant la mise à jour : chaque CASE voit l'ancienne ligne.
        // Une occurrence plus ancienne (reprise de l'historique) ne remplace pas la dernière occurrence connue
        // et ne réouvre pas une tâche résolue après elle.
        // (paramètres positionnels : un paramètre nommé répété n'est pas accepté par tous les drivers PDO)
        $date    = $occurrence['derniere_occurrence'];
        $resolu  = self::STATUT_RESOLU;
        $deploye = self::STATUT_DEPLOYE;

        return $connexion->executeStatement(
            'UPDATE ' . self::TABLE . ' SET
                nb_occurrences = nb_occurrences + 1,
                premiere_occurrence = CASE WHEN ? < premiere_occurrence THEN ? ELSE premiere_occurrence END,
                derniere_occurrence = CASE WHEN ? >= derniere_occurrence THEN ? ELSE derniere_occurrence END,
                dernier_id_suivi = CASE WHEN ? >= derniere_occurrence THEN ? ELSE dernier_id_suivi END,
                dernier_numero_document = CASE WHEN ? >= derniere_occurrence THEN ? ELSE dernier_numero_document END,
                dernier_message = CASE WHEN ? >= derniere_occurrence THEN ? ELSE dernier_message END,
                statut_operation = CASE WHEN ? >= derniere_occurrence THEN ? ELSE statut_operation END,
                est_regression = CASE WHEN statut IN (?, ?) AND ? > COALESCE(date_resolution, premiere_occurrence) THEN 1 ELSE est_regression END,
                statut = CASE WHEN statut IN (?, ?) AND ? > COALESCE(date_resolution, premiere_occurrence) THEN ? ELSE statut END
            WHERE signature = ?',
            [
                $date, $date,
                $date, $date,
                $date, $occurrence['dernier_id_suivi'],
                $date, $occurrence['dernier_numero_document'],
                $date, $occurrence['dernier_message'],
                $date, $occurrence['statut_operation'],
                $resolu, $deploye, $date,
                $resolu, $deploye, $date, self::STATUT_A_FAIRE,
                $signature,
            ]
        );
    }
}
