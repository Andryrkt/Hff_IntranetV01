<?php

namespace App\Service\da;

use App\Controller\Traits\da\PrixFournisseurTrait;
use App\Controller\Traits\lienGenerique;
use App\Entity\admin\utilisateur\User;
use App\Entity\da\DemandeAppro;
use App\Service\EmailService;
use App\Traits\PrepareData;

class EmailDaService
{
    use lienGenerique;
    use PrepareData;
    use PrixFournisseurTrait;
    private $twig;
    private $emailTemplate;

    public function __construct($twig)
    {
        $this->twig = $twig;
        $this->emailTemplate = "da/email/emailDa.html.twig";
    }

    /** 
     * Fonction pour obtenir l'url de l'INTRANET
     */
    private function getUrlIntranet()
    {
        return $this->urlGenerique($_ENV['BASE_PATH_COURT']);
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
        return $this->urlGenerique("{$_ENV['BASE_PATH_COURT']}/{$template[$daTypeId]}/$id");
    }

    /** 
     * Fonction pour obtenir le label de la DA pour mail
     */
    private function getDaLabelForMail(int $daTypeId): string
    {
        $daLabels = [
            DemandeAppro::TYPE_DA_AVEC_DIT  => "d'approvisionnement",
            DemandeAppro::TYPE_DA_DIRECT    => "d'achat",
            DemandeAppro::TYPE_DA_REAPPRO   => "de réappro mensuel",
        ];
        return $daLabels[$daTypeId];
    }

    /** 
     * Fonction pour obtenir les variables indispensables du template de mail
     */
    private function getImportantVariables(DemandeAppro $demandeAppro, User $connectedUser, string $daLabel, string $service): array
    {
        return [
            'demandeAppro' => $demandeAppro,
            'fullNameUser' => $connectedUser->getFullName(),
            'daLabel'      => $daLabel,
            'observation'  => $demandeAppro->getObservation() ?? '-',
            'service'      => strtoupper($service),
            'urlIntranet'  => $this->getUrlIntranet(),
            'urlDetail'    => $this->getUrlDetail($demandeAppro->getId(), $demandeAppro->getDaTypeId()),
        ];
    }

    /** 
     * Méthode pour envoyer une email pour les observations d'une DA (avec DIT, Direct, Réappro)
     * @param DemandeAppro $demandeAppro  objet de la demande appro
     * @param string       $observation   observation émis
     * @param User         $connectedUser l'utilisateur connecté
     * @param bool         $estAppro      si l'utilisateur est appro ou non
     */
    public function envoyerMailObservationDa(DemandeAppro $demandeAppro, string $observation, User $connectedUser, bool $estAppro)
    {
        $daLabel = $this->getDaLabelForMail($demandeAppro->getDaTypeId());
        $service = $estAppro ? 'appro' : ($demandeAppro->getDaTypeId() === DemandeAppro::TYPE_DA_AVEC_DIT ? 'atelier' : $demandeAppro->getServiceEmetteur()->getLibelleService());
        $to      = $estAppro ? $demandeAppro->getUser()->getMail() : DemandeAppro::MAIL_APPRO;
        $this->envoyerEmail([
            'to'        => $to,
            'variables' => [
                'header'        => "{$demandeAppro->getNumeroDemandeAppro()} - DEMANDE " . strtoupper($daLabel) . " : <span class=\"commente\">OBSERVATION AJOUTÉE PAR LE SERVICE " . strtoupper($service) . "</span>",
                'templateName'  => "observationDa",
                'subject'       => "{$demandeAppro->getNumeroDemandeAppro()} - Observation ajoutée par le service " . strtoupper($service),
                'observationDa' => $observation,
            ] + $this->getImportantVariables($demandeAppro, $connectedUser, $daLabel, $service), // opérateur `+` pour ne pas écraser les clés existantes
        ]);
    }

    /** 
     * Méthode pour envoyer une email pour la création d'une DA (avec DIT, Direct, Réappro)
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param User $connectedUser l'utilisateur connecté
     */
    public function envoyerMailCreationDa(DemandeAppro $demandeAppro, User $connectedUser)
    {
        $daLabel = $this->getDaLabelForMail($demandeAppro->getDaTypeId());
        $service = $demandeAppro->getDaTypeId() === DemandeAppro::TYPE_DA_AVEC_DIT ? 'atelier' : $demandeAppro->getServiceEmetteur()->getLibelleService();
        $this->envoyerEmail([
            'to'        => DemandeAppro::MAIL_APPRO,
            'variables' => [
                'header'        => "{$demandeAppro->getNumeroDemandeAppro()} - DEMANDE " . strtoupper($daLabel) . " : <span class=\"newDa\">CRÉATION</span>",
                'templateName'  => "newDa",
                'subject'       => "{$demandeAppro->getNumeroDemandeAppro()} - Nouvelle demande $daLabel créé",
                'preparedDatas' => $this->prepareDataForMailCreationDa($demandeAppro->getDAL(), $demandeAppro->getDaTypeId()),
            ] + $this->getImportantVariables($demandeAppro, $connectedUser, $daLabel, $service), // opérateur `+` pour ne pas écraser les clés existantes
        ]);
    }

    /** 
     * Méthode pour envoyer une email pour la proposition d'une DA (avec DIT, Direct)
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param User $connectedUser l'utilisateur connecté
     */
    public function envoyerMailPropositionDa(DemandeAppro $demandeAppro, User $connectedUser)
    {
        $daLabel          = $this->getDaLabelForMail($demandeAppro->getDaTypeId());
        $fournisseurs     = $this->gererPrixFournisseurs($demandeAppro->getDAL());
        $service          = "appro";
        $serviceDemandeur = $demandeAppro->getDaTypeId() === DemandeAppro::TYPE_DA_AVEC_DIT ? 'atelier' : $demandeAppro->getServiceEmetteur()->getLibelleService();
        $this->envoyerEmail([
            'to'        => $demandeAppro->getUser()->getMail(),
            'variables' => [
                'header'            => "{$demandeAppro->getNumeroDemandeAppro()} - DEMANDE " . strtoupper($daLabel) . " : <span class=\"propositionDa\">PROPOSITION</span>",
                'templateName'      => "propositionDa",
                'subject'           => "{$demandeAppro->getNumeroDemandeAppro()} - Proposition créée par l'Appro",
                'serviceDemandeur'  => strtoupper($serviceDemandeur),
                'preparedDal'       => $this->prepareDataForMailPropositionDa($demandeAppro->getDAL()),
                'fournisseurs'      => $fournisseurs,
                'listeFournisseurs' => array_keys($fournisseurs),
            ] + $this->getImportantVariables($demandeAppro, $connectedUser, $daLabel, $service),
        ]);
    }

    /** 
     * Méthode pour envoyer une email pour la modification d'une DA (avec DIT, Direct)
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param User $connectedUser l'utilisateur connecté
     */
    public function envoyerMailModificationDa(DemandeAppro $demandeAppro, User $connectedUser, iterable $oldDals)
    {
        $daLabel = $this->getDaLabelForMail($demandeAppro->getDaTypeId());
        $service = $demandeAppro->getDaTypeId() === DemandeAppro::TYPE_DA_AVEC_DIT ? 'atelier' : $demandeAppro->getServiceEmetteur()->getLibelleService();
        $this->envoyerEmail([
            'to'        => DemandeAppro::MAIL_APPRO,
            'variables' => [
                'header'        => "{$demandeAppro->getNumeroDemandeAppro()} - DEMANDE " . strtoupper($daLabel) . " : <span class=\"modificationDa\">MODIFICATION</span>",
                'templateName'  => "modificationDa",
                'subject'       => "{$demandeAppro->getNumeroDemandeAppro()} - Modification demande $daLabel",
                'preparedDatas' => $this->prepareDataForMailModificationDa($demandeAppro->getDaTypeId(), $demandeAppro->getDAL(), $oldDals),
            ] + $this->getImportantVariables($demandeAppro, $connectedUser, $daLabel, $service),
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
        // $content['cc'][] = 'hoby.ralahy@hff.mg';

        $emailService->sendEmail($content['to'], $content['cc'], $this->emailTemplate, $content['variables'] ?? [], $content['attachments'] ?? []);
    }
}
