<?php

namespace App\Controller\tik;

use App\Controller\Controller;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\utilisateur\User;
use App\Service\tik\HandleRequestService;
use App\Entity\tik\DemandeSupportInformatique;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\historiqueOperation\HistoriqueOperationTIKService;

/**
 * @Route("/it")
 */
class ReouvertTikController extends Controller
{
    private $historiqueOperation;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationTIKService($this->getEntityManager());
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
        $connectedUser = $this->getEntityManager()->getRepository(User::class)->find($this->getSessionService()->get('user_id'));

        /** 
         * @var DemandeSupportInformatique $supportInfo entité correspondant à l'id 
         */
        $supportInfo = $this->getEntityManager()->getRepository(DemandeSupportInformatique::class)->find($id);

        // Vérifier si l'utilisateur peut modifier le ticket
        if (!$this->canReouvrir($supportInfo, $connectedUser)) {
            $this->redirectToRoute('profil_acceuil');
        }

        $handleRequestService = new HandleRequestService($this->getEntityManager(), $this->getTwig(), $connectedUser, $supportInfo);

        $handleRequestService
            ->setStatut($this->getEntityManager()->getRepository(StatutDemande::class)->find(63))  // statut réouvert
            ->reouvrirTicket()
        ;

        $this->getSessionService()->set('notification', [
            'type'    => 'success',
            'message' => 'Le ticket ' . $supportInfo->getNumeroTicket() . ' a été réouvert avec succès',
        ]);
    }

    /** 
     * Fonction pour vérifier si l'utilisateur peut réouvrir le ticket
     */
    private function canReouvrir(DemandeSupportInformatique $ticket, User $utilisateur): bool
    {
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
