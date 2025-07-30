<?php

namespace App\Service\tik;

use App\Controller\Traits\tik\EnvoiFichier;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\tik\TkiCommentaires;
use App\Entity\admin\tik\TkiStatutTicketInformatique;
use App\Entity\admin\utilisateur\User;
use App\Entity\tik\DemandeSupportInformatique;
use App\Entity\tik\TkiPlanning;
use App\Service\SessionManagerService;
use DateTime;

class HandleRequestService
{
    use EnvoiFichier;

    private $emailTikService;

    private $tkiCommentaire;

    private $em;

    private $form;

    private SessionInterface $sessionService;

    private User $connectedUser;

    private DemandeSupportInformatique $supportInfo;

    private StatutDemande $statut;

    /**=====================================================================================
     * CONSTRUCT
     **=====================================================================================*/

    public function __construct(User $connectedUser, DemandeSupportInformatique $supportInfo)
    {
        $this->emailTikService = new EmailTikService();
        $this->tkiCommentaire = new TkiCommentaires();
        $this->em = $em;
        $this->sessionService = $session;
        $this->connectedUser = $em->getRepository(User::class)->find($session->get('user_id'));
    }

    /**=====================================================================================
     * GETTERS and SETTERS
     **=====================================================================================*/

    /**
     * Get the value of statut
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set the value of statut
     *
     * @return  self
     */
    public function setStatut(StatutDemande $statut)
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * Get the value of form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Set the value of form
     *
     * @return  self
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Get the value of tkiCommentaire
     */
    public function getTkiCommentaire()
    {
        return $this->tkiCommentaire;
    }

    /**
     * Set the value of tkiCommentaire
     *
     * @return  self
     */
    public function setTkiCommentaire($tkiCommentaire)
    {
        $this->tkiCommentaire = $tkiCommentaire;

        return $this;
    }

    /**=====================================================================================
     * METHODS
     **=====================================================================================*/

    /** 
     * Méthode pour gérer la requête selon l'action
     */
    public function handleTheRequest(array $button, $form)
    {
        $actions = [
            'refuser' => 'refuserTicket',
            'commenter' => 'commenterTicketEnAttente', // statut en attente
            'valider' => 'validerTicket',
            'planifier' => 'planifierTicket',
            'transferer' => 'transfererTicket',
            'cloturer'   => 'cloturerTicket',
            'resoudre'   => 'resoudreTicket',
        ];

        $action = $button['action'];

        $this->setForm($form);
        $this->setStatut($button['statut']);

        $this->{$actions[$action]}();
    }

    /**
     * Méthode pour gérer un ticket validé
     */
    private function validerTicket()
    {
        $this->supportInfo
            ->setIntervenant($this->form->getData()->getIntervenant())
            ->setValidateur($this->connectedUser)
            ->setNomIntervenant($this->form->getData()->getIntervenant()->getNomUtilisateur())
            ->setMailIntervenant($this->form->getData()->getIntervenant()->getMail())
            ->setIdStatutDemande($this->statut)    // statut en cours
        ;

        if (!is_null($this->form->get('commentaires')->getData())) {
            $this->tkiCommentaire
                ->setNumeroTicket($this->form->getData()->getNumeroTicket())
                ->setNomUtilisateur($this->connectedUser->getNomUtilisateur())
                ->setCommentaires($this->form->get('commentaires')->getData())
                ->setUtilisateur($this->connectedUser)
                ->setDemandeSupportInformatique($this->supportInfo)
            ;

            //envoi les donnée dans la base de donnée
            $this->em->persist($this->tkiCommentaire);
        }

        $this->em->persist($this->supportInfo);
        $this->em->flush();

        $this->historiqueStatut();

        $nomPrenomIntervenant = $this->form->getData()->getIntervenant()->getPersonnels()->getNom() . ' ' . $this->form->getData()->getIntervenant()->getPersonnels()->getPrenoms();

        // Envoi email validation
        $variableEmail = $this->emailTikService->prepareDonneeEmail($this->supportInfo, $this->connectedUser, $nomPrenomIntervenant);

        $this->emailTikService->envoyerEmail($this->emailTikService->prepareEmail('valide', $variableEmail));

        $this->sessionService->set('notification', [
            'type' => 'success',
            'message' => "Le ticket " . $this->form->getData()->getNumeroTicket() . " a été validé.",
        ]);
    }

    /**
     * Méthode pour gérer un ticket refusé
     */
    private function refuserTicket()
    {
        $this->tkiCommentaire
            ->setNumeroTicket($this->form->getData()->getNumeroTicket())
            ->setNomUtilisateur($this->connectedUser->getNomUtilisateur())
            ->setCommentaires($this->form->get('commentaires')->getData())
            ->setUtilisateur($this->connectedUser)
            ->setDemandeSupportInformatique($this->supportInfo)
        ;

        $this->supportInfo
            ->setValidateur($this->connectedUser)
            ->setIdStatutDemande($this->statut)    // statut refusé
        ;

        $this->em->persist($this->tkiCommentaire);
        $this->em->persist($this->supportInfo);

        $this->em->flush();

        $this->historiqueStatut(); // historisation du statut

        // Envoi email refus
        $variableEmail = $this->emailTikService->prepareDonneeEmail($this->supportInfo, $this->connectedUser, $this->form->get('commentaires')->getData());

        $this->emailTikService->envoyerEmail($this->emailTikService->prepareEmail('refuse', $variableEmail));

        $this->sessionService->set('notification', [
            'type' => 'success',
            'message' => "Le ticket " . $this->form->getData()->getNumeroTicket() . " a été refusé.",
        ]);
    }

    /**
     * Méthode pour gérer un ticket commenté (statut en attente)
     */
    private function commenterTicketEnAttente()
    {
        $this->tkiCommentaire
            ->setNumeroTicket($this->form->getData()->getNumeroTicket())
            ->setNomUtilisateur($this->connectedUser->getNomUtilisateur())
            ->setCommentaires($this->form->get('commentaires')->getData())
            ->setUtilisateur($this->connectedUser)
            ->setDemandeSupportInformatique($this->supportInfo)
        ;

        $this->supportInfo
            ->setValidateur($this->connectedUser)
            ->setIdStatutDemande($this->statut)    // statut en attente
        ;

        $this->em->persist($this->tkiCommentaire);
        $this->em->persist($this->supportInfo);

        $this->em->flush();

        $this->historiqueStatut(); // historisation du statut

        // Envoi email mise en attente
        $variableEmail = $this->emailTikService->prepareDonneeEmail($this->supportInfo, $this->connectedUser, $this->form->get('commentaires')->getData());

        $this->emailTikService->envoyerEmail($this->emailTikService->prepareEmail('suspendu', $variableEmail));

        $this->sessionService->set('notification', [
            'type'    => 'success',
            'message' => "Le ticket " . $this->form->getData()->getNumeroTicket() . " a été mise en attente avec succès."
        ]);
    }

    /**
     * Méthode pour gérer un ticket planifié
     */
    private function planifierTicket()
    {
        /** 
         * entité TkiPlanning 
         **/
        $partofDay = $this->form->getData()->getPartOfDay();
        $dateDebut = $this->form->getData()->getDateDebutPlanning();
        $dateFin = $this->form->getData()->getDateFinPlanning();

        $datePlanning = $this->getNumberOfDayPlanning($partofDay, $dateDebut, $dateFin);

        foreach ($datePlanning as $date) {
            $planning = new TkiPlanning;

            $planning
                ->setNumeroTicket($this->form->getData()->getNumeroTicket())
                ->setObjetDemande($this->form->getData()->getObjetDemande())
                ->setDetailDemande($this->form->getData()->getDetailDemande())
                ->setUser($this->connectedUser)
                ->setDemandeSupportInfo($this->form->getData())
                ->setDateDebutPlanning($date['debut'])
                ->setDateFinPlanning($date['fin'])
            ;
            $this->em->persist($planning);
        }

        /** 
         * entité DemandeSupportInformatique 
         **/
        $this->supportInfo
            ->setIdStatutDemande($this->statut)    // statut planifié
        ;
        $this->em->persist($this->supportInfo);

        $this->em->flush();

        $this->historiqueStatut();

        // Envoi email de planification
        $variableEmail = $this->emailTikService->prepareDonneeEmail($this->supportInfo, $this->connectedUser, $dateDebut);

        $this->emailTikService->envoyerEmail($this->emailTikService->prepareEmail('planifie', $variableEmail));
    }

    /** 
     * Méthode pour retourner un tableau de couple de date Début et date Fin, qui tronque la différence entre $dateDebut et $dateFin
     * 
     * @param string $partofDay partie de la journée
     *  - AM => 08:00 - 12:00
     *  - PM => 13:30 - 17:30
     * @param DateTime $dateDebut date de début du planning
     * @param DateTime $dateFin date de fin du planning
     * 
     * @return array
     */
    private function getNumberOfDayPlanning(string $partofDay, DateTime $dateDebut, DateTime $dateFin): array
    {
        $result = [];

        // Définition des plages horaires selon la partie de journée
        $timeRanges = [
            'AM' => ['08:00', '12:00'],
            'PM' => ['13:30', '17:30']
        ];

        [$startHour, $endHour] = $timeRanges[$partofDay];

        // Création des dates de début et fin pour le découpage
        $currentStart = clone $dateDebut;
        $currentEnd = clone $dateDebut;

        // Réinitialisation de l'heure selon la partie de la journée
        $currentStart->setTime((int)explode(':', $startHour)[0], (int)explode(':', $startHour)[1]);  // Ex: Si AM alors $currentStart->setTime(8, 0)
        $currentEnd->setTime((int)explode(':', $endHour)[0], (int)explode(':', $endHour)[1]);  // Ex: Si PM alors $currentEnd->setTime(17, 30)

        $dateDebut = clone $currentStart;

        // Parcours de l'intervalle date par date
        while ($currentStart->format('Y-m-d') <= $dateFin->format('Y-m-d')) {
            // Ignorer les samedis et dimanches
            if ((int)$currentStart->format('N') >= 6) { // 6 = samedi, 7 = dimanche
                // Aller au lundi suivant
                $currentStart->modify('next monday')->setTime((int)explode(':', $startHour)[0], (int)explode(':', $startHour)[1]);
                $currentEnd = clone $currentStart;
                $currentEnd->setTime((int)explode(':', $endHour)[0], (int)explode(':', $endHour)[1]);
                continue;
            }

            // Si la fin actuelle dépasse la date de fin, on ajuste
            if ($currentEnd > $dateFin) {
                $currentEnd = clone $dateFin;
                $currentEnd->setTime((int)explode(':', $endHour)[0], (int)explode(':', $endHour)[1]);
            }

            // Ajout du couple (date de début, date de fin) au résultat
            $result[] = [
                'debut' => clone $currentStart,
                'fin' => clone $currentEnd
            ];

            // Passage au jour suivant
            $currentStart->modify('+1 day')->setTime((int)explode(':', $startHour)[0], (int)explode(':', $startHour)[1]);
            $currentEnd->modify('+1 day')->setTime((int)explode(':', $endHour)[0], (int)explode(':', $endHour)[1]);
        }

        return $result;
    }

    /**
     * Méthode pour gérer un ticket transferé
     */
    private function transfererTicket()
    {
        $this->supportInfo
            ->setIntervenant($this->form->getData()->getIntervenant())                              // nouveau intervenant
            ->setNomIntervenant($this->form->getData()->getIntervenant()->getNomUtilisateur())      // nom d'utilisateur du nouveau intervenant
            ->setMailIntervenant($this->form->getData()->getIntervenant()->getMail())               // mail du nouveau intervenant
            // ->setIdStatutDemande($button['statut'])                                 ******* QUESTION: statut ????
        ;

        //envoi les donnée dans la base de donnée
        $this->em->persist($this->supportInfo);
        $this->em->flush();

        $nomPrenomNouveauIntervenant = $this->form->getData()->getIntervenant()->getPersonnels()->getNom() . ' ' . $this->form->getData()->getIntervenant()->getPersonnels()->getPrenoms();

        // Envoi email de transfert
        $variableEmail = $this->emailTikService->prepareDonneeEmail($this->supportInfo, $this->connectedUser, $nomPrenomNouveauIntervenant);

        $this->emailTikService->envoyerEmail($this->emailTikService->prepareEmail('transfere', $variableEmail));

        $this->sessionService->set('notification', [
            'type' => 'success',
            'message' => "Le ticket " . $this->form->getData()->getNumeroTicket() . " a été transféré.",
        ]);
    }

    /**
     * Méthode pour gérer un ticket résolu
     */
    private function resoudreTicket()
    {
        $this->tkiCommentaire
            ->setNumeroTicket($this->form->getData()->getNumeroTicket())
            ->setNomUtilisateur($this->connectedUser->getNomUtilisateur())
            ->setCommentaires($this->form->get('commentaires')->getData())
            ->setUtilisateur($this->connectedUser)
            ->setDemandeSupportInformatique($this->supportInfo)
        ;

        $this->supportInfo
            ->setIdStatutDemande($this->statut)    // statut resolu
        ;

        $this->em->persist($this->tkiCommentaire);
        $this->em->persist($this->supportInfo);

        $this->em->flush();

        $this->historiqueStatut($this->supportInfo, $this->statut); // historisation du statut

        // Envoi email resolution
        $variableEmail = $this->emailTikService->prepareDonneeEmail($this->supportInfo, $this->connectedUser, $this->form->get('commentaires')->getData());

        $this->emailTikService->envoyerEmail($this->emailTikService->prepareEmail('resolu', $variableEmail));

        $this->sessionService->set('notification', [
            'type' => 'success',
            'message' => "Le ticket " . $this->form->getData()->getNumeroTicket() . " a été résolu.",
        ]);
    }

    /**
     * Fonction pour gérer le commentaire d'un ticket
     */
    public function commenterTicket($form, $commentaire)
    {
        $this->setForm($form);
        $this->setTkiCommentaire($commentaire);
        $this->tkiCommentaire
            ->setUtilisateur($this->connectedUser)
            ->setDemandeSupportInformatique($this->supportInfo)
        ;

        $this->traitementEtEnvoiDeFichier($form, $this->tkiCommentaire);

        $text = str_replace(["\r\n", "\n", "\r"], "<br>", $this->tkiCommentaire->getCommentaires());
        $this->tkiCommentaire->setCommentaires($text);

        $this->em->persist($this->tkiCommentaire);
        $this->em->flush();

        // Envoi email mise en attente
        $variableEmail = $this->emailTikService->prepareDonneeEmail($this->supportInfo, $this->connectedUser, $this->tkiCommentaire->getCommentaires());

        $this->emailTikService->envoyerEmail($this->emailTikService->prepareEmail('commente', $variableEmail, $this->connectedUser->getMail()));
    }

    /** 
     * Fonction pour gérer la cloture d'un ticket
     */
    public function cloturerTicket()
    {
        $this->supportInfo
            ->setIdStatutDemande($this->statut)    // statut cloturé
        ;

        $this->em->persist($this->supportInfo);
        $this->em->flush();

        $this->historiqueStatut(); // historisation du statut

        // Envoi email cloturé
        $variableEmail = $this->emailTikService->prepareDonneeEmail($this->supportInfo, $this->connectedUser);

        $this->emailTikService->envoyerEmail($this->emailTikService->prepareEmail('cloture', $variableEmail, $this->connectedUser->getMail()));
    }

    /** 
     * Fonction pour gérer la réouverture d'un ticket
     */
    public function reouvrirTicket()
    {
        $this->supportInfo
            ->setIdStatutDemande($this->statut)    // statut réouvert
        ;

        $this->em->persist($this->supportInfo);
        $this->em->flush();

        $this->historiqueStatut(); // historisation du statut

        // Envoi email cloturé
        $variableEmail = $this->emailTikService->prepareDonneeEmail($this->supportInfo, $this->connectedUser);

        $this->emailTikService->envoyerEmail($this->emailTikService->prepareEmail('reouvert', $variableEmail, $this->connectedUser->getMail()));
    }

    /**
     * fonction pour historiser le statut du ticket
     */
    private function historiqueStatut()
    {
        $tikStatut = new TkiStatutTicketInformatique();
        $tikStatut
            ->setNumeroTicket($this->supportInfo->getNumeroTicket())
            ->setCodeStatut($this->statut->getCodeStatut())
            ->setIdStatutDemande($this->statut)
        ;
        $this->em->persist($tikStatut);
        $this->em->flush();
    }
}
