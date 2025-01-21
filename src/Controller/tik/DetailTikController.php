<?php

namespace App\Controller\tik;

use App\Controller\Controller;
use App\Controller\Traits\lienGenerique;
use App\Controller\Traits\tik\EnvoiFichier;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\tik\TkiCommentaires;
use App\Entity\admin\tik\TkiStatutTicketInformatique;
use App\Entity\admin\utilisateur\User;
use App\Entity\tik\DemandeSupportInformatique;
use App\Entity\tik\TkiPlanning;
use App\Form\admin\tik\TkiCommentairesType;
use App\Form\tik\DetailTikType;
use App\Repository\admin\StatutDemandeRepository;
use App\Service\tik\EmailTikService;
use App\Service\tik\HandleRequestService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DetailTikController extends Controller
{
    use lienGenerique;
    use EnvoiFichier;
    private $emailTikService;

    public function __construct()
    {
        parent::__construct();
        $this->emailTikService = new EmailTikService;
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
        $supportInfo = self::$em->getRepository(DemandeSupportInformatique::class)->find($id);

        /** 
         * @var User $connectedUser l'utilisateur connecté
         */
        $connectedUser = self::$em->getRepository(User::class)->find($this->sessionService->get('user_id'));

        $handleRequestService = new HandleRequestService($connectedUser, $supportInfo);

        if (!$supportInfo) {
            self::$twig->display('404.html.twig');
        } else {
            $form = self::$validator->createBuilder(DetailTikType::class, $supportInfo)->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /** 
                 * @var DemandeSupportInformatique $dataForm l'entité du DemandeSupportInformatique envoyé par le formualire de validation
                 */
                $dataForm = $form->getData();
                $numTik   = $dataForm->getNumeroTicket(); // numéro du ticket

                /** 
                 * @var array $button tableau associatif contenant "action" => l'action de la requête (refuser, valider, ...); "statut" => code statut (79, 80, ...) de la demande selon l'action 
                 */
                $button = $this->getButton($request);

                $handleRequestService->handleTheRequest($button, $form);

                if ($button['action'] === 'planifier') {
                    $this->redirectToRoute("tik_calendar_planning");
                }

                /* switch ($button['action']) {
                    case 'refuser':
                        $commentaires = new TkiCommentaires;
                        $commentaires
                            ->setNumeroTicket($dataForm->getNumeroTicket())
                            ->setNomUtilisateur($connectedUser->getNomUtilisateur())
                            ->setCommentaires($form->get('commentaires')->getData())
                            ->setUtilisateur($connectedUser)
                            ->setDemandeSupportInformatique($supportInfo)
                        ;

                        $supportInfo
                            ->setValidateur($connectedUser)
                            ->setIdStatutDemande($button['statut'])    // statut refusé
                        ;

                        self::$em->persist($commentaires);
                        self::$em->persist($supportInfo);

                        self::$em->flush();

                        $this->historiqueStatut($supportInfo, $button['statut']); // historisation du statut

                        // Envoi email refus
                        $variableEmail = $this->emailTikService->prepareDonneeEmail($supportInfo, $connectedUser, $form->get('commentaires')->getData());

                        $this->emailTikService->envoyerEmail($this->emailTikService->prepareEmail('refuse', $variableEmail));

                        $this->sessionService->set('notification', [
                            'type'    => 'success',
                            'message' => "Le ticket $numTik a été refusé."
                        ]);

                        break;

                    case 'commenter':
                        $commentaires = new TkiCommentaires;
                        $commentaires
                            ->setNumeroTicket($dataForm->getNumeroTicket())
                            ->setNomUtilisateur($connectedUser->getNomUtilisateur())
                            ->setCommentaires($form->get('commentaires')->getData())
                            ->setUtilisateur($connectedUser)
                            ->setDemandeSupportInformatique($supportInfo)
                        ;

                        $supportInfo
                            ->setValidateur($connectedUser)
                            ->setIdStatutDemande($button['statut'])    // statut en attente
                        ;

                        self::$em->persist($commentaires);
                        self::$em->persist($supportInfo);

                        self::$em->flush();

                        $this->historiqueStatut($supportInfo, $button['statut']); // historisation du statut

                        // Envoi email mise en attente
                        $variableEmail = $this->emailTikService->prepareDonneeEmail($supportInfo, $connectedUser, $form->get('commentaires')->getData());

                        $this->emailTikService->envoyerEmail($this->emailTikService->prepareEmail('suspendu', $variableEmail));

                        $this->sessionService->set('notification', [
                            'type'    => 'success',
                            'message' => "Le ticket $numTik a été suspendu."
                        ]);

                        break;

                    case 'valider':
                        $supportInfo
                            ->setIntervenant($dataForm->getIntervenant())
                            ->setValidateur($connectedUser)
                            ->setNomIntervenant($dataForm->getIntervenant()->getNomUtilisateur())
                            ->setMailIntervenant($dataForm->getIntervenant()->getMail())
                            ->setIdStatutDemande($button['statut'])    // statut en cours
                        ;

                        //envoi les donnée dans la base de donnée
                        self::$em->persist($supportInfo);
                        self::$em->flush();

                        $this->historiqueStatut($supportInfo, $button['statut']);

                        $nomPrenomIntervenant = $dataForm->getIntervenant()->getPersonnels()->getNom() . ' ' . $dataForm->getIntervenant()->getPersonnels()->getPrenoms();

                        // Envoi email validation
                        $variableEmail = $this->emailTikService->prepareDonneeEmail($supportInfo, $connectedUser, $nomPrenomIntervenant);

                        $this->emailTikService->envoyerEmail($this->emailTikService->prepareEmail('valide', $variableEmail));

                        $this->sessionService->set('notification', [
                            'type'    => 'success',
                            'message' => "Le ticket $numTik a été validé."
                        ]);

                        break;

                    case 'planifier':
                        $supportInfo
                            ->setIdStatutDemande($button['statut'])    // statut planifié
                        ;

                        $planning = self::$em->getRepository(TkiPlanning::class)->findOneBy(['numeroTicket' => $dataForm->getNumeroTicket()]);

                        $planning = $planning ?? new TkiPlanning;

                        $planning
                            ->setNumeroTicket($dataForm->getNumeroTicket())
                            ->setDateDebutPlanning($dataForm->getDateDebutPlanning())
                            ->setDateFinPlanning($dataForm->getDateFinPlanning())
                            ->setObjetDemande($dataForm->getObjetDemande())
                            ->setDetailDemande($dataForm->getDetailDemande())
                            ->setUserId($connectedUser)
                            ->setDemandeId($dataForm)
                        ;

                        //envoi les donnée dans la base de donnée
                        self::$em->persist($supportInfo);
                        self::$em->persist($planning);

                        self::$em->flush();

                        $this->historiqueStatut($supportInfo, $button['statut']);

                        // Envoi email de planification
                        $variableEmail = $this->emailTikService->prepareDonneeEmail($supportInfo, $connectedUser, $dataForm->getDateDebutPlanning());

                        $this->emailTikService->envoyerEmail($this->emailTikService->prepareEmail('planifie', $variableEmail));

                        $this->redirectToRoute("tik_calendar_planning");

                        break;

                    case 'transferer':
                        $supportInfo
                            ->setIntervenant($dataForm->getIntervenant())                              // nouveau intervenant
                            ->setNomIntervenant($dataForm->getIntervenant()->getNomUtilisateur())      // nom d'utilisateur du nouveau intervenant
                            ->setMailIntervenant($dataForm->getIntervenant()->getMail())               // mail du nouveau intervenant
                            // ->setIdStatutDemande($button['statut'])                                 ******* QUESTION: statut ????
                        ;

                        //envoi les donnée dans la base de donnée
                        self::$em->persist($supportInfo);
                        self::$em->flush();

                        // $this->historiqueStatut($supportInfo, $button['statut']);                   ******* QUESTION: historisation ????      

                        $nomPrenomNouveauIntervenant = $dataForm->getIntervenant()->getPersonnels()->getNom() . ' ' . $dataForm->getIntervenant()->getPersonnels()->getPrenoms();

                        // Envoi email de transfert
                        $variableEmail = $this->emailTikService->prepareDonneeEmail($supportInfo, $connectedUser, $nomPrenomNouveauIntervenant);

                        $this->emailTikService->envoyerEmail($this->emailTikService->prepareEmail('transfere', $variableEmail));

                        $this->sessionService->set('notification', [
                            'type'    => 'success',
                            'message' => "Le ticket $numTik a été transféré."
                        ]);

                        break;


                    case 'resoudre':
                        $commentaires = new TkiCommentaires;
                        $commentaires
                            ->setNumeroTicket($dataForm->getNumeroTicket())
                            ->setNomUtilisateur($connectedUser->getNomUtilisateur())
                            ->setCommentaires($form->get('commentaires')->getData())
                            ->setUtilisateur($connectedUser)
                            ->setDemandeSupportInformatique($supportInfo)
                        ;

                        $supportInfo
                            ->setIdStatutDemande($button['statut'])    // statut resolu
                        ;

                        self::$em->persist($commentaires);
                        self::$em->persist($supportInfo);

                        self::$em->flush();

                        $this->historiqueStatut($supportInfo, $button['statut']); // historisation du statut

                        // Envoi email resolution
                        $variableEmail = $this->emailTikService->prepareDonneeEmail($supportInfo, $connectedUser, $form->get('commentaires')->getData());

                        $this->emailTikService->envoyerEmail($this->emailTikService->prepareEmail('resolu', $variableEmail));

                        $this->sessionService->set('notification', [
                            'type'    => 'success',
                            'message' => "Le ticket $numTik a été résolu."
                        ]);

                        break;
                } */

                $this->redirectToRoute("liste_tik_index");
            }

            $commentaire = new TkiCommentaires($supportInfo->getNumeroTicket(), $connectedUser->getNomUtilisateur());

            $formCommentaire = self::$validator->createBuilder(TkiCommentairesType::class, $commentaire)->getForm();

            $formCommentaire->handleRequest($request);

            if ($request->request->has('commenter') && $formCommentaire->isSubmitted() && $formCommentaire->isValid()) {
                $commentaire
                    ->setUtilisateur($connectedUser)
                    ->setDemandeSupportInformatique($supportInfo)
                ;
                $this->traitementEtEnvoiDeFichier($formCommentaire, $commentaire);

                $text = str_replace(["\r\n", "\n", "\r"], "<br>", $commentaire->getCommentaires());
                $commentaire->setCommentaires($text);

                //envoi les donnée dans la base de donnée
                self::$em->persist($commentaire);
                self::$em->flush();

                $variableEmail = $this->emailTikService->prepareDonneeEmail($supportInfo, $connectedUser, $commentaire->getCommentaires());

                $this->emailTikService->envoyerEmail($this->emailTikService->prepareEmail('comment', $variableEmail, $connectedUser->getMail()));
            }

            $statutOuvert  = $supportInfo->getIdStatutDemande()->getId() == 58;
            $isIntervenant = $supportInfo->getIntervenant() !== null && ($supportInfo->getIntervenant()->getId() == $connectedUser->getId());

            $this->logUserVisit('detail_tik', [
                'id' => $id
            ]); // historisation du page visité par l'utilisateur 

            self::$twig->display('tik/demandeSupportInformatique/detail.html.twig', [
                'tik'               => $supportInfo,
                'form'              => $form->createView(),
                'formCommentaire'   => $formCommentaire->createView(),
                'canComment'        => $this->canComment($connectedUser, $supportInfo),
                'statutOuvert'      => $statutOuvert,
                'autoriser'         => !empty(array_intersect(["INTERVENANT", "VALIDATEUR"], $connectedUser->getRoleNames())),  // vérfifie si parmi les roles de l'utilisateur on trouve "INTERVENANT" ou "VALIDATEUR"
                'validateur'        => in_array("VALIDATEUR", $connectedUser->getRoleNames()),                                  // vérfifie si parmi les roles de l'utilisateur on trouve "VALIDATEUR"
                'intervenant'       => !$statutOuvert && $isIntervenant,                   // statut différent de ouvert et l'utilisateur connecté est l'intervenant
                'connectedUser'     => $connectedUser,
                'commentaires'      => self::$em->getRepository(TkiCommentaires::class)
                    ->findBy(
                        ['numeroTicket' => $supportInfo->getNumeroTicket()],
                        ['dateCreation' => 'ASC']
                    ),
                'historiqueStatut'  => self::$em->getRepository(TkiStatutTicketInformatique::class)
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
            '00'  => 'transferer',
        ];

        /** 
         * @var StatutDemandeRepository $statutDemande repository pour StatutDemande
         */
        $statutDemande = self::$em->getRepository(StatutDemande::class);

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
     * fonction pour historiser le statut du ticket
     */
    private function historiqueStatut($supportInfo, $statut)
    {
        $tikStatut = new TkiStatutTicketInformatique();
        $tikStatut
            ->setNumeroTicket($supportInfo->getNumeroTicket())
            ->setCodeStatut($statut->getCodeStatut())
            ->setIdStatutDemande($statut)
        ;
        self::$em->persist($tikStatut);
        self::$em->flush();
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
}
