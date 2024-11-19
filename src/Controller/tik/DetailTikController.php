<?php

namespace App\Controller\tik;

use App\Controller\Controller;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\tik\TkiCommentaires;
use App\Entity\admin\tik\TkiStatutTicketInformatique;
use App\Entity\admin\utilisateur\User;
use App\Entity\tik\DemandeSupportInformatique;
use App\Form\tik\DetailTikType;
use App\Repository\admin\StatutDemandeRepository;
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
        
                        $this->sessionService->set('notification',['type' => 'success', 'message' => 'La validation a été enregistrée']);
                        break;

                    case 'transferer':
                        # code...
                        break;

                    case 'planifier':
                        # code...
                        break;
                }
                
                
                $this->redirectToRoute("liste_tik_index");
            }

            self::$twig->display('tik/demandeSupportInformatique/detail.html.twig', [
                'tik'        => $supportInfo,
                'form'       => $form->createView(),
                'autoriser'  => !empty(array_intersect(["INTERVENANT", "VALIDATEUR"], $connectedUser->getRoleNames())),  // vérfifie si parmi les roles de l'utilisateur on trouve "INTERVENANT" ou "VALIDATEUR"
                'validateur' => in_array("VALIDATEUR", $connectedUser->getRoleNames())                                   // vérfifie si parmi les roles de l'utilisateur on trouve "VALIDATEUR"
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
}
