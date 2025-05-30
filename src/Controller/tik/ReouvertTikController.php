<?php

namespace App\Controller\tik;

use App\Controller\Controller;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\utilisateur\User;
use App\Entity\tik\DemandeSupportInformatique;
use App\Service\historiqueOperation\HistoriqueOperationTIKService;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\tik\HandleRequestService;

class ReouvertTikController extends Controller
{
    private $historiqueOperation;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationTIKService;
    }

    /**
     * @Route("/tik-reouvert/{id}", name="tik_reouvert")
     *
     * @return void
     */
    public function reouvert($id)
    {
        /** 
         * @var User $connectedUser l'utilisateur connecté
         */
        $connectedUser = self::$em->getRepository(User::class)->find($this->sessionService->get('user_id'));

        /** 
         * @var DemandeSupportInformatique $supportInfo entité correspondant à l'id 
         */
        $supportInfo = self::$em->getRepository(DemandeSupportInformatique::class)->find($id);

        // Vérifier si l'utilisateur peut modifier le ticket
        if (!$this->canReouvrir($supportInfo)) {
            $this->redirectToRoute('profil_acceuil');
        }

        $handleRequestService = new HandleRequestService($connectedUser, $supportInfo);

        $handleRequestService
            ->setStatut(self::$em->getRepository(StatutDemande::class)->find(63))  // statut réouvert
            ->reouvrirTicket()
        ;

        $this->sessionService->set('notification', [
            'type'    => 'success',
            'message' => 'Le ticket ' . $supportInfo->getNumeroTicket() . ' a été réouvert avec succès',
        ]);
    }

    /** 
     * Fonction pour vérifier si l'utilisateur peut réouvrir le ticket
     */
    private function canReouvrir(DemandeSupportInformatique $ticket): bool
    {
        $this->verifierSessionUtilisateur();

        $idUtilisateur  = $this->sessionService->get('user_id');

        /** 
         * @var User $utilisateur l'utilisateur connecté
         */
        $utilisateur    = $idUtilisateur !== '-' ? self::$em->getRepository(User::class)->find($idUtilisateur) : null;

        if (is_null($utilisateur)) {
            $this->SessionDestroy();
            $this->redirectToRoute("security_signin");
        }

        // Si c'est le demandeur et statut résolu
        if ($ticket->getUserId() === $utilisateur->getId() && $ticket->getIdStatutDemande()->getId() === 62) {
            return true;
        }

        return false;
    }
}
