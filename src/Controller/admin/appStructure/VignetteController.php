<?php

namespace App\Controller\admin\appStructure;

use App\Controller\Controller;
use App\Entity\admin\Vignette;
use App\Form\admin\VignetteType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VignetteController extends Controller
{
    /**
     * @Route("/admin/vignette/list", name="vignette_index")
     *
     * @return void
     */
    public function index()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $data = $this->getEntityManager()->getRepository(Vignette::class)->findAll();

        return $this->render(
            'admin/vignette/list.html.twig',
            [
                'data' => $data
            ]
        );
    }

    /**
     * @Route("/admin/vignette/new", name="vignette_new")
     */
    public function new(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $form = $this->getFormFactory()->createBuilder(VignetteType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $vignette = $form->getData();

            $this->getEntityManager()->persist($vignette);
            $this->getEntityManager()->flush();

            $this->redirectToRoute("vignette_index");
        }

        return $this->render(
            'admin/vignette/new.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/admin/vignette/edit/{id}", name="vignette_update")
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function edit(Request $request, $id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $vignette = $this->getEntityManager()->getRepository(Vignette::class)->find($id);

        $form = $this->getFormFactory()->createBuilder(VignetteType::class, $vignette)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $vignette = $form->getData();

            $this->getEntityManager()->persist($vignette);
            $this->getEntityManager()->flush();

            $this->redirectToRoute("vignette_index");
        }

        return $this->render(
            'admin/vignette/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
