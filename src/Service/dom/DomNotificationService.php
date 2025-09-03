<?php

namespace App\Service\dom;

use App\Entity\dom\Dom;
use App\Entity\admin\utilisateur\User;
use App\Service\SessionManagerService;
use Twig\Environment;

/**
 * Service de notification pour les DOM
 */
class DomNotificationService
{
    private SessionManagerService $session;
    private Environment $twig;

    public function __construct(
        SessionManagerService $session,
        Environment $twig
    ) {
        $this->session = $session;
        $this->twig = $twig;
    }

    /**
     * Ajoute une notification d'erreur
     */
    public function addError(string $message, array $context = []): void
    {
        $this->addNotification('danger', $message, $context);
    }

    /**
     * Ajoute une notification de succès
     */
    public function addSuccess(string $message, array $context = []): void
    {
        $this->addNotification('success', $message, $context);
    }

    /**
     * Ajoute une notification d'avertissement
     */
    public function addWarning(string $message, array $context = []): void
    {
        $this->addNotification('warning', $message, $context);
    }

    /**
     * Ajoute une notification d'information
     */
    public function addInfo(string $message, array $context = []): void
    {
        $this->addNotification('info', $message, $context);
    }

    /**
     * Ajoute une notification générique
     */
    private function addNotification(string $type, string $message, array $context = []): void
    {
        $notification = [
            'type' => $type,
            'message' => $this->formatMessage($message, $context),
            'timestamp' => new \DateTime(),
            'context' => $context
        ];

        $notifications = $this->session->get('notifications', []);
        $notifications[] = $notification;
        $this->session->set('notifications', $notifications);
    }

    /**
     * Récupère toutes les notifications
     */
    public function getNotifications(): array
    {
        $notifications = $this->session->get('notifications', []);
        $this->clearNotifications();
        return $notifications;
    }

    /**
     * Efface toutes les notifications
     */
    public function clearNotifications(): void
    {
        $this->session->remove('notifications');
    }

    /**
     * Envoie une notification par email (à implémenter selon votre système d'email)
     */
    public function sendEmailNotification(
        string $to,
        string $subject,
        string $template,
        array $context = []
    ): bool {
        try {
            // TODO: Implémenter l'envoi d'email selon votre configuration
            // Pour l'instant, on log juste l'action
            error_log("Email notification: {$subject} to {$to}");
            return true;
        } catch (\Exception $e) {
            $this->addError('Erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Notifie la création d'un DOM
     */
    public function notifyDomCreation(Dom $dom, User $user): void
    {
        $message = sprintf(
            'DOM %s créé avec succès pour %s %s',
            $dom->getNumeroOrdreMission(),
            $dom->getNom() ?? '',
            $dom->getPrenom() ?? ''
        );

        $this->addSuccess($message, [
            'dom_id' => $dom->getId(),
            'numero' => $dom->getNumeroOrdreMission(),
            'user' => $user->getNomUtilisateur() ?? ''
        ]);
    }

    /**
     * Notifie une erreur de validation
     */
    public function notifyValidationError(array $errors): void
    {
        foreach ($errors as $error) {
            $message = is_array($error) ? $error['message'] : $error;
            $this->addError($message, ['validation' => true]);
        }
    }

    /**
     * Notifie un chevauchement de dates
     */
    public function notifyDateOverlap(string $matricule, string $nom, string $prenom): void
    {
        $message = sprintf(
            '%s %s %s a déjà une mission enregistrée sur ces dates, vérifiez SVP!',
            $matricule,
            $nom,
            $prenom
        );

        $this->addError($message, [
            'type' => 'date_overlap',
            'matricule' => $matricule
        ]);
    }

    /**
     * Notifie une limite de montant dépassée
     */
    public function notifyAmountLimitExceeded(float $montant, string $modePaiement): void
    {
        $message = sprintf(
            'Le montant %s dépasse la limite autorisée pour le mode de paiement %s',
            number_format($montant, 0, ',', '.'),
            $modePaiement
        );

        $this->addError($message, [
            'type' => 'amount_limit',
            'montant' => $montant,
            'mode_paiement' => $modePaiement
        ]);
    }

    /**
     * Notifie la validation d'un DOM
     */
    public function notifyDomValidation(Dom $dom, User $validator): void
    {
        $message = sprintf(
            'DOM %s validé par %s',
            $dom->getNumeroOrdreMission(),
            $validator->getNomUtilisateur() ?? ''
        );

        $this->addSuccess($message, [
            'dom_id' => $dom->getId(),
            'numero' => $dom->getNumeroOrdreMission(),
            'validator' => $validator->getNomUtilisateur() ?? ''
        ]);
    }

    /**
     * Notifie le rejet d'un DOM
     */
    public function notifyDomRejection(Dom $dom, User $rejector, string $reason = ''): void
    {
        $message = sprintf(
            'DOM %s rejeté par %s',
            $dom->getNumeroOrdreMission(),
            $rejector->getNomUtilisateur() ?? ''
        );

        if ($reason) {
            $message .= ' - Raison : ' . $reason;
        }

        $this->addWarning($message, [
            'dom_id' => $dom->getId(),
            'numero' => $dom->getNumeroOrdreMission(),
            'rejector' => $rejector->getNomUtilisateur  () ?? '',
            'reason' => $reason
        ]);
    }

    /**
     * Notifie l'expiration d'un DOM
     */
    public function notifyDomExpiration(Dom $dom): void
    {
        $message = sprintf(
            'DOM %s expiré - Date de fin : %s',
            $dom->getNumeroOrdreMission(),
            $dom->getDateFin()->format('d/m/Y')
        );

        $this->addWarning($message, [
            'dom_id' => $dom->getId(),
            'numero' => $dom->getNumeroOrdreMission(),
            'date_fin' => $dom->getDateFin()
        ]);
    }

    /**
     * Formate un message avec le contexte
     */
    private function formatMessage(string $message, array $context): string
    {
        foreach ($context as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                $message = str_replace('{' . $key . '}', $value, $message);
            }
        }

        return $message;
    }

    /**
     * Récupère les notifications par type
     */
    public function getNotificationsByType(string $type): array
    {
        $allNotifications = $this->session->get('notifications', []);
        return array_filter($allNotifications, function ($notification) use ($type) {
            return $notification['type'] === $type;
        });
    }

    /**
     * Compte les notifications par type
     */
    public function countNotificationsByType(string $type): int
    {
        return count($this->getNotificationsByType($type));
    }

    /**
     * Vérifie s'il y a des erreurs
     */
    public function hasErrors(): bool
    {
        return $this->countNotificationsByType('danger') > 0;
    }

    /**
     * Vérifie s'il y a des avertissements
     */
    public function hasWarnings(): bool
    {
        return $this->countNotificationsByType('warning') > 0;
    }

    /**
     * Récupère le dernier message d'erreur
     */
    public function getLastError(): ?string
    {
        $errors = $this->getNotificationsByType('danger');
        if (empty($errors)) {
            return null;
        }

        $lastError = end($errors);
        return $lastError['message'];
    }

    /**
     * Récupère le dernier message de succès
     */
    public function getLastSuccess(): ?string
    {
        $successes = $this->getNotificationsByType('success');
        if (empty($successes)) {
            return null;
        }

        $lastSuccess = end($successes);
        return $lastSuccess['message'];
    }
}
