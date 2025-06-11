<?php

namespace App\Service;

use App\Controller\Controller;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class EmailService
{
    private $mailer;
    private $twig;
    private $twigMailer;

    public function __construct()
    {
        $this->twig = Controller::getTwig();

        $this->mailer = new PHPMailer(true);

        // Configurer les paramètres SMTP ici
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.gmail.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = 'hasina.andrianadison@hff.mg';
        $this->mailer->Password = 'pjld idch wnif byfm ';
        // $this->mailer->Username = 'noreply.email@hff.mg';
        // $this->mailer->Password = 'aztq lelp kpzm qhff';
        //$this->mailer->Password = '2b6615f71ff2a7';
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = 587;
        $this->mailer->CharSet = 'UTF-8';

        // Définir l'expéditeur par défaut
        $this->mailer->setFrom("noreply.email@hff.mg", 'noreply');

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

    public function sendEmail($to, $cc = [], $template, $variables = [], $attachments = [])
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
