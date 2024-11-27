<?php

namespace App\Controller\tik;

use App\Controller\Controller;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\tik\TkiCommentaires;
use App\Entity\admin\tik\TkiStatutTicketInformatique;
use App\Entity\admin\utilisateur\User;
use App\Entity\tik\DemandeSupportInformatique;
use App\Entity\tik\TkiPlanning;
use App\Form\admin\tik\TkiCommentairesType;
use App\Form\tik\DetailTikType;
use App\Repository\admin\StatutDemandeRepository;
use App\Repository\tik\TkiPlanningRepository;
use App\Service\EmailService;
use App\Service\fichier\FileUploaderService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DetailTikController extends Controller
{
    /**  
     * @Route("/tik-detail/{id<\d+>}", name="detail_tik")
     */
    public function detail($id, Request $request)
    {
        /** 
         * @var DemandeSupportInformatique $supportInfo l'entité du DemandeSupportInformatique correspondant à l'id $id
         */
        $supportInfo = self::$em->getRepository(DemandeSupportInformatique::class)->find($id);

        /** 
         * @var User $connectedUser l'utilisateur connecté
         */
        $connectedUser = self::$em->getRepository(User::class)->find($this->sessionService->get('user_id'));

        /** 
         * @var User $demandeur l'utilisateur qui a fait la demande de support info
         */
        $demandeur   = $supportInfo->getUserId();

        /** 
         * @var User $validateur l'utilisateur qui a validé ou refusé la demande
         */
        $validateur  = $supportInfo->getValidateur();

        /** 
         * @var User $intervenant l'utilisateur qui a été assigné à la demande
         */
        $intervenant = $supportInfo->getIntervenant();

        /** 
         * @var array $authorizedUsers les utilisateurs autorisés à commenter
         */
        $authorizedUsers = [ $demandeur->getId(), ];
        
        if ($validateur !== null)  { $authorizedUsers[] = $validateur->getId(); }
        if ($intervenant !== null) { $authorizedUsers[] = $intervenant->getId();}

        /** 
         * Vérifie si l'utilisateur connecté peut commenter.
         * 
         * @var bool $canComment Indique si l'utilisateur connecté peut commenter ou non.
         */
        $canComment = in_array($connectedUser->getId(), $authorizedUsers);

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

                /** 
                 * @var array $button tableau associatif contenant "action" => l'action de la requête (refuser, valider, ...); "statut" => code statut (79, 80, ...) de la demande selon l'action 
                 */
                $button = $this->getButton($request);

                switch ($button['action']) {
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
                        $variableEmail = $this->donneeEmail($supportInfo, $connectedUser, $form->get('commentaires')->getData());
                        
                        $this->confirmerEnvoiEmail($this->emailTikRefuse($variableEmail));

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

                        $nomPrenomIntervenant = $dataForm->getIntervenant()->getPersonnels()->getNom().' '.$dataForm->getIntervenant()->getPersonnels()->getPrenoms();

                        // Envoi email validation
                        $variableEmail = $this->donneeEmail($supportInfo, $connectedUser, $nomPrenomIntervenant);
                        
                        $this->confirmerEnvoiEmail($this->emailTikValide($variableEmail));
        
                        break;

                    case 'planifier':
                        $supportInfo
                            ->setIdStatutDemande($button['statut'])    // statut planifié
                        ;

                        $planning = self::$em->getRepository(TkiPlanning::class)->findOneBy(['numeroTicket'=>$dataForm->getNumeroTicket()]);

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
                        $variableEmail = $this->donneeEmail($supportInfo, $connectedUser);
                        
                        $this->confirmerEnvoiEmail($this->emailTikPlanifie($variableEmail));

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

                        $nomPrenomNouveauIntervenant = $dataForm->getIntervenant()->getPersonnels()->getNom().' '.$dataForm->getIntervenant()->getPersonnels()->getPrenoms();

                        // Envoi email de transfert
                        $variableEmail = $this->donneeEmail($supportInfo, $connectedUser, $nomPrenomNouveauIntervenant);
                        
                        $this->confirmerEnvoiEmail($this->emailTikTransfere($variableEmail));
        
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
                        $variableEmail = $this->donneeEmail($supportInfo, $connectedUser, $form->get('commentaires')->getData());

                        $this->confirmerEnvoiEmail($this->emailTikResolu($variableEmail));

                        break;
                }
                
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

                //envoi les donnée dans la base de donnée
                self::$em->persist($commentaire);
                self::$em->flush();

                $variableEmail = $this->donneeEmail($supportInfo, $connectedUser, $commentaire->getCommentaires());

                $this->confirmerEnvoiEmail($this->emailTikCommente($variableEmail, $connectedUser->getMail()));
                
                $this->redirectToRoute("liste_tik_index");
            }

            $statutOuvert  = $supportInfo->getIdStatutDemande()->getId() == 79;
            $isIntervenant = $supportInfo->getIntervenant() !== null && ($supportInfo->getIntervenant()->getId() == $connectedUser->getId());

            self::$twig->display('tik/demandeSupportInformatique/detail.html.twig', [
                'tik'               => $supportInfo,
                'form'              => $form->createView(),
                'formCommentaire'   => $formCommentaire->createView(),
                'canComment'        => $canComment,
                'statutOuvert'      => $statutOuvert,
                'autoriser'         => !empty(array_intersect(["INTERVENANT", "VALIDATEUR"], $connectedUser->getRoleNames())),  // vérfifie si parmi les roles de l'utilisateur on trouve "INTERVENANT" ou "VALIDATEUR"
                'validateur'        => in_array("VALIDATEUR", $connectedUser->getRoleNames()),                                  // vérfifie si parmi les roles de l'utilisateur on trouve "VALIDATEUR"
                'intervenant'       => !$statutOuvert && $isIntervenant,                   // statut différent de ouvert et l'utilisateur connecté est l'intervenant
                'connectedUser'     => $connectedUser,
                'commentaires'      => self::$em->getRepository(TkiCommentaires::class)
                                                ->findBy(
                                                        ['numeroTicket' =>$supportInfo->getNumeroTicket()],
                                                        ['dateCreation' => 'ASC']
                                                    ),
                'historiqueStatut'  => self::$em->getRepository(TkiStatutTicketInformatique::class)
                                                ->findBy(
                                                        ['numeroTicket'=>$supportInfo->getNumeroTicket()],
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
            '80' => 'refuser',      // statut Refusé
            '81' => 'valider',      // statut en cours
            '82' => 'planifier',    // statut planifié
            '83' => 'resoudre',     // statut planifié
            '00' => 'transferer',   
        ];

        /** 
         * @var StatutDemandeRepository $statutDemande repository pour StatutDemande
         */
        $statutDemande = self::$em->getRepository(StatutDemande::class);

        // Trouver la clé correspondante
        foreach ($actions as $code => $action) {
            if ($request->request->has($action)) {
                return [
                    'statut' => $statutDemande->find($code), // l'entité StatutDemande ayant un id=$code
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

    private function donneeEmail(DemandeSupportInformatique $tik, User $userConnecter, $variable = '') : array
    {
        return [
            'id'                 => $tik->getId(),
            'numTik'             => $tik->getNumeroTicket(),
            'emailValidateur'    => $tik->getValidateur() ? $tik->getValidateur()->getMail() : null,
            'emailUserDemandeur' => $tik->getMailDemandeur(),
            'emailIntervenant'   => $tik->getMailIntervenant(),   
            'variable'           => $variable,
            'userConnecter'      => $userConnecter->getPersonnels()->getNom() . ' ' . $userConnecter->getPersonnels()->getPrenoms(),
            'template'           => 'tik/email/emailTik.html.twig',
        ];
    }

    /** 
     * email pour un ticket refusé
     */
    private function emailTikRefuse($tab): array
    {
        return [ 
            'to'        => $tab['emailUserDemandeur'],
            'template'  => $tab['template'],
            'variables' => [
                'statut'      => "refuse",
                'subject'     => "{$tab['numTik']} - Ticket refusé",
                'tab'         => $tab,
                'action_url'  => "http://localhost/Hffintranet/tik-detail/{$tab['id']}"   // TO DO: à changer plus tard
            ]
        ];
    }

    /** 
     * email pour un ticket validé
     */
    private function emailTikValide($tab): array
    {
        return [ 
            'to'        => $tab['emailUserDemandeur'],
            'cc'        => [$tab['emailIntervenant']],
            'template'  => $tab['template'],
            'variables' => [ 
                'statut'      => "valide",
                'subject'     => "{$tab['numTik']} - Ticket validé",
                'tab'         => $tab,
                'action_url'  => "http://localhost/Hffintranet/tik-detail/{$tab['id']}"   // TO DO: à changer plus tard
            ]
        ];
    }

    /** 
     * email pour un ticket commenté
     */
    private function emailTikCommente($tab, $emailUserConnected): array
    {
        $tabEmail = array_filter([$tab['emailValidateur'], $tab['emailUserDemandeur'], $tab['emailIntervenant']]);
        $cc = array_values(array_diff($tabEmail, [$emailUserConnected]));
        return [ 
            'to'        => $cc[0],
            'cc'        => !empty($cc[1]) ? [$cc[1]] : [],
            'template'  => $tab['template'],
            'variables' => [ 
                'statut'      => "comment",
                'subject'     => "{$tab['numTik']} - Commentaire émis",
                'tab'         => $tab,
                'action_url'  => "http://localhost/Hffintranet/tik-detail/{$tab['id']}"   // TO DO: à changer plus tard
            ]
        ];
    }

    /** 
     * email pour un ticket résolu
     */
    private function emailTikResolu($tab): array
    {
        $tabEmail = array_values(array_filter([$tab['emailUserDemandeur'], $tab['emailValidateur']]));
        return [ 
            'to'        => $tabEmail[0],
            'cc'        => !empty($tabEmail[1]) ? [$tabEmail[1]] : [],
            'template'  => $tab['template'],
            'variables' => [
                'statut'      => "resolu",
                'subject'     => "{$tab['numTik']} - Ticket résolu",
                'tab'         => $tab,
                'action_url'  => "http://localhost/Hffintranet/tik-detail/{$tab['id']}"   // TO DO: à changer plus tard
            ]
        ];
    }

    /** 
     * email pour un ticket planifié
     */
    private function emailTikPlanifie($tab): array
    {
        $tabEmail = array_values(array_filter([$tab['emailUserDemandeur'], $tab['emailValidateur']]));
        return [ 
            'to'        => $tabEmail[0],
            'cc'        => !empty($tabEmail[1]) ? [$tabEmail[1]] : [],
            'template'  => $tab['template'],
            'variables' => [
                'statut'      => "planifie",
                'subject'     => "{$tab['numTik']} - Ticket planifié",
                'tab'         => $tab,
                'action_url'  => "http://localhost/Hffintranet/tik-detail/{$tab['id']}"   // TO DO: à changer plus tard
            ]
        ];
    }

    /** 
     * email pour un ticket transferé
     */
    private function emailTikTransfere($tab): array
    {
        $tabEmail = array_values(array_filter([$tab['emailUserDemandeur'], $tab['emailValidateur'], $tab['emailIntervenant']]));
        return [ 
            'to'        => $tabEmail[0],
            'cc'        => array_slice($tabEmail, 1),
            'template'  => $tab['template'],
            'variables' => [ 
                'statut'      => "transfere",
                'subject'     => "{$tab['numTik']} - Ticket transféré",
                'tab'         => $tab,
                'action_url'  => "http://localhost/Hffintranet/tik-detail/{$tab['id']}"   // TO DO: à changer plus tard
            ]
        ];
    }

    /** 
     * fonction pour vérifier l'envoi du mail ou non 
     */
    private function confirmerEnvoiEmail(array $content)
    {
        $email = new EmailService;
        
        $content['cc'] = $content['cc'] ?? [];
        
        if ($email->sendEmail($content['to'], $content['cc'], $content['template'], $content['variables'])) {
            $this->sessionService->set('notification',['type' => 'success', 'message' => 'Une email a été envoyé.']);
        } else {
            $this->sessionService->set('notification',['type' => 'danger', 'message' => "l'email n'a pas été envoyé."]);
        }
    }

    /** 
     * Fonction pour le traitement de fichier
     */
    private function traitementEtEnvoiDeFichier($form, TkiCommentaires $commentaire)
    {
        //TRAITEMENT FICHIER
        $fileNames = [];
        // Récupérez les fichiers uploadés depuis le formulaire
        $files        = $form->get('fileNames')->getData();
        $chemin       = $_SERVER['DOCUMENT_ROOT'] . '/Upload/tik/fichiers';
        $fileUploader = new FileUploaderService($chemin);
        if ($files) {
            foreach ($files as $file) {
                // Définissez le préfixe pour chaque fichier, par exemple "DS_" pour "Demande de Support"
                $prefix   = $commentaire->getNumeroTicket().'_commentaire_';
                $fileName = $fileUploader->upload($file, $prefix);
                // Obtenir la taille du fichier dans l'emplacement final
                $filePath = $chemin . '/' . $fileName;
                $fileSize = round(filesize($filePath) / 1024, 2); // Taille en Ko avec 2 décimales
                if (file_exists($filePath)) {
                    $fileSize = round(filesize($filePath) / 1024, 2);
                } else {
                    $fileSize = 0; // ou autre valeur par défaut ou message d'erreur
                }
                
                $fileNames[] = [
                    'name' => $fileName,
                    'size' => $fileSize
                ];
            }
        }

       // Enregistrez les noms des fichiers dans votre entité
        $commentaire->setFileNames($fileNames);
    }
}
