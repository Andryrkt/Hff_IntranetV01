<?php

namespace App\Controller\Traits;

use App\Entity\admin\utilisateur\User;

trait AutorisationTrait
{
    private function autorisationApp(User $user, int $idApp): bool
    {
        $AppIds = $user->getApplicationsIds();
        return in_array($idApp, $AppIds);
    }

    private function autorisationAcces(User $user, int $idApp)
    {
        if (!$this->autorisationApp($user, $idApp)) {
            $message = "vous n'avez pas l'autorisation ... contacter l'administrateur";

            $this->sessionService->set('notification', ['type' => 'danger', 'message' => $message]);
            $this->redirectToRoute("profil_acceuil");
        }
    }
}
