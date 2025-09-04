<?php

namespace App\Controller\admin;


use App\Controller\Controller;
use App\Entity\admin\Secteur;
use App\Form\admin\SecteurType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SecteurController extends Controller
{
    /**
     * @Route("/admin/secteur", name="secteur_index")
     *
     * @return void
     */
    public function index()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $data = $this->getEntityManager()->getRepository(Secteur::class)->findBy([], ['id' => 'DESC']);


        return $this->render('admin/secteur/list.html.twig', [

            'data' => $data
        ]);
    }

    /**
     * @Route("/admin/secteur/new", name="secteur_new")
     */
    public function new(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $form = $this->getFormFactory()->createBuilder(SecteurType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $secteur = $form->getData();

            $this->getEntityManager()->persist($secteur);
            $this->getEntityManager()->flush();
            $this->redirectToRoute("secteur_index");
        }

        return $this->render(
            'admin/secteur/new.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/admin/secteur/edit/{id}", name="secteur_update")
     *
     * @return void
     */
    public function edit(Request $request, $id)
    {

        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $secteur = $this->getEntityManager()->getRepository(Secteur::class)->find($id);

        $form = $this->getFormFactory()->createBuilder(SecteurType::class, $secteur)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            $this->getEntityManager()->flush();
            $this->redirectToRoute("secteur_index");
        }

        return $this->render(
            'admin/secteur/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
