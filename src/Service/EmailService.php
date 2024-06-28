<?php

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class EmailService
{
    private $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);

        // Configurer les paramètres SMTP ici
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.gmail.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = 'hasina.andrianadison@hff.mg';
        $this->mailer->Password = 'vghe hxap jecp atge';
       //$this->mailer->Password = '2b6615f71ff2a7';
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = 587;

        // Définir l'expéditeur par défaut
        $this->mailer->setFrom("hasimanjaka.ratompoarinandro@hff.mg", 'Lanto Tsiafindrahasina');
        
        // Activer le débogage SMTP
        // $this->mailer->SMTPDebug = 2;
        // $this->mailer->Debugoutput = 'html';
    }

    public function setFrom($fromEmail, $fromName)
    {
        
        if (filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            $this->mailer->setFrom($fromEmail, $fromName);
          
        } else {
            throw new Exception('Invalid email address');
        }
    }

    public function sendEmail($to, $subject, $body, $altBody = '')
    {
        try {
            $this->mailer->addAddress($to);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;
            $this->mailer->AltBody = $altBody;

            $this->mailer->send();

         
            return true;
        } catch (\Exception $e) {

            dd('erreur'. $e->getMessage());
            // Log the error message or handle it as needed
            return false;
        }
    }
}