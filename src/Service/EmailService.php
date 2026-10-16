<?php

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Service\historiqueOperation\suivi\SuiviOperationService;

class EmailService
{
    private $mailer;
    private $twig;
    private $twigMailer;

    public function __construct($twig)
    {
        $this->twig = $twig;

        $this->mailer = new PHPMailer(true);

        // Configurer les paramètres SMTP ici
        $this->mailer->isSMTP();
        $this->mailer->Host       = $_ENV['MAIL_HOST'];
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Port       = $_ENV['MAIL_PORT'];
        $this->mailer->Username   = $_ENV['MAIL_USERNAME'];
        $this->mailer->Password   = $_ENV['MAIL_PASSWORD'];
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->CharSet    = $_ENV['MAIL_CHARSET'];

        // Définir l'expéditeur par défaut
        $this->mailer->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);

        // Activer le débogage SMTP
        // $this->mailer->SMTPDebug = 2;
        // $this->mailer->Debugoutput = 'html';

        $this->twigMailer = new TwigMailerService($this->mailer, $this->twig);
    }

    public function setFrom($fromEmail, $fromName)
    {

        if (filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            $this->mailer->setFrom($fromEmail, $fromName);
        } else {
            throw new Exception('Invalid email address');
        }
    }

    public function sendEmail($to, $cc, $template, $variables = [], $attachments = [])
    {
        try {
            // Créer le contenu de l'email via le template
            $this->twigMailer->create($template, $variables);

            // Obtenir l'instance de PHPMailer
            $mailer = $this->twigMailer->getPhpMailer();

            // Ajouter le ou les destinataires
            $recipients = is_array($to) ? $to : [$to];
            foreach ($recipients as $recipient) {
                $mailer->addAddress($recipient);
            }
            // Ajouter les CC
            if ($cc !== null) {
                foreach ($cc as $c) {
                    $mailer->addCC($c);
                }
            }

            // Ajout de CC
            $mailBccEntries = explode(';', $_ENV['MAIL_CC']);
            foreach ($mailBccEntries as $entry) {
                [$name, $email] = array_map('trim', explode(':', $entry));
                $mailer->addCC($email, $name);
            }

            // ajout du BCC
            $mailBccEntries = explode(';', $_ENV['MAIL_BCC']);
            foreach ($mailBccEntries as $entry) {
                [$name, $email] = array_map('trim', explode(':', $entry));
                $mailer->addBCC($email, $name);
            }

            // Ajouter les pièces jointes
            foreach ($attachments as $filePath => $fileName) {
                $mailer->addAttachment($filePath, $fileName);
            }

            // objet et destinataires affichés dans le suivi des opérations (copies fixes MAIL_CC / MAIL_BCC non reprises)
            $resume = $this->resumeMail($mailer->Subject, $recipients, $cc);

            // l'envoi SMTP prend plusieurs secondes : il est fait après la réponse, l'utilisateur n'attend pas
            if (self::envoiApresReponse()) {
                self::mettreEnFile($this, $resume);
                return true;
            }

            // Envoyer l'e-mail
            $this->twigMailer->send();

            SuiviOperationService::noter($resume);

            return true;
        } catch (\Exception $e) {
            if (isset($mailer)) {
                SuiviOperationService::noter('Échec de l\'envoi : ' . $e->getMessage() . ' · ' . $this->resumeMail($mailer->Subject, $recipients ?? [], $cc));
            }
            // Gérer l'erreur
            dd('erreur: ' . $e->getMessage());
            return false;
        }
    }


    /**
     * Mails préparés, envoyés à la fin de la requête une fois la réponse transmise au navigateur
     * @var array<int, array{0: self, 1: string}>
     */
    private static array $fileAttente = [];

    /**
     * Envoi après la réponse : en web uniquement (pas en ligne de commande), désactivable par MAIL_ENVOI_APRES_REPONSE=0
     */
    private static function envoiApresReponse(): bool
    {
        return PHP_SAPI !== 'cli' && ($_ENV['MAIL_ENVOI_APRES_REPONSE'] ?? '1') !== '0';
    }

    private static function mettreEnFile(self $service, string $resume): void
    {
        if (empty(self::$fileAttente)) {
            register_shutdown_function([self::class, 'envoyerFileAttente']);
        }
        self::$fileAttente[] = [$service, $resume];

        SuiviOperationService::noter('Envoi programmé après la réponse · ' . $resume);
    }

    /**
     * Fonction d'arrêt : transmet la réponse au navigateur, puis envoie les mails en attente.
     * Chaque envoi est une étape "Envoi du mail" du suivi des opérations (durée réelle, erreur SMTP éventuelle).
     */
    public static function envoyerFileAttente(): void
    {
        $file = self::$fileAttente;
        self::$fileAttente = [];
        if (empty($file)) {
            return;
        }

        ignore_user_abort(true); // continuer même si le navigateur a déjà fermé la connexion
        self::libererNavigateur();

        foreach ($file as [$service, $resume]) {
            try {
                SuiviOperationService::suivre('Envoi du mail', function () use ($service, $resume) {
                    SuiviOperationService::noter($resume);
                    $service->twigMailer->send();
                });
            } catch (\Throwable $e) {
                // l'étape du suivi est déjà en échec avec le message ; on passe au mail suivant
                error_log('[EmailService] Echec de l\'envoi du mail (' . $resume . ') : ' . $e->getMessage());
            }
        }
    }

    /**
     * Ferme la session et envoie la réponse complète au navigateur (PHP en module Apache : pas de fastcgi_finish_request),
     * pour que la page suivante s'affiche sans attendre la fin des envois
     */
    private static function libererNavigateur(): void
    {
        // la session est verrouillée jusqu'à sa fermeture : la page suivante attendrait sinon la fin des envois
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
            return;
        }

        if (headers_sent()) {
            flush();
            return;
        }

        // récupère tout ce qui est en tampon (du plus externe au plus interne) et annonce sa taille exacte
        $contenu = '';
        while (ob_get_level() > 0) {
            $contenu = ob_get_clean() . $contenu;
        }
        header('Connection: close');
        header('Content-Encoding: none');
        header('Content-Length: ' . strlen($contenu));
        echo $contenu;
        flush();
    }

    /**
     * Résumé d'un mail pour le suivi des opérations : objet, destinataires et copies
     */
    private function resumeMail(?string $objet, array $destinataires, ?array $copies): string
    {
        $resume = 'Objet : ' . ($objet ?: '-') . ' · À : ' . (implode(', ', array_filter($destinataires)) ?: '-');
        if (!empty(array_filter((array) $copies))) {
            $resume .= ' · Cc : ' . implode(', ', array_filter($copies));
        }

        return $resume;
    }

    /**
     * Get the value of mailer
     */
    public function getMailer()
    {
        return $this->mailer;
    }

    /**
     * Set the value of mailer
     *
     * @return  self
     */
    public function setMailer($mailer)
    {
        $this->mailer = $mailer;

        return $this;
    }
}
