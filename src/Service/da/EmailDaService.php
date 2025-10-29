<?php

namespace App\Service\da;

use App\Controller\Traits\da\DaTrait;
use App\Controller\Traits\lienGenerique;
use App\Entity\admin\utilisateur\User;
use App\Entity\da\DemandeAppro;
use App\Service\EmailService;
use App\Traits\PrepareData;

class EmailDaService
{
    use DaTrait;
    use lienGenerique;
    use PrepareData;
    private $twig;
    private $emailTemplate;

    public function __construct($twig)
    {
        $this->twig = $twig;
        $this->emailTemplate = "da/email/emailDa.html.twig";

        $this->initDaTrait();
    }

    /** 
     * Fonction pour obtenir l'url du détail de la DA
     * @param string $id       id de la DA
     * @param int    $daTypeId le type de la DA
     */
    private function getUrlDetail(string $id, int $daTypeId)
    {
        $template = [
            DemandeAppro::TYPE_DA_AVEC_DIT  => 'demande-appro/detail-avec-dit',
            DemandeAppro::TYPE_DA_DIRECT    => 'demande-appro/detail-direct',
            DemandeAppro::TYPE_DA_REAPPRO   => 'demande-appro/detail-reappro',
        ];
        return $this->urlGenerique(str_replace('/', '', $_ENV['BASE_PATH_COURT']) . "/{$template[$daTypeId]}/$id");
    }

    private function getDaLabelForMail(int $daTypeId): string
    {
        $daLabels = [
            DemandeAppro::TYPE_DA_AVEC_DIT  => 'd’approvisionnement',
            DemandeAppro::TYPE_DA_DIRECT    => 'd’achat',
            DemandeAppro::TYPE_DA_REAPPRO   => 'de réappro mensuel',
        ];
        return $daLabels[$daTypeId];
    }

    /** 
     * Méthode pour envoyer une email pour la création d'une DA (avec DIT, Direct, Réappro)
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param User $connectedUser l'utilisateur connecté
     */
    public function envoyerMailcreationDa(DemandeAppro $demandeAppro, User $connectedUser)
    {
        $daLabel = $this->getDaLabelForMail($demandeAppro->getDaTypeId());
        $service = $demandeAppro->getDaTypeId() === DemandeAppro::TYPE_DA_AVEC_DIT ? 'atelier' : $demandeAppro->getServiceEmetteur()->getLibelleService();
        $this->envoyerEmail([
            'to'        => DemandeAppro::MAIL_APPRO,
            'variables' => [
                'header'         => "{$demandeAppro->getNumeroDemandeAppro()} - DEMANDE " . strtoupper($daLabel) . " : <span class=\"newDa\">CRÉATION</span>",
                'templateName'   => "newDa",
                'daLabel'        => $daLabel,
                'fullNameUser'   => $connectedUser->getFullName(),
                'subject'        => "{$demandeAppro->getNumeroDemandeAppro()} - Nouvelle demande $daLabel créé",
                'demandeAppro'   => $demandeAppro,
                'observation'    => $demandeAppro->getObservation() ?? '-',
                'preparedDatas'  => $this->prepareDataForMailCreationDa($demandeAppro->getDAL(), $demandeAppro->getDaTypeId()),
                'service'        => strtoupper($service),
                'action_url'     => $this->getUrlDetail($demandeAppro->getId(), $demandeAppro->getDaTypeId()),
            ],
        ]);
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
     * Méthode pour envoyer une email pour la création d'une DA directe
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param array $tab tableau de données à utiliser dans le corps du mail
     */
    public function envoyerMailcreationDaDirect(DemandeAppro $demandeAppro, array $tab)
    {
        $this->envoyerEmail([
            'to'        => DemandeAppro::MAIL_APPRO,
            'variables' => [
                'tab'            => $tab,
                'statut'         => "newDa",
                'subject'        => "{$demandeAppro->getNumeroDemandeAppro()} - Nouvelle demande d'achat créé",
                'demandeAppro'   => $demandeAppro,
                'action_url'     => $this->getUrlDetail($demandeAppro->getId(), false),
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
        $fournisseurs = $this->gererPrixFournisseurs($demandeAppro->getDAL());
        $this->envoyerEmail([
            'to'        => $demandeAppro->getUser()->getMail(),
            'variables' => [
                'tab'               => $tab,
                'statut'            => "propositionDa",
                'subject'           => "{$demandeAppro->getNumeroDemandeAppro()} - Proposition créée par l'Appro",
                'demandeAppro'      => $demandeAppro,
                'preparedDal'       => $this->prepareDataForMailPropositionDa($demandeAppro->getDAL()),
                'fournisseurs'      => $fournisseurs,
                'listeFournisseurs' => array_keys($fournisseurs),
                'action_url'        => $this->getUrlDetail($demandeAppro->getId()),
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
        $fournisseurs = $this->gererPrixFournisseurs($demandeAppro->getDAL());
        $this->envoyerEmail([
            'to'        => $demandeAppro->getUser()->getMail(),
            'variables' => [
                'tab'               => $tab,
                'statut'            => "propositionDa",
                'subject'           => "{$demandeAppro->getNumeroDemandeAppro()} - Proposition créée par l'Appro",
                'demandeAppro'      => $demandeAppro,
                'preparedDal'       => $this->prepareDataForMailPropositionDa($demandeAppro->getDAL()),
                'fournisseurs'      => $fournisseurs,
                'listeFournisseurs' => array_keys($fournisseurs),
                'action_url'        => $this->getUrlDetail($demandeAppro->getId(), false),
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
     * Méthode pour envoyer une email de validation à l'Atelier et l'Appro
     * 
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param array $resultatExport résultat d'export
     * @param array $tab tableau de données à utiliser dans le corps du mail
     */
    public function envoyerMailValidationDaAvecDit(DemandeAppro $demandeAppro, array $resultatExport, array $tab): void
    {
        $this->envoyerMailValidationDaAvecDitAuxAtelier($demandeAppro, $resultatExport, $tab); // envoi de mail à l'atelier
        $this->envoyerMailValidationDaAvecDitAuxAppro($demandeAppro, $resultatExport, $tab); // envoi de mail à l'appro
    }

    /** 
     * Méthode pour envoyer une email de validation au service demandeur et l'Appro
     * 
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param array $resultatExport résultat d'export
     * @param array $tab tableau de données à utiliser dans le corps du mail
     */
    public function envoyerMailValidationDaDirect(DemandeAppro $demandeAppro, array $resultatExport, array $tab): void
    {
        $this->envoyerMailValidationDaDirectAuxService($demandeAppro, $resultatExport, $tab); // envoi de mail à l'atelier
        $this->envoyerMailValidationDaDirectAuxAppro($demandeAppro, $resultatExport, $tab); // envoi de mail à l'appro
    }

    /** 
     * Méthode pour envoyer un email
     */
    public function envoyerEmail(array $content): void
    {
        $emailService = new EmailService($this->twig);

        $emailService->getMailer()->setFrom('noreply.email@hff.mg', 'noreply.da');

        $content['cc'] = $content['cc'] ?? [];
        $content['cc'][] = 'hoby.ralahy@hff.mg';

        $emailService->sendEmail($content['to'], $content['cc'] ?? [], $this->emailTemplate, $content['variables'] ?? [], $content['attachments'] ?? []);
    }
}
