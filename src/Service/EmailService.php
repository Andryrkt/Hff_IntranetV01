<?php

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Twig\Environment as TwigEnvironment;

class EmailService
{
    private $mailer;
    private $twig;
    private $bccRecipients;

    public function __construct(
        TwigEnvironment $twig,
        string $mailerHost,
        string $mailerUser,
        string $mailerPass,
        int $mailerPort,
        string $fromEmail,
        string $fromName,
        array $bccRecipients = []
    ) {
        $this->twig = $twig;
        $this->bccRecipients = $bccRecipients;

        $this->mailer = new PHPMailer(true);

        // Configurer les paramètres SMTP à partir des arguments injectés
        $this->mailer->isSMTP();
        $this->mailer->Host = $mailerHost;
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $mailerUser;
        $this->mailer->Password = $mailerPass;
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = $mailerPort;
        $this->mailer->CharSet = 'UTF-8';

        // Définir l'expéditeur par défaut
        $this->mailer->setFrom($fromEmail, $fromName);
    }

    public function sendEmail(string $to, array $cc = [], string $subject, string $template, array $variables = [], array $attachments = [])
    {
        try {
            // Rendre le template HTML
            $htmlBody = $this->twig->render($template, $variables);

            $this->mailer->Subject = $subject;
            $this->mailer->Body = $htmlBody;
            $this->mailer->isHTML(true);

            // Ajouter le destinataire
            $this->mailer->addAddress($to);

            // Ajouter les CC
            if (!empty($cc)) {
                foreach ($cc as $c) {
                    $this->mailer->addCC($c);
                }
            }

            // Ajouter les BCC configurés
            if (!empty($this->bccRecipients)) {
                foreach ($this->bccRecipients as $bcc) {
                    $this->mailer->addBCC($bcc);
                }
            }

            // Ajouter les pièces jointes
            foreach ($attachments as $filePath => $fileName) {
                $this->mailer->addAttachment($filePath, $fileName);
            }

            // Envoyer l'e-mail
            $this->mailer->send();

            return true;
        } catch (Exception $e) {
            // Propager l'exception pour que l'appelant puisse la gérer
            // ou injecter un logger (ex: Psr\Log\LoggerInterface) et logguer l'erreur
            // error_log('Erreur EmailService: ' . $e->getMessage());
            throw $e;
        } finally {
            // Nettoyer les adresses et pièces jointes pour le prochain envoi
            $this->mailer->clearAddresses();
            $this->mailer->clearCCs();
            $this->mailer->clearBCCs();
            $this->mailer->clearAttachments();
        }
    }
}