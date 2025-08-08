<?php

namespace App\Service\da;

use App\Controller\Traits\lienGenerique;
use App\Entity\da\DemandeAppro;
use App\Service\EmailService;

class EmailDaService
{
    use lienGenerique;

    /** 
     * Méthode pour envoyer une email de validation à l'Atelier
     * 
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param array $resultatExport résultat d'export
     * @param array $tab tableau de données à utiliser dans le corps du mail
     * 
     * @return void
     */
    public function envoyerMailValidationDaAvecDitAuxAtelier(DemandeAppro $demandeAppro, array $resultatExport, array $tab): void
    {
        $this->envoyerEmail([
            'to'        => $demandeAppro->getUser()->getMail(),
            'variables' => [
                'tab'            => $tab,
                'statut'         => "validationDa",
                'subject'        => "{$demandeAppro->getNumeroDemandeAppro()} - Proposition(s) validée(s) par l'" . strtoupper($tab['service']),
                'demandeAppro'   => $demandeAppro,
                'resultatExport' => $resultatExport,
                'action_url'     => $this->urlGenerique(str_replace('/', '', $_ENV['BASE_PATH_COURT']) . "/demande-appro/detail-avec-dit/{$demandeAppro->getId()}"),
            ],
            'attachments' => [
                $resultatExport['filePath'] => $resultatExport['fileName'],
            ],
        ]);
    }

    /** 
     * Méthode pour envoyer une email de validation à l'Appro
     * 
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param array $resultatExport résultat d'export
     * @param array $tab tableau de données à utiliser dans le corps du mail
     * 
     * @return void
     */
    public function envoyerMailValidationDaAvecDitAuxAppro(DemandeAppro $demandeAppro, array $resultatExport, array $tab): void
    {
        $this->envoyerEmail([
            'to'        => DemandeAppro::MAIL_APPRO,
            'variables' => [
                'tab'            => $tab,
                'statut'         => "validationAteDa",
                'subject'        => "{$demandeAppro->getNumeroDemandeAppro()} - Proposition(s) validée(s) par l'" . strtoupper($tab['service']),
                'demandeAppro'   => $demandeAppro,
                'resultatExport' => $resultatExport,
                'action_url'     => $this->urlGenerique(str_replace('/', '', $_ENV['BASE_PATH_COURT']) . "/demande-appro/detail-avec-dit/{$demandeAppro->getId()}"),
            ],
        ]);
    }

    /** 
     * Méthode pour envoyer un email
     */
    public function envoyerEmail(array $content): void
    {
        $emailService = new EmailService();

        $emailService->getMailer()->setFrom('noreply.email@hff.mg', 'noreply.da');

        $emailService->sendEmail($content['to'], $content['cc'] ?? [], "da/email/emailDa.html.twig", $content['variables'] ?? [], $content['attachments'] ?? []);
    }
}
