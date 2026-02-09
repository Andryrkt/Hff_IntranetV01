<?php

namespace App\Service\da;

use App\Controller\Traits\da\PrixFournisseurTrait;
use App\Controller\Traits\lienGenerique;
use App\Entity\admin\utilisateur\User;
use App\Entity\da\DemandeAppro;
use App\Entity\da\DemandeApproParent;
use App\Service\EmailService;
use App\Traits\PrepareDataDAP;

class EmailDaService
{
    use lienGenerique;
    use PrepareDataDAP;
    use PrixFournisseurTrait;
    private $twig;
    private $mailAppro;
    private $emailTemplate;

    public function __construct($twig)
    {
        $this->twig = $twig;
        $this->mailAppro = $_ENV['MAIL_TO_APPRO'];
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
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL   => 'demande-appro/detail-reappro',
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
            DemandeAppro::TYPE_DA_REAPPRO_MENSUEL   => "de réappro mensuel",
        ];
        return $daLabels[$daTypeId];
    }

    /** 
     * Fonction pour obtenir les variables indispensables du template de mail
     */
    private function getImportantVariables($demandeAppro, User $connectedUser, string $daLabel, string $service): array
    {
        return [
            'demandeAppro' => $demandeAppro,
            'fullNameUser' => $connectedUser->getFullName(),
            'daLabel'      => $daLabel,
            'observation'  => $demandeAppro->getObservation() ?? '-',
            'service'      => strtoupper($service),
            'urlIntranet'  => $this->getUrlIntranet(),
            'urlDetail'    => $demandeAppro instanceof DemandeAppro ? $this->getUrlDetail($demandeAppro->getId(), $demandeAppro->getDaTypeId()) : "",
            'dateYear'     => date('Y'),
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
        $to      = $estAppro ? $demandeAppro->getUser()->getMail() : $this->mailAppro;
        $this->envoyerEmail([
            'to'        => $to,
            'variables' => [
                'templateName'  => "observationDa",
                'header'        => "{$demandeAppro->getNumeroDemandeAppro()} - DEMANDE " . strtoupper($daLabel) . " : <span class=\"commente\"> OBSERVATION AJOUTÉE PAR LE SERVICE " . strtoupper($service) . " </span>",
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
            'to'        => $this->mailAppro,
            'variables' => [
                'templateName'  => "newDa",
                'header'        => "{$demandeAppro->getNumeroDemandeAppro()} - DEMANDE " . strtoupper($daLabel) . " : <span class=\"newDa\"> CRÉATION </span>",
                'subject'       => "{$demandeAppro->getNumeroDemandeAppro()} - Nouvelle demande $daLabel créé",
                'preparedDatas' => $this->prepareDataForMailCreationDa($demandeAppro->getDaTypeId(), $demandeAppro->getDAL()),
            ] + $this->getImportantVariables($demandeAppro, $connectedUser, $daLabel, $service), // opérateur `+` pour ne pas écraser les clés existantes
        ]);
    }

    /** 
     * Méthode pour envoyer une email pour la création d'une DA parent (avec DIT, Direct, Réappro)
     * @param DemandeApproParent $demandeApproParent objet de la demande appro parent
     * @param User $connectedUser l'utilisateur connecté
     */
    public function envoyerMailCreationDaParent(DemandeApproParent $demandeApproParent, User $connectedUser)
    {
        $daLabel = "d'achat";
        $service = $demandeApproParent->getServiceEmetteur()->getLibelleService();
        $this->envoyerEmail([
            'to'        => $this->mailAppro,
            'variables' => [
                'templateName'  => "newDa",
                'header'        => "{$demandeApproParent->getNumeroDemandeAppro()} - DEMANDE " . strtoupper($daLabel) . " : <span class=\"newDa\"> CRÉATION </span>",
                'subject'       => "{$demandeApproParent->getNumeroDemandeAppro()} - Nouvelle demande $daLabel créé",
                'preparedDatas' => $this->prepareDataForMailCreationDa(DemandeAppro::TYPE_DA_DIRECT, $demandeApproParent->getDemandeApproParentLines()),
            ] + $this->getImportantVariables($demandeApproParent, $connectedUser, $daLabel, $service), // opérateur `+` pour ne pas écraser les clés existantes
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
                'templateName'      => "propositionDa",
                'header'            => "{$demandeAppro->getNumeroDemandeAppro()} - DEMANDE " . strtoupper($daLabel) . " : <span class=\"propositionDa\"> PROPOSITION </span>",
                'subject'           => "{$demandeAppro->getNumeroDemandeAppro()} - Proposition créée par l'Appro",
                'serviceDemandeur'  => strtoupper($serviceDemandeur),
                'preparedDatas'     => $this->prepareDataForMailPropositionDa($demandeAppro->getDAL()),
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
            'to'        => $this->mailAppro,
            'variables' => [
                'templateName'  => "modificationDa",
                'header'        => "{$demandeAppro->getNumeroDemandeAppro()} - DEMANDE " . strtoupper($daLabel) . " : <span class=\"modificationDa\"> MODIFICATION </span>",
                'subject'       => "{$demandeAppro->getNumeroDemandeAppro()} - Modification demande $daLabel",
                'preparedDatas' => $this->prepareDataForMailModificationDa($demandeAppro->getDaTypeId(), $demandeAppro->getDAL(), $oldDals),
            ] + $this->getImportantVariables($demandeAppro, $connectedUser, $daLabel, $service),
        ]);
    }

    /** 
     * Méthode pour envoyer une email pour la validation d'une DA (avec DIT, Direct)
     * @param DemandeAppro $demandeAppro objet de la demande appro
     * @param User $connectedUser l'utilisateur connecté
     */
    public function envoyerMailValidationDa(DemandeAppro $demandeAppro, User $connectedUser, array $resultatExport)
    {
        $avecDIT   = $demandeAppro->getDaTypeId() === DemandeAppro::TYPE_DA_AVEC_DIT;
        $daLabel   = $this->getDaLabelForMail($demandeAppro->getDaTypeId());
        $service   = $avecDIT ? 'atelier' : $demandeAppro->getServiceEmetteur()->getLibelleService();
        $constp    = $avecDIT ? 'ZST' : 'ZDI';
        $variables = [
            'templateName'  => "validationDa",
            'header'        => "{$demandeAppro->getNumeroDemandeAppro()} - PROPOSITION(S) <span class=\"validationDa\"> VALIDÉE(S) PAR LE SERVICE " . strtoupper($service) . " </span>",
            'subject'       => "{$demandeAppro->getNumeroDemandeAppro()} - Proposition(s) validée(s) par le service " . strtoupper($service),
            'preparedDatas' => $this->prepareDataForMailValidationDa($demandeAppro->getDaTypeId(), $resultatExport['donnees']),
        ];
        $this->envoyerEmail([
            'to'          => $demandeAppro->getUser()->getMail(),
            'variables'   => [
                "phraseValidation" => "Vous trouverez en pièce jointe le fichier contenant les références $constp.",
            ] + $variables + $this->getImportantVariables($demandeAppro, $connectedUser, $daLabel, $service),
            'attachments' => [
                $resultatExport['filePath'] => $resultatExport['fileName'],
            ],
        ]);
        $this->envoyerEmail([
            'to'        => $this->mailAppro,
            'variables' => $variables + $this->getImportantVariables($demandeAppro, $connectedUser, $daLabel, $service),
        ]);
    }

    /** 
     * Méthode pour envoyer une email pour les validations d'une DA Réappro
     * @param DemandeAppro $demandeAppro  objet de la demande appro
     * @param string       $observation   observation émis
     * @param User         $connectedUser l'utilisateur connecté
     * @param bool         $estValide     si l'utilisateur a validé la demande ou non
     */
    public function envoyerMailValidationReappro(DemandeAppro $demandeAppro, string $observation, User $connectedUser, bool $estValide = true)
    {
        $service    = 'appro';
        $daLabel    = 'de réappro mensuel';
        $class      = $estValide ? 'valide'  : 'refuse';
        $valide     = $estValide ? 'validée' : 'refusée';
        $validation = $estValide ? 'la validation' : 'le refus';
        $this->envoyerEmail([
            'to'        => $demandeAppro->getUser()->getMail(),
            'variables' => [
                'templateName'  => "validationReapproDa",
                'header'        => "{$demandeAppro->getNumeroDemandeAppro()} - DEMANDE DE REAPPRO : <span class=\"$class\"> " . strtoupper($valide) . " </span>",
                'subject'       => "{$demandeAppro->getNumeroDemandeAppro()} - Demande de réappro $valide par le service " . strtoupper($service),
                'valide'        => $valide,
                'validation'    => $validation,
                'preparedDatas' => $this->prepareDataForMailValidationDaReappro(DemandeAppro::TYPE_DA_REAPPRO_MENSUEL, $demandeAppro->getDAL()),
                'observationDa' => $observation,
            ] + $this->getImportantVariables($demandeAppro, $connectedUser, $daLabel, $service), // opérateur `+` pour ne pas écraser les clés existantes
        ]);
    }

    /** 
     * Méthode pour envoyer un email
     */
    public function envoyerEmail(array $content): void
    {
        $emailService = new EmailService($this->twig);

        $emailService->getMailer()->setFrom($_ENV['MAIL_FROM_ADDRESS'], 'noreply.da');

        $emailService->sendEmail($content['to'], $content['cc'] ?? [], $this->emailTemplate, $content['variables'] ?? [], $content['attachments'] ?? []);
    }
}
