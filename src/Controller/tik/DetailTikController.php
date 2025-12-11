<?php

use App\Service\EmailService;

namespace App\Controller\tik;

use App\Controller\Controller;
use App\Entity\tik\TkiPlanning;
use App\Form\tik\DetailTikType;
use App\Entity\admin\StatutDemande;
use App\Service\tik\EmailTikService;
use App\Entity\admin\utilisateur\User;
use App\Controller\Traits\lienGenerique;
use App\Entity\admin\tik\TkiCommentaires;
use App\Service\tik\HandleRequestService;
use App\Controller\Traits\tik\EnvoiFichier;
use App\Form\admin\tik\TkiCommentairesType;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\tik\DemandeSupportInformatique;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\admin\StatutDemandeRepository;
use App\Entity\admin\tik\TkiStatutTicketInformatique;

/**
 * @Route("/it")
 */
class DetailTikController extends Controller
{
    use lienGenerique;
    use EnvoiFichier;
    private $emailTikService;

    public function __construct()
    {
        parent::__construct();
        $this->emailTikService = new EmailTikService($this->getEntityManager(), $this->getTwig());
    }

    /**  
     * @Route("/tik-detail/{id<\d+>}", name="detail_tik")
     */
    public function detail($id, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** 
         * @var DemandeSupportInformatique $supportInfo l'entité du DemandeSupportInformatique correspondant à l'id $id
         */
        $supportInfo = $this->getEntityManager()->getRepository(DemandeSupportInformatique::class)->find($id);

        $userInfo = $this->session->get('user_info');
        /** 
         * @var User $connectedUser l'utilisateur connecté
         */
        $connectedUser = $this->getEntityManager()->getRepository(User::class)->find($userInfo['id']);

        $handleRequestService = new HandleRequestService($this->getEntityManager(), $this->getTwig(), $connectedUser, $supportInfo);

        if (!$supportInfo) {
            return $this->render('404.html.twig');
        } else {
            $formDetail = $this->getFormFactory()->createBuilder(DetailTikType::class, $supportInfo)->getForm();

            $formDetail->handleRequest($request);

            if ($formDetail->isSubmitted() && $formDetail->isValid()) {
                /** 
                 * @var array $button tableau associatif contenant "action" => l'action de la requête (refuser, valider, ...); "statut" => code statut (79, 80, ...) de la demande selon l'action 
                 */
                $button = $this->getButton($request);

                $handleRequestService->handleTheRequest($button, $formDetail);

                if ($button['action'] === 'planifier') {
                    $this->redirectToRoute("tik_calendar_planning");
                }

                $this->redirectToRoute("liste_tik_index");
            }

            $commentaire = new TkiCommentaires($supportInfo->getNumeroTicket(), $connectedUser->getNomUtilisateur());

            $formCommentaire = $this->getFormFactory()->createBuilder(TkiCommentairesType::class, $commentaire)->getForm();

            $formCommentaire->handleRequest($request);

            if ($formCommentaire->isSubmitted() && $formCommentaire->isValid()) {
                $handleRequestService->commenterTicket($formCommentaire, $commentaire);
            }

            $statutOuvert  = $supportInfo->getIdStatutDemande()->getId() == 58;
            $isIntervenant = $supportInfo->getIntervenant() !== null && ($supportInfo->getIntervenant()->getId() == $connectedUser->getId());

            $this->logUserVisit('detail_tik', [
                'id' => $id
            ]); // historisation du page visité par l'utilisateur 

            $template = $this->determineTemplate($connectedUser, $supportInfo);

            return $this->render("tik/demandeSupportInformatique/$template.html.twig", [
                'tik'               => $supportInfo,
                'form'              => $formDetail->createView(),
                'formCommentaire'   => $formCommentaire->createView(),
                'canComment'        => $this->canComment($connectedUser, $supportInfo),
                'statutOuvert'      => $statutOuvert,
                'autoriser'         => !empty(array_intersect(["INTERVENANT", "VALIDATEUR"], $connectedUser->getRoleNames())),  // vérifie si parmi les roles de l'utilisateur on trouve "INTERVENANT" ou "VALIDATEUR"
                'validateur'        => in_array("VALIDATEUR", $connectedUser->getRoleNames()),                                  // vérifie si parmi les roles de l'utilisateur on trouve "VALIDATEUR"
                'intervenant'       => !$statutOuvert && $isIntervenant,                   // statut différent de ouvert et l'utilisateur connecté est l'intervenant
                'connectedUser'     => $connectedUser,
                'commentaires'      => $this->getEntityManager()->getRepository(TkiCommentaires::class)
                    ->findBy(
                        ['numeroTicket' => $supportInfo->getNumeroTicket()],
                        ['dateCreation' => 'ASC']
                    ),
                'historiqueStatut'  => $this->getEntityManager()->getRepository(TkiStatutTicketInformatique::class)
                    ->findBy(
                        ['numeroTicket' => $supportInfo->getNumeroTicket()],
                        ['dateStatut'  => 'DESC']
                    ),
            ]);
        }
    }

    /** 
     * fonction qui retourne l'action du bouton cliqué dans le formulaire
     */
    private function getButton(Request $request)
    {
        $actions = [
            'REF' => 'refuser',      // statut refusé
            'ENC' => 'valider',      // statut en cours
            'PLA' => 'planifier',    // statut planifié
            'RES' => 'resoudre',     // statut résolu
            'ENA' => 'commenter',    // statut en attente
            'CLO' => 'cloturer',     // statut cloturé
            '00'  => 'transferer',
        ];

        /** 
         * @var StatutDemandeRepository $statutDemande repository pour StatutDemande
         */
        $statutDemande = $this->getEntityManager()->getRepository(StatutDemande::class);

        // Trouver la clé correspondante
        foreach ($actions as $code => $action) {
            if ($request->request->has($action)) {
                return [
                    'statut' => $statutDemande->findByCodeStatut($code), // l'entité StatutDemande ayant un id=$code
                    'action' => $action
                ];
            }
        }
    }

    /** 
     * Vérifie si l'utilisateur connecté peut commenter sur le ticket
     * 
     * @param User $connectedUser l'utilisateur connecté
     * @param DemandeSupportInformatique $tik le ticket en question
     * 
     * @return bool
     */
    private function canComment(User $connectedUser, DemandeSupportInformatique $tik): bool
    {
        /** 
         * @var User $demandeur l'utilisateur qui a fait la demande de support info
         */
        $demandeur   = $tik->getUserId();

        /** 
         * @var User $validateur l'utilisateur qui a validé ou refusé la demande
         */
        $validateur  = $tik->getValidateur();

        /** 
         * @var User $intervenant l'utilisateur qui a été assigné à la demande
         */
        $intervenant = $tik->getIntervenant();

        /** 
         * @var array $authorizedUsers les utilisateurs autorisés à commenter
         */
        $authorizedUsers = [$demandeur->getId(),];

        if ($validateur !== null) {
            $authorizedUsers[] = $validateur->getId();
        }
        if ($intervenant !== null) {
            $authorizedUsers[] = $intervenant->getId();
        }

        return in_array($connectedUser->getId(), $authorizedUsers);
    }

    /** 
     * Méthode dédiée pour la logique de sélection du template
     * 
     * @return string
     */
    private function determineTemplate($connectedUser, $supportInfo): string
    {
        if (in_array($supportInfo->getIdStatutDemande()->getId(), [59, 62, 64, 65])) { // statut refusé, résolu, clôturé, mise en attente
            return "detail-2";
        } else if ($supportInfo->getIdStatutDemande()->getId() === 58) { // statut ouvert
            if (in_array("VALIDATEUR", $connectedUser->getRoleNames())) {  // profil = VALIDATEUR
                return "detail-1";
            }
            return "detail-2";
        }

        if (in_array("INTERVENANT", $connectedUser->getRoleNames())) { // profil = INTERVENANT
            return "detail-1";
        }

        return "detail-2";
    }
}
