<?php

namespace App\Controller;

class DomsController extends Controller
{
    /**
     * @Route("/first-form", name="first_form")
     */
    public function firstForm(Request $request): Response
    {
        $form = $this->createForm(FirstFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Logique pour traiter les données ici si nécessaire

            // Redirection vers le second formulaire
            return $this->redirectToRoute('second_form');
        }

        return $this->render('form/firstForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/second-form", name="second_form")
     */
    public function secondForm(Request $request): Response
    {
        $form = $this->createForm(SecondFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ici, vous pouvez enregistrer les données dans la base de données si nécessaire

            // Redirection ou affichage de confirmation
            return $this->redirectToRoute('some_success_route');
        }

        return $this->render('form/secondForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}