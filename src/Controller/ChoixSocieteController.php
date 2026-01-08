<?php

namespace App\Controller;

use App\Controller\Controller;
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
        $form = $this->getFormFactory()->createBuilder(ChoixSocieteType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $societe = $data['societe'];

            // Stocker la société choisie dans la session
            $this->getSession()->set('choix_societe', $societe->getId());

            //TODO: Rediriger vers une autre page après le choix selon le societe choisie
            return $this->redirectToRoute('profil_acceuil');
        }

        return $this->render('choix_societe.html.twig', [
            'controller_name' => 'ChoixSocieteController',
            'form' => $form->createView(),
        ]);
    }
}
