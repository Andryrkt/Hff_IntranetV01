<?php

namespace App\Controller;

use App\Controller\Controller;
use App\Entity\admin\utilisateur\Profil;
use App\Form\ChoixSocieteType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class ChoixSocieteController extends Controller
{
    /**
     * @Route("/choix-societe", name="choix_societe")
     *
     * @return void
     */
    public function index(Request $request)
    {
        $profils = $this->getUser()->getProfils();

        $societes = [];
        /** @var Profil $profil */
        foreach ($profils as $profil) {
            $societe = $profil->getSociete();
            if ($societe && !isset($societes[$societe->getId()])) $societes[$societe->getCodeSociete()] = $societe;
        }

        $form = $this->getFormFactory()->createBuilder(ChoixSocieteType::class, NULL, [
            'societes' => array_values($societes),
            'profils'  => $profils,
        ])->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $userInfo = $this->getSessionService()->get('user_info');
            $userInfo['societe_code'] = $data['societe'];
            $userInfo['profil_id'] = $data['profil'];

            $this->getSessionService()->set('user_info', $userInfo);

            //TODO: Rediriger vers une autre page après le choix selon le societe choisie
            return $this->redirectToRoute('profil_acceuil');
        }

        return $this->render('choix_societe.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
