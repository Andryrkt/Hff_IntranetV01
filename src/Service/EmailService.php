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
        $this->mailer->Username = 'noreply.email@hff.mg';
        $this->mailer->Password = 'aztq lelp kpzm qhff';
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

    public function sendEmail($to, $cc = [],  $template, $variables = [])
    {
        try {


            // Create email content using the template
            $this->twigMailer->create($template, $variables);

            // Set the recipient
            $this->twigMailer->getPhpMailer()->addAddress($to);
            if ($cc !== null) {
                foreach ($cc as $c) {
                    $this->twigMailer->getPhpMailer()->addCC($c);
                }
            }

            // Send the email
            $this->twigMailer->send();

            return true;
        } catch (\Exception $e) {
            // Log the error message or handle it as needed
            dd('erreur: ' . $e->getMessage());
            return false;
        }
    }
}
