<?php

namespace App\Controller\admin\appStructure;

use App\Controller\Controller;
use App\Form\admin\ProfilType;
use App\Entity\admin\utilisateur\Profil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends Controller
{
    /**
     * @Route("/admin/profil/list", name="profil_index")
     *
     * @return void
     */
    public function index()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $data = $this->getEntityManager()->getRepository(Profil::class)->findAll();

        return $this->render(
            'admin/profil/list.html.twig',
            [
                'data' => $data
            ]
        );
    }

    /**
     * @Route("/admin/profil/new", name="profil_new")
     */
    public function new(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $form = $this->getFormFactory()->createBuilder(ProfilType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $profil = $form->getData();

            $this->getEntityManager()->persist($profil);
            $this->getEntityManager()->flush();

            $this->redirectToRoute("profil_index");
        }

        return $this->render(
            'admin/profil/new.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/admin/profil/edit/{id}", name="profil_update")
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function edit(Request $request, $id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $profil = $this->getEntityManager()->getRepository(Profil::class)->find($id);

        $form = $this->getFormFactory()->createBuilder(ProfilType::class, $profil)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $profil = $form->getData();

            $this->getEntityManager()->persist($profil);
            $this->getEntityManager()->flush();

            $this->redirectToRoute("profil_index");
        }

        return $this->render(
            'admin/profil/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
