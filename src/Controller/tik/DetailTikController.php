<?php

namespace App\Controller\tik;

use App\Controller\Controller;
use App\Entity\admin\StatutDemande;
use App\Entity\admin\utilisateur\User;
use App\Entity\tik\DemandeSupportInformatique;
use App\Form\tik\DetailTikType;
use App\Repository\admin\utilisateur\UserRepository;
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
         * @var User $user l'utilisateur connecté
         */
        $user = self::$em->getRepository(User::class)->find($this->sessionService->get('user_id'));
        $statutDemande = self::$em->getRepository(StatutDemande::class);

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
                
                $supportInfo
                    ->setNomIntervenant($dataForm->getIntervenant()->getNomUtilisateur())
                    ->setMailIntervenant($dataForm->getIntervenant()->getMail())
                ;
                
                //envoi les donnée dans la base de donnée
                self::$em->persist($supportInfo);
                self::$em->flush(); 

                $this->sessionService->set('notification',['type' => 'success', 'message' => 'La validation a été enregistrée']);
                $this->redirectToRoute("liste_tik_index");
            }

            self::$twig->display('tik/demandeSupportInformatique/detail.html.twig', [
                'tik'        => $supportInfo,
                'form'       => $form->createView(),
                'autoriser'  => !empty(array_intersect(["INTERVENANT", "VALIDATEUR"], $user->getRoleNames())),  // vérfifie si parmi les roles de l'utilisateur on trouve "INTERVENANT" ou "VALIDATEUR"
                'validateur' => in_array("VALIDATEUR", $user->getRoleNames())                                   // vérfifie si parmi les roles de l'utilisateur on trouve "VALIDATEUR"
            ]);
        }
        
    }
}

