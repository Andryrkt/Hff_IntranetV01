<?php

namespace App\Service\historiqueOperation\suivi;

use App\Entity\admin\utilisateur\Role;
use App\Service\EmailService;
use Doctrine\DBAL\Connection;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Mails d'alerte des tâches du suivi des opérations (l'alerte Outlook prévient même sans l'intranet ouvert) :
 * - nouvelle tâche de priorité haute et régression : à tous les intervenants (rôle INTERVENANT)
 * - assignation : à la personne assignée
 *
 * N'utilise pas EmailService::sendEmail : pas de copies fixes MAIL_CC / MAIL_BCC, pas de dd() en cas d'erreur
 * et pas d'étape dans le suivi (un envoi en échec créerait lui-même une tâche). Une alerte ne fait jamais échouer l'appelant.
 */
class AlerteTacheSuiviOperation
{
    /** @var array<int, array{0: string[], 1: string, 2: string}> */
    private static array $fileAttente = [];

    public static function nouvelleTache(Connection $connexion, int $id, string $typeOperation, string $etape, ?string $message): void
    {
        self::proteger(fn () => self::alerter(self::mailsIntervenants($connexion), "[Intranet] Nouveau bug urgent n° $id : $typeOperation – $etape",
            'Un nouveau problème de priorité <strong>haute</strong> vient d\'être détecté.', $id, $typeOperation, $etape, $message));
    }

    public static function regression(Connection $connexion, int $id, string $typeOperation, string $etape, ?string $message): void
    {
        self::proteger(fn () => self::alerter(self::mailsIntervenants($connexion), "[Intranet] Régression tâche n° $id : $typeOperation – $etape",
            'Un problème déjà corrigé <strong>est revenu</strong> : la tâche repasse « À faire ».', $id, $typeOperation, $etape, $message));
    }

    public static function assignation(Connection $connexion, int $id, string $nomUtilisateur, string $assignePar, string $typeOperation, string $etape, ?string $message): void
    {
        self::proteger(function () use ($connexion, $id, $nomUtilisateur, $assignePar, $typeOperation, $etape, $message) {
            $mail = $connexion->fetchOne('SELECT mail FROM users WHERE nom_utilisateur = ?', [$nomUtilisateur]);
            self::alerter($mail ? [$mail] : [], "[Intranet] Tâche n° $id assignée : $typeOperation – $etape",
                htmlspecialchars($assignePar) . ' vous a assigné cette tâche : saisissez un début et une échéance pour la planifier.', $id, $typeOperation, $etape, $message);
        });
    }

    // une alerte ne doit jamais faire échouer l'enregistrement de la tâche
    private static function proteger(callable $alerte): void
    {
        try {
            $alerte();
        } catch (\Throwable $e) {
            error_log('[AlerteTacheSuiviOperation] ' . $e->getMessage());
        }
    }

    /**
     * @return string[]
     */
    private static function mailsIntervenants(Connection $connexion): array
    {
        return $connexion->fetchFirstColumn(
            'SELECT u.mail FROM users u INNER JOIN user_roles ur ON ur.user_id = u.id WHERE ur.role_id = ? AND u.mail IS NOT NULL',
            [Role::ROLE_INTERVENANT]
        );
    }

    private static function alerter(array $destinataires, string $objet, string $intro, int $id, string $typeOperation, string $etape, ?string $message): void
    {
        $destinataires = array_values(array_unique(array_filter(array_map('trim', $destinataires), fn ($m) => filter_var($m, FILTER_VALIDATE_EMAIL))));
        if (empty($destinataires)) {
            return;
        }

        // lien absent hors requête web (ligne de commande) : pas d'hôte connu
        $lien = isset($_SERVER['HTTP_HOST'])
            ? 'http://' . $_SERVER['HTTP_HOST'] . '/' . trim($_ENV['BASE_PATH_COURT'] ?? '', '/') . '/admin/suivi-operation?vue=taches#tache-' . $id
            : null;

        $corps = '<p>' . $intro . '</p>'
            . '<p><strong>Tâche n° ' . $id . '</strong> – ' . htmlspecialchars($typeOperation) . ' : ' . htmlspecialchars($etape) . '</p>'
            . '<p style="color:#555">' . htmlspecialchars($message ?? '-') . '</p>'
            . ($lien ? '<p><a href="' . htmlspecialchars($lien) . '">Ouvrir la tâche dans l\'intranet</a></p>' : '');

        if (empty(self::$fileAttente)) {
            register_shutdown_function([self::class, 'envoyerFileAttente']);
        }
        self::$fileAttente[] = [$destinataires, $objet, $corps];
    }

    /**
     * Fonction d'arrêt : envoi après la réponse (le SMTP prend plusieurs secondes, l'utilisateur n'attend pas)
     */
    public static function envoyerFileAttente(): void
    {
        $file = self::$fileAttente;
        self::$fileAttente = [];
        if (empty($file)) {
            return;
        }

        ignore_user_abort(true);
        if (PHP_SAPI !== 'cli') {
            EmailService::libererNavigateur();
        }

        foreach ($file as [$destinataires, $objet, $corps]) {
            try {
                $mailer = self::creerMailer();
                foreach ($destinataires as $destinataire) {
                    $mailer->addAddress($destinataire);
                }
                $mailer->isHTML(true);
                $mailer->Subject = $objet;
                $mailer->Body    = $corps;
                $mailer->send();
            } catch (\Throwable $e) {
                error_log('[AlerteTacheSuiviOperation] Echec de l\'envoi (' . $objet . ') : ' . $e->getMessage());
            }
        }
    }

    // même configuration SMTP que EmailService
    private static function creerMailer(): PHPMailer
    {
        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host       = $_ENV['MAIL_HOST'];
        $mailer->SMTPAuth   = true;
        $mailer->Port       = $_ENV['MAIL_PORT'];
        $mailer->Username   = $_ENV['MAIL_USERNAME'];
        $mailer->Password   = $_ENV['MAIL_PASSWORD'];
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->CharSet    = $_ENV['MAIL_CHARSET'];
        $mailer->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);

        return $mailer;
    }
}
