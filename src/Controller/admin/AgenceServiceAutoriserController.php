<?php

namespace App\Controller\admin;

use App\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\admin\utilisateur\AgenceServiceAutoriser;
use App\Form\admin\utilisateur\AgenceServiceAutoriserType;

class AgenceServiceAutoriserController extends Controller
{
    /**
     * @Route("/admin/autoriser", name="autoriser_index")
     */
    public function index()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $data = $this->getEntityManager()->getRepository(AgenceServiceAutoriser::class)->findBy([], ['id' => 'DESC']);

        return $this->render('admin/AgenceServiceAutoriser/list.html.twig', [
            'data' => $data
        ]);
    }

    /**
     * @Route("/admin/autoriser/new", name="autoriser_new")
     */
    public function new(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $form = $this->getFormFactory()->createBuilder(AgenceServiceAutoriserType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $AgenceServiceAutoriser = $form->getData();
            $this->getEntityManager()->persist($AgenceServiceAutoriser);

            $this->getEntityManager()->flush();
            $this->redirectToRoute("autoriser_index");
        }

        return $this->render('admin/AgenceServiceAutoriser/new.html.twig', [

            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/autoriser/edit/{id}", name="autoriser_update")
     *
     * @return void
     */
    public function edit(Request $request, $id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $user = $this->getEntityManager()->getRepository(AgenceServiceAutoriser::class)->find($id);

        $form = $this->getFormFactory()->createBuilder(AgenceServiceAutoriserType::class, $user)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            $this->getEntityManager()->flush();
            $this->redirectToRoute("autoriser_index");
        }

        return $this->render(
            'admin/AgenceServiceAutoriser/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/autoriser/delete/{id}", name="autoriser_delete")
     *
     * @return void
     */
    public function delete($id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $user = $this->getEntityManager()->getRepository(AgenceServiceAutoriser::class)->find($id);

        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();

        $this->redirectToRoute("autoriser_index");
    }
}
