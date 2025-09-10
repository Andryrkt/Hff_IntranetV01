<?php

namespace App\Service\tik;

use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\admin\utilisateur\User;
use App\Controller\Traits\lienGenerique;
use App\Entity\tik\DemandeSupportInformatique;

class EmailTikService
{
    use lienGenerique;

    private $em;
    private $twig;
    private $emailService;

    public function __construct(EntityManagerInterface $em, $twig, EmailService $emailService)
    {
        $this->em = $em;
        $this->twig = $twig;
        $this->emailService = $emailService;
    }

    /** 
     * Méthode pour préparer les données pour l'envoi d'email
     */
    public function prepareDonneeEmail(DemandeSupportInformatique $tik, User $userConnecter, $variable = ''): array
    {
        return [
            'id'                 => $tik->getId(),
            'numTik'             => $tik->getNumeroTicket(),
            'emailValidateur'    => $tik->getValidateur() ? $tik->getValidateur()->getMail() : null,
            'emailUserDemandeur' => $tik->getMailDemandeur(),
            'emailIntervenant'   => $tik->getMailIntervenant(),
            'variable'           => $variable,
            'userConnecter'      => $userConnecter->getPersonnels()->getNom() . ' ' . $userConnecter->getPersonnels()->getPrenoms(),
            'template'           => 'tik/email/emailTik.html.twig',
        ];
    }

    /** 
     * Méthode pour préparer l'email selon le statut du ticket
     * 
     * @param string $statut Statut du ticket
     *  - valide : Ticket validé
     *  - refuse : Ticket refusé
     *  - suspendu : Ticket en attente (ou suspendu)
     *  - resolu : Ticket résolu
     *  - planifie : Ticket planifié
     *  - transfere : Ticket transféré
     *  - comment : Ticket commenté
     * @param array $tab tableau des données pour l'email
     * @param string|null $emailUserConnected email de l'utilisateur connecté
     */
    public function prepareEmail(string $statut, array $tab, ?string $emailUserConnected = null): array
    {
        $subject = "{$tab['numTik']} - Ticket {$statut}";
        $actionUrl = $this->urlGenerique($_ENV['BASE_PATH_COURT'] . "/tik-detail/{$tab['id']}");

        $to = $tab['emailUserDemandeur'];
        $cc = [];

        switch ($statut) {
            case 'refuse':
            case 'suspendu':
            case 'resolu':
            case 'planifie':
                $to = $tab['emailUserDemandeur'];
                $cc = !empty($tab['emailValidateur']) ? [$tab['emailValidateur']] : [];
                break;

            case 'valide':
                $to = $tab['emailUserDemandeur'];
                $cc = [$tab['emailIntervenant']];
                break;

            case 'reouvert':
            case 'cloture':
                $tabEmail = array_filter([$tab['emailValidateur'], $tab['emailUserDemandeur'], $tab['emailIntervenant']]);
                $cc = array_values(array_diff($tabEmail, [$emailUserConnected]));
                $to = $cc[0] ?? $tab['emailUserDemandeur'];
                break;

            case 'commente':
                if ($tab['emailValidateur']) {
                    $tabEmail = array_filter([$tab['emailValidateur'], $tab['emailUserDemandeur'], $tab['emailIntervenant']]);
                    $cc = array_values(array_diff($tabEmail, [$emailUserConnected]));
                    $to = $cc[0] ?? $tab['emailUserDemandeur'];
                } else {
                    // Gestion des validateurs
                    $emailValidateurs = array_map(function ($validateur) {
                        return $validateur->getMail();
                    }, $this->em->getRepository(User::class)->findByRole('VALIDATEUR'));
                    $to = $emailValidateurs[0] ?? $tab['emailUserDemandeur'];
                    $cc = array_slice($emailValidateurs, 1);
                }
                break;

            case 'transfere':
                $tabEmail = array_values(array_filter([$tab['emailUserDemandeur'], $tab['emailValidateur'], $tab['emailIntervenant']]));
                $to = $tabEmail[0];
                $cc = array_slice($tabEmail, 1);
                break;
        }

        return [
            'to'        => $to,
            'cc'        => $cc,
            'template'  => $tab['template'],
            'variables' => [
                'statut'      => $statut,
                'subject'     => $subject,
                'tab'         => $tab,
                'action_url'  => $actionUrl
            ],
        ];
    }

    /** 
     * Méthode pour envoyer un email
     */
    public function envoyerEmail(array $content): void
    {
        $this->emailService->getMailer()->setFrom('noreply.email@hff.mg', 'noreply.ticketing');

        $content['cc'] = $content['cc'] ?? [];

        $this->emailService->sendEmail($content['to'], $content['cc'], $content['template'], $content['variables']);
    }
}
