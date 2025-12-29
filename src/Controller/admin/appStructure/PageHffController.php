<?php

namespace App\Controller\admin\appStructure;

use App\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\admin\historisation\pageConsultation\PageHff;
use App\Form\admin\PageHffType;

/**
 * @Route("/admin/page-hff")
 */
class PageHffController extends Controller
{
    /**
     * @Route("/", name="page_hff_index")
     */
    public function index()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $data = $this->getEntityManager()->getRepository(PageHff::class)->findAll();

        return $this->render('admin/page-hff/list.html.twig', [
            'data' => $data,
        ]);
    }

    /**
     * @Route("/new", name="page_hff_new")
     */
    public function new(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $form = $this->getFormFactory()->createBuilder(PageHffType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pageHff = $form->getData();

            $this->getEntityManager()->persist($pageHff);
            $this->getEntityManager()->flush();

            $this->redirectToRoute("page_hff_index");
        }

        return $this->render('admin/page-hff/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/{id}", name="page_hff_update")
     */
    public function edit(Request $request, $id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $pageHff = $this->getEntityManager()->getRepository(PageHff::class)->find($id);

        $form = $this->getFormFactory()->createBuilder(PageHffType::class, $pageHff)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pageHff = $form->getData();

            $this->getEntityManager()->persist($pageHff);
            $this->getEntityManager()->flush();
            return $this->redirectToRoute("page_hff_index");
        }

        return $this->render('admin/page-hff/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{id}", name="page_hff_delete")
     */
    public function delete($id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $pageHff = $this->getEntityManager()->getRepository(PageHff::class)->find($id);

        $this->getEntityManager()->remove($pageHff);
        $this->getEntityManager()->flush();

        $this->redirectToRoute("page_hff_index");
    }
}
