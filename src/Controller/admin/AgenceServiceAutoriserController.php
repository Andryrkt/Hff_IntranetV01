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

        $data = self::$em->getRepository(AgenceServiceAutoriser::class)->findBy([], ['id' => 'DESC']);

        self::$twig->display('admin/AgenceServiceAutoriser/list.html.twig', [
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

        $form = self::$validator->createBuilder(AgenceServiceAutoriserType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $AgenceServiceAutoriser = $form->getData();
            self::$em->persist($AgenceServiceAutoriser);

            self::$em->flush();
            $this->redirectToRoute("autoriser_index");
        }

        self::$twig->display('admin/AgenceServiceAutoriser/new.html.twig', [

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

        $user = self::$em->getRepository(AgenceServiceAutoriser::class)->find($id);

        $form = self::$validator->createBuilder(AgenceServiceAutoriserType::class, $user)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            self::$em->flush();
            $this->redirectToRoute("autoriser_index");
        }

        self::$twig->display(
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

        $user = self::$em->getRepository(AgenceServiceAutoriser::class)->find($id);

        self::$em->remove($user);
        self::$em->flush();

        $this->redirectToRoute("autoriser_index");
    }
}
