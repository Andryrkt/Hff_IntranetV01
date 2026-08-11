<?php

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

            // Ajouter le destinataire
            $mailer->addAddress($to);

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

            // Envoyer l'e-mail
            $this->twigMailer->send();

            return true;
        } catch (\Exception $e) {
            // Gérer l'erreur
            dd('erreur: ' . $e->getMessage());
            return false;
        }
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
