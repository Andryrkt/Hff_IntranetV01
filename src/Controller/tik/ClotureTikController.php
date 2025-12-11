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
class ClotureTikController extends Controller
{
    private $historiqueOperation;

    public function __construct()
    {
        parent::__construct();
        $this->historiqueOperation = new HistoriqueOperationTIKService($this->getEntityManager());
    }

    /**
     * @Route("/tik-cloture/{id}", name="tik_cloture")
     *
     * @return void
     */
    public function cloture($id)
    {
        $this->verifierSessionUtilisateur();

        $userInfo = $this->session->get('user_info');
        /** 
         * @var User $connectedUser l'utilisateur connecté
         */
        $connectedUser = $this->getEntityManager()->getRepository(User::class)->find($userInfo['id']);

        /** 
         * @var DemandeSupportInformatique $supportInfo entité correspondant à l'id 
         */
        $supportInfo = $this->getEntityManager()->getRepository(DemandeSupportInformatique::class)->find($id);

        // Vérifier si l'utilisateur peut modifier le ticket
        if (!$this->canCloturer($supportInfo, $connectedUser)) {
            $this->redirectToRoute('profil_acceuil');
        }

        $handleRequestService = new HandleRequestService($this->getEntityManager(), $this->getTwig(), $connectedUser, $supportInfo);

        $handleRequestService
            ->setStatut($this->getEntityManager()->getRepository(StatutDemande::class)->find(64))  // statut cloturé
            ->cloturerTicket()
        ;

        $this->historiqueOperation->sendNotificationCloture('Le ticket ' . $supportInfo->getNumeroTicket() . ' a été clôturé avec succès', $supportInfo->getNumeroTicket(), 'liste_tik_index', true);
    }

    /** 
     * Fonction pour vérifier si l'utilisateur peut cloturer le ticket
     */
    private function canCloturer(DemandeSupportInformatique $ticket, User $utilisateur): bool
    {
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
