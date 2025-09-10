<?php

namespace App\Service\da;

use App\Controller\Traits\lienGenerique;
use App\Entity\da\DemandeAppro;
use App\Service\EmailService;

class EmailDaService
{
    use lienGenerique;

    private const EMAIL_TEMPLATE = 'da/email/emailDa.html.twig';

    private $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    private function getUrlDetail(string $id, bool $avecDit = true): string
    {
        $template = $avecDit ? "demande-appro/detail-avec-dit" : "demande-appro/detail-direct";
        return $this->urlGenerique(str_replace('/', '', $_ENV['BASE_PATH_COURT'] ?? '') . "/$template/$id");
    }

    public function envoyerMailcreationDaAvecDit(DemandeAppro $demandeAppro, array $tab): void
    {
        $variables = [
            'tab' => $tab,
            'statut' => "newDa",
            'demandeAppro' => $demandeAppro,
            'action_url' => $this->getUrlDetail($demandeAppro->getId()),
        ];
        $subject = "{$demandeAppro->getNumeroDemandeAppro()} - Nouvelle demande d'approvisionnement créé";
        $this->envoyerEmail(DemandeAppro::MAIL_APPRO, $subject, $variables);
    }

    public function envoyerMailPropositionDaAvecDit(DemandeAppro $demandeAppro, array $tab): void
    {
        $variables = [
            'tab' => $tab,
            'statut' => "propositionDa",
            'demandeAppro' => $demandeAppro,
            'action_url' => $this->getUrlDetail($demandeAppro->getId()),
        ];
        $subject = "{$demandeAppro->getNumeroDemandeAppro()} - Proposition créee par l'Appro";
        $this->envoyerEmail($demandeAppro->getUser()->getMail(), $subject, $variables);
    }

    public function envoyerMailPropositionDaDirect(DemandeAppro $demandeAppro, array $tab): void
    {
        $variables = [
            'tab' => $tab,
            'statut' => "propositionDa",
            'demandeAppro' => $demandeAppro,
            'action_url' => $this->getUrlDetail($demandeAppro->getId(), false),
        ];
        $subject = "{$demandeAppro->getNumeroDemandeAppro()} - Proposition créee par l'Appro";
        $this->envoyerEmail($demandeAppro->getUser()->getMail(), $subject, $variables);
    }

    public function envoyerMailModificationDaAvecDit(DemandeAppro $demandeAppro, array $tab): void
    {
        $variables = [
            'tab' => $tab,
            'statut' => "modificationDa",
            'demandeAppro' => $demandeAppro,
            'action_url' => $this->getUrlDetail($demandeAppro->getId()),
        ];
        $subject = "{$demandeAppro->getNumeroDemandeAppro()} - Modification demande d'approvisionnement";
        $this->envoyerEmail(DemandeAppro::MAIL_APPRO, $subject, $variables);
    }

    public function envoyerMailModificationDaDirect(DemandeAppro $demandeAppro, array $tab): void
    {
        $variables = [
            'tab' => $tab,
            'statut' => "modificationDa",
            'demandeAppro' => $demandeAppro,
            'action_url' => $this->getUrlDetail($demandeAppro->getId(), false),
        ];
        $subject = "{$demandeAppro->getNumeroDemandeAppro()} - Modification demande d'achat";
        $this->envoyerEmail(DemandeAppro::MAIL_APPRO, $subject, $variables);
    }

    public function envoyerMailObservationDaAvecDit(DemandeAppro $demandeAppro, array $tab): void
    {
        $variables = [
            'tab' => $tab,
            'statut' => "commente",
            'demandeAppro' => $demandeAppro,
            'action_url' => $this->getUrlDetail($demandeAppro->getId()),
        ];
        $to = $tab['service'] == 'atelier' ? DemandeAppro::MAIL_APPRO : $demandeAppro->getUser()->getMail();
        $subject = "{$demandeAppro->getNumeroDemandeAppro()} - Observation ajoutée par le service " . strtoupper($tab['service']);
        $this->envoyerEmail($to, $subject, $variables);
    }

    public function envoyerMailObservationDaDirect(DemandeAppro $demandeAppro, array $tab): void
    {
        $variables = [
            'tab' => $tab,
            'statut' => "commente",
            'demandeAppro' => $demandeAppro,
            'action_url' => $this->getUrlDetail($demandeAppro->getId(), false),
        ];
        $to = $tab['service'] == 'appro' ? $demandeAppro->getUser()->getMail() : DemandeAppro::MAIL_APPRO;
        $subject = "{$demandeAppro->getNumeroDemandeAppro()} - Observation ajoutée par le service " . strtoupper($tab['service']);
        $this->envoyerEmail($to, $subject, $variables);
    }

    public function envoyerMailValidationDaAvecDitAuxAtelier(DemandeAppro $demandeAppro, array $resultatExport, array $tab): void
    {
        $variables = [
            'tab' => $tab,
            'statut' => "validationDa",
            'demandeAppro' => $demandeAppro,
            'resultatExport' => $resultatExport,
            'action_url' => $this->getUrlDetail($demandeAppro->getId()),
        ];
        $subject = "{$demandeAppro->getNumeroDemandeAppro()} - Proposition(s) validée(s) par l'" . strtoupper($tab['service']);
        $attachments = [$resultatExport['filePath'] => $resultatExport['fileName']];
        $this->envoyerEmail($demandeAppro->getUser()->getMail(), $subject, $variables, [], $attachments);
    }

    public function envoyerMailValidationDaDirectAuxService(DemandeAppro $demandeAppro, array $resultatExport, array $tab): void
    {
        $variables = [
            'tab' => $tab,
            'statut' => "validationDa",
            'demandeAppro' => $demandeAppro,
            'resultatExport' => $resultatExport,
            'action_url' => $this->getUrlDetail($demandeAppro->getId(), false),
        ];
        $subject = "{$demandeAppro->getNumeroDemandeAppro()} - Proposition(s) validée(s) par le service " . strtoupper($tab['service']);
        $attachments = [$resultatExport['filePath'] => $resultatExport['fileName']];
        $this->envoyerEmail($demandeAppro->getUser()->getMail(), $subject, $variables, [], $attachments);
    }

    public function envoyerMailValidationDaAvecDitAuxAppro(DemandeAppro $demandeAppro, array $resultatExport, array $tab): void
    {
        $variables = [
            'tab' => $tab,
            'statut' => "validationAteDa",
            'demandeAppro' => $demandeAppro,
            'resultatExport' => $resultatExport,
            'action_url' => $this->getUrlDetail($demandeAppro->getId()),
        ];
        $subject = "{$demandeAppro->getNumeroDemandeAppro()} - Proposition(s) validée(s) par l'" . strtoupper($tab['service']);
        $this->envoyerEmail(DemandeAppro::MAIL_APPRO, $subject, $variables);
    }

    public function envoyerMailValidationDaDirectAuxAppro(DemandeAppro $demandeAppro, array $resultatExport, array $tab): void
    {
        $variables = [
            'tab' => $tab,
            'statut' => "validationAteDa",
            'demandeAppro' => $demandeAppro,
            'resultatExport' => $resultatExport,
            'action_url' => $this->getUrlDetail($demandeAppro->getId(), false),
        ];
        $subject = "{$demandeAppro->getNumeroDemandeAppro()} - Proposition(s) validée(s) par le service " . strtoupper($tab['service']);
        $this->envoyerEmail(DemandeAppro::MAIL_APPRO, $subject, $variables);
    }

    public function envoyerMailValidationDaAvecDit(DemandeAppro $demandeAppro, array $resultatExport, array $tab): void
    {
        $this->envoyerMailValidationDaAvecDitAuxAtelier($demandeAppro, $resultatExport, $tab);
        $this->envoyerMailValidationDaAvecDitAuxAppro($demandeAppro, $resultatExport, $tab);
    }

    public function envoyerMailValidationDaDirect(DemandeAppro $demandeAppro, array $resultatExport, array $tab): void
    {
        $this->envoyerMailValidationDaDirectAuxService($demandeAppro, $resultatExport, $tab);
        $this->envoyerMailValidationDaDirectAuxAppro($demandeAppro, $resultatExport, $tab);
    }

    private function envoyerEmail(string $to, string $subject, array $variables, array $cc = [], array $attachments = []): void
    {
        try {
            $this->emailService->sendEmail(
                $to,
                $cc,
                $subject,
                self::EMAIL_TEMPLATE,
                $variables,
                $attachments
            );
        } catch (\Exception $e) {
            // Gérer l'erreur, par exemple en loggant
            error_log("Erreur d'envoi d'email pour la DA: " . $e->getMessage());
            // Optionnellement, relancer l'exception si l'appelant doit être notifié
            // throw $e;
        }
    }
}
