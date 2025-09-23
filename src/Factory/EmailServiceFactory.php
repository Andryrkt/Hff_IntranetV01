<?php

namespace App\Factory;

use App\Service\EmailService;
use Twig\Environment as TwigEnvironment;

class EmailServiceFactory
{
    private $twig;

    public function __construct(TwigEnvironment $twig)
    {
        $this->twig = $twig;
    }

    public function create(): EmailService
    {
        $mailerHost = $_ENV['MAILER_HOST'] ?? 'smtp.gmail.com';
        $mailerUser = $_ENV['MAILER_USER'] ?? 'noreply@hff.mg';
        $mailerPass = $_ENV['MAILER_PASSWORD'] ?? 'your_password_here';
        $mailerPort = (int)($_ENV['MAILER_PORT'] ?? 587);
        $fromEmail = $_ENV['MAILER_FROM_EMAIL'] ?? 'noreply.email@hff.mg';
        $fromName = $_ENV['MAILER_FROM_NAME'] ?? 'noreply';
        $bccRecipients = [
            'ranofimenjajam@gmail.com',
            'hasina.andrianadison@hff.mg'
        ];

        return new EmailService(
            $this->twig,
            $mailerHost,
            $mailerUser,
            $mailerPass,
            $mailerPort,
            $fromEmail,
            $fromName,
            $bccRecipients
        );
    }
}
