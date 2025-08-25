<?php

namespace App\Controller\tik;

use App\Controller\Controller;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\utilisateur\User;
use App\Entity\tik\DemandeSupportInformatique;
use App\Service\historiqueOperation\HistoriqueOperationTIKService;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\tik\HandleRequestService;

/**
 * @Route("/it")
 */
class ClotureTikController extends Controller
{
    private $historiqueOperation;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationTIKService;
    }

    /**
     * @Route("/tik-cloture/{id}", name="tik_cloture")
     *
     * @return void
     */
    public function cloture($id)
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
        if (!$this->canCloturer($supportInfo)) {
            $this->redirectToRoute('profil_acceuil');
        }

        $handleRequestService = new HandleRequestService($connectedUser, $supportInfo);

        $handleRequestService
            ->setStatut(self::$em->getRepository(StatutDemande::class)->find(64))  // statut cloturé
            ->cloturerTicket()
        ;

        $this->historiqueOperation->sendNotificationCloture('Le ticket ' . $supportInfo->getNumeroTicket() . ' a été clôturé avec succès', $supportInfo->getNumeroTicket(), 'liste_tik_index', true);
    }

    /** 
     * Fonction pour vérifier si l'utilisateur peut cloturer le ticket
     */
    private function canCloturer(DemandeSupportInformatique $ticket): bool
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

        // Si validateur
        if (in_array("VALIDATEUR", $utilisateur->getRoleNames())) {
            if ($ticket->getIdStatutDemande()->getId() !== 59 && $ticket->getIdStatutDemande()->getId() !== 64) { // statut non cloturé et non refusé
                return true;
            }
        } else if ($ticket->getUserId() === $utilisateur->getId() && $ticket->getIdStatutDemande()->getId() === 62) { // si c'est le demandeur et statut résolu
            return true;
        }

        return false;
    }
}
