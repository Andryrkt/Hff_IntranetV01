<?php

namespace App\Service\da;

use App\Controller\Traits\lienGenerique;
use App\Entity\da\DemandeAppro;
use App\Service\EmailService;

class EmailDaService
{
    use lienGenerique;
    private $emailTemplate;

    public function __construct()
    {
        $this->emailTemplate = "da/email/emailDa.html.twig";
    }

    /** 
     * Fonction pour obtenir l'url du détail de la DA
     * @param string $id id de la DA
     * @param bool $avecDit paramètre booléen pour indiquer si c'est avec DIT ou non
     */
    private function getUrlDetail(string $id, bool $avecDit = true)
    {
        $template = $avecDit ? "demande-appro/detail-avec-dit" : "demande-appro/detail-direct";
        return $this->urlGenerique(str_replace('/', '', $_ENV['BASE_PATH_COURT']) . "/$template/$id");
    }

    /** 
     * Méthode pour envoyer une email pour la création d'une DA avec DIT
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param array $tab tableau de données à utiliser dans le corps du mail
     */
    public function envoyerMailcreationDaAvecDit(DemandeAppro $demandeAppro, array $tab)
    {
        $this->envoyerEmail([
            'to'        => DemandeAppro::MAIL_APPRO,
            'variables' => [
                'tab'            => $tab,
                'statut'         => "newDa",
                'subject'        => "{$demandeAppro->getNumeroDemandeAppro()} - Nouvelle demande d'approvisionnement créé",
                'demandeAppro'   => $demandeAppro,
                'action_url'     => $this->getUrlDetail($demandeAppro->getId()),
            ],
        ]);
    }

    /** 
     * Méthode pour envoyer une email de propositions pour une DA avec DIT
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param array $tab tableau de données à utiliser dans le corps du mail
     */
    public function envoyerMailPropositionDaAvecDit(DemandeAppro $demandeAppro, array $tab)
    {
        $this->envoyerEmail([
            'to'        => $demandeAppro->getUser()->getMail(),
            'variables' => [
                'tab'            => $tab,
                'statut'         => "propositionDa",
                'subject'        => "{$demandeAppro->getNumeroDemandeAppro()} - Proposition créee par l'Appro",
                'demandeAppro'   => $demandeAppro,
                'action_url'     => $this->getUrlDetail($demandeAppro->getId()),
            ],
        ]);
    }

    /** 
     * Méthode pour envoyer une email de propositions pour une DA directe
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param array $tab tableau de données à utiliser dans le corps du mail
     */
    public function envoyerMailPropositionDaDirect(DemandeAppro $demandeAppro, array $tab)
    {
        $this->envoyerEmail([
            'to'        => $demandeAppro->getUser()->getMail(),
            'variables' => [
                'tab'            => $tab,
                'statut'         => "propositionDa",
                'subject'        => "{$demandeAppro->getNumeroDemandeAppro()} - Proposition créee par l'Appro",
                'demandeAppro'   => $demandeAppro,
                'action_url'     => $this->getUrlDetail($demandeAppro->getId(), false),
            ],
        ]);
    }

    /** 
     * Méthode pour envoyer une email de modifications pour une DA avec DIT
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param array $tab tableau de données à utiliser dans le corps du mail
     */
    public function envoyerMailModificationDaAvecDit(DemandeAppro $demandeAppro, array $tab)
    {
        $this->envoyerEmail([
            'to'        => DemandeAppro::MAIL_APPRO,
            'variables' => [
                'tab'            => $tab,
                'statut'         => "modificationDa",
                'subject'        => "{$demandeAppro->getNumeroDemandeAppro()} - Modification demande d'approvisionnement",
                'demandeAppro'   => $demandeAppro,
                'action_url'     => $this->getUrlDetail($demandeAppro->getId()),
            ],
        ]);
    }

    /** 
     * Méthode pour envoyer une email de modifications pour une DA directe
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param array $tab tableau de données à utiliser dans le corps du mail
     */
    public function envoyerMailModificationDaDirect(DemandeAppro $demandeAppro, array $tab)
    {
        $this->envoyerEmail([
            'to'        => DemandeAppro::MAIL_APPRO,
            'variables' => [
                'tab'            => $tab,
                'statut'         => "modificationDa",
                'subject'        => "{$demandeAppro->getNumeroDemandeAppro()} - Modification demande d'achat",
                'demandeAppro'   => $demandeAppro,
                'action_url'     => $this->getUrlDetail($demandeAppro->getId(), false),
            ],
        ]);
    }

    /** 
     * Méthode pour envoyer une email sur l'observation émis pour une DA avec DIT
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param array $tab tableau de données à utiliser dans le corps du mail
     */
    public function envoyerMailObservationDaAvecDit(DemandeAppro $demandeAppro, array $tab)
    {
        $this->envoyerEmail([
            'to'        => $tab['service'] == 'atelier' ? DemandeAppro::MAIL_APPRO : $demandeAppro->getUser()->getMail(),
            'variables' => [
                'tab'            => $tab,
                'statut'         => "commente",
                'subject'        => "{$demandeAppro->getNumeroDemandeAppro()} - Observation ajoutée par le service " . strtoupper($tab['service']),
                'demandeAppro'   => $demandeAppro,
                'action_url'     => $this->getUrlDetail($demandeAppro->getId()),
            ],
        ]);
    }

    /** 
     * Méthode pour envoyer une email sur l'observation émis pour une DA directe
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param array $tab tableau de données à utiliser dans le corps du mail
     */
    public function envoyerMailObservationDaDirect(DemandeAppro $demandeAppro, array $tab)
    {
        $this->envoyerEmail([
            'to'        => $tab['service'] == 'appro' ? $demandeAppro->getUser()->getMail() : DemandeAppro::MAIL_APPRO,
            'variables' => [
                'tab'            => $tab,
                'statut'         => "commente",
                'subject'        => "{$demandeAppro->getNumeroDemandeAppro()} - Observation ajoutée par le service " . strtoupper($tab['service']),
                'demandeAppro'   => $demandeAppro,
                'action_url'     => $this->getUrlDetail($demandeAppro->getId(), false),
            ],
        ]);
    }

    /** 
     * Méthode pour envoyer une email de validation d'une DA avec DIT à l'Atelier
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param array $resultatExport résultat d'export
     * @param array $tab tableau de données à utiliser dans le corps du mail
     */
    public function envoyerMailValidationDaAvecDitAuxAtelier(DemandeAppro $demandeAppro, array $resultatExport, array $tab)
    {
        $this->envoyerEmail([
            'to'        => $demandeAppro->getUser()->getMail(),
            'variables' => [
                'tab'            => $tab,
                'statut'         => "validationDa",
                'subject'        => "{$demandeAppro->getNumeroDemandeAppro()} - Proposition(s) validée(s) par l'" . strtoupper($tab['service']),
                'demandeAppro'   => $demandeAppro,
                'resultatExport' => $resultatExport,
                'action_url'     => $this->getUrlDetail($demandeAppro->getId()),
            ],
            'attachments' => [
                $resultatExport['filePath'] => $resultatExport['fileName'],
            ],
        ]);
    }

    /** 
     * Méthode pour envoyer une email de validation d'une DA directe au service emetteur
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param array $resultatExport résultat d'export
     * @param array $tab tableau de données à utiliser dans le corps du mail
     */
    public function envoyerMailValidationDaDirectAuxService(DemandeAppro $demandeAppro, array $resultatExport, array $tab)
    {
        $this->envoyerEmail([
            'to'        => $demandeAppro->getUser()->getMail(),
            'variables' => [
                'tab'            => $tab,
                'statut'         => "validationDa",
                'subject'        => "{$demandeAppro->getNumeroDemandeAppro()} - Proposition(s) validée(s) par le service " . strtoupper($tab['service']),
                'demandeAppro'   => $demandeAppro,
                'resultatExport' => $resultatExport,
                'action_url'     => $this->getUrlDetail($demandeAppro->getId(), false),
            ],
            'attachments' => [
                $resultatExport['filePath'] => $resultatExport['fileName'],
            ],
        ]);
    }

    /** 
     * Méthode pour envoyer une email de validation d'une DA avec DIT à l'Appro
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param array $resultatExport résultat d'export
     * @param array $tab tableau de données à utiliser dans le corps du mail
     */
    public function envoyerMailValidationDaAvecDitAuxAppro(DemandeAppro $demandeAppro, array $resultatExport, array $tab)
    {
        $this->envoyerEmail([
            'to'        => DemandeAppro::MAIL_APPRO,
            'variables' => [
                'tab'            => $tab,
                'statut'         => "validationAteDa",
                'subject'        => "{$demandeAppro->getNumeroDemandeAppro()} - Proposition(s) validée(s) par l'" . strtoupper($tab['service']),
                'demandeAppro'   => $demandeAppro,
                'resultatExport' => $resultatExport,
                'action_url'     => $this->getUrlDetail($demandeAppro->getId()),
            ],
        ]);
    }

    /** 
     * Méthode pour envoyer une email de validation d'une DA directe à l'Appro
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param array $resultatExport résultat d'export
     * @param array $tab tableau de données à utiliser dans le corps du mail
     */
    public function envoyerMailValidationDaDirectAuxAppro(DemandeAppro $demandeAppro, array $resultatExport, array $tab)
    {
        $this->envoyerEmail([
            'to'        => DemandeAppro::MAIL_APPRO,
            'variables' => [
                'tab'            => $tab,
                'statut'         => "validationAteDa",
                'subject'        => "{$demandeAppro->getNumeroDemandeAppro()} - Proposition(s) validée(s) par le service " . strtoupper($tab['service']),
                'demandeAppro'   => $demandeAppro,
                'resultatExport' => $resultatExport,
                'action_url'     => $this->getUrlDetail($demandeAppro->getId(), false),
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

        $emailService->sendEmail($content['to'], $content['cc'] ?? [], $this->emailTemplate, $content['variables'] ?? [], $content['attachments'] ?? []);
    }
}
