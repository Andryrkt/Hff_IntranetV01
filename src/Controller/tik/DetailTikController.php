<?php

namespace App\Controller\tik;

use App\Controller\Controller;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\tik\TkiCommentaires;
use App\Entity\admin\tik\TkiStatutTicketInformatique;
use App\Entity\admin\utilisateur\User;
use App\Entity\tik\DemandeSupportInformatique;
use App\Form\admin\tik\TkiCommentairesType;
use App\Form\tik\DetailTikType;
use App\Repository\admin\StatutDemandeRepository;
use App\Service\EmailService;
use DateTime;
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
                            ->setIdStatutDemande($button['statut'])    // statut refusé
                        ;

                        self::$em->persist($commentaires);
                        self::$em->persist($supportInfo);

                        self::$em->flush();

                        $this->historiqueStatut($supportInfo, $button['statut']); // historisation du statut

                        // Envoi email refus
                        $variableEmail = $this->donneeEmail($supportInfo, $form->get('commentaires')->getData(), $connectedUser);
                        
                        $this->confirmerEnvoiEmail($this->emailRefuse($variableEmail));

                        break;

                    case 'valider':
                        $supportInfo
                            ->setNomIntervenant($dataForm->getIntervenant()->getNomUtilisateur())
                            ->setMailIntervenant($dataForm->getIntervenant()->getMail())
                            ->setIdStatutDemande($button['statut'])    // statut en cours
                        ;
                        
                        //envoi les donnée dans la base de donnée
                        self::$em->persist($supportInfo);
                        self::$em->flush(); 

                        $this->historiqueStatut($supportInfo, $button['statut']);

                        $intervenant = $dataForm->getIntervenant()->getPersonnels()->getNom().' '.$dataForm->getIntervenant()->getPersonnels()->getPrenoms();

                        // Envoi email validation
                        $variableEmail = $this->donneeEmail($supportInfo, 'Intervenant affecté: '.$intervenant, $connectedUser);
                        
                        $this->confirmerEnvoiEmail($this->emailValide($variableEmail));
        
                        break;

                    case 'transferer':
                        # code...
                        break;

                    case 'planifier':
                        # code...
                        break;
                        
                    case 'planifier':
                        # code...
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
                //envoi les donnée dans la base de donnée
                self::$em->persist($commentaire);
                self::$em->flush();

                $this->redirectToRoute("liste_tik_index");
            }

            self::$twig->display('tik/demandeSupportInformatique/detail.html.twig', [
                'tik'          => $supportInfo,
                'form'         => $form->createView(),
                'formCommentaire' => $formCommentaire->createView(),
                'autoriser'    => !empty(array_intersect(["INTERVENANT", "VALIDATEUR"], $connectedUser->getRoleNames())),  // vérfifie si parmi les roles de l'utilisateur on trouve "INTERVENANT" ou "VALIDATEUR"
                'validateur'   => in_array("VALIDATEUR", $connectedUser->getRoleNames()),                                  // vérfifie si parmi les roles de l'utilisateur on trouve "VALIDATEUR"
                'intervenant'  => ($supportInfo->getIdStatutDemande()->getId() == 81) && ($supportInfo->getIntervenant()->getId()==$connectedUser->getId()),  // statut en cours et l'utilisateur connecté est l'intervenant
                'connectedUser'=> $connectedUser,
                'commentaires' => self::$em->getRepository(TkiCommentaires::class)
                                           ->findBy(
                                                ['numeroTicket' =>$supportInfo->getNumeroTicket()],
                                                ['dateCreation' => 'ASC']
                                            ),
                'historiqueStatut' => self::$em->getRepository(TkiStatutTicketInformatique::class)
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
                    'statut' => $statutDemande->find($code),
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

    private function donneeEmail(DemandeSupportInformatique $tik, string $observation, User $userConnecter) : array
    {
        return [
            'emailUserDemandeur' => $tik->getMailDemandeur(),
            'emailIntervenant'   => $tik->getMailIntervenant(),
            'template'           => 'tik/email/emailTik.html.twig',
            'numTik'             => $tik->getNumeroTicket(),
            'id'                 => $tik->getId(),
            'observation'        => $observation,
            'validateur'         => $userConnecter->getPersonnels()->getNom() . ' ' . $userConnecter->getPersonnels()->getPrenoms()
        ];
    }

    private function emailRefuse($tab): array
    {
        return [ 
            'to'        => $tab['emailUserDemandeur'],
            'template'  => $tab['template'],
            'variables' => [
                'subject'     => "DEMANDE DE SUPPORT INFORMATIQUE REFUSEE ({$tab['numTik']})",
                'message'     => "La demande de support informatique <b>{$tab['numTik']}</b> a été réfusée par <b>{$tab['validateur']}</b>.",
                'observation' => $tab['observation'],
                'action_url'  => "http://localhost/Hffintranet/tik-detail/{$tab['id']}"   // TO DO: à changer plus tard
            ]
        ];
    }

    private function emailValide($tab): array
    {
        return [ 
            'to'        => $tab['emailUserDemandeur'],
            'cc'        => [$tab['emailIntervenant']],
            'template'  => $tab['template'],
            'variables' => [ 
                'subject'     => "DEMANDE DE SUPPORT INFORMATIQUE VALIDEE ({$tab['numTik']})",
                'message'     => "La demande de support informatique <b>{$tab['numTik']}</b> a été validée par <b>{$tab['validateur']}</b>.",
                'observation' => $tab['observation'],
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
}
