<?php

namespace App\Api\tik;

use App\Controller\Controller;
use App\Entity\admin\utilisateur\User;
use Symfony\Component\Routing\Annotation\Route;

class ModificationTicketApi extends Controller
{
    /**
     * @Route("/api/modification-ticket-fetch/{numTik}", name="modification_ticket_fetch")
     *
     * @return void
     */
    public function canEdit($numTik)
    {
        $result = [
            'edit'   => false,
            'ouvert' => true,
        ];

        $idUtilisateur  = $this->sessionService->get('user_id');
        $utilisateur    = $idUtilisateur !== '-' ? self::$em->getRepository(User::class)->find($idUtilisateur) : null;

        $allTik = $utilisateur->getSupportInfoUser();

        foreach ($allTik as $tik) {
            // si le numéro du ticket appartient à l'utilisateur connecté
            if ($numTik === $tik->getNumeroTicket()) {
                $result['edit'] = true;
                // et si le statut du ticket est ouvert ou en attente
                $result['ouvert'] = ($tik->getIdStatutDemande()->getId() === 58 || $tik->getIdStatutDemande()->getId() === 65) ? true : false;
                break;
            }
        }

        header("Content-type:application/json");

        echo json_encode($result);
    }
}
