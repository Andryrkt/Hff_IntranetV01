<?php

namespace App\Controller\admin\tik;

use App\Controller\Controller;
use App\Entity\admin\tik\TkiAutresCategorie;
use Symfony\Component\HttpFoundation\Request;
use App\Form\admin\tik\TkiAutresCategorieType;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\BaseController;

class TkiAutresCategoriesController extends BaseController
{
    /**
     * @Route("/admin/tki-autres-categories-new", name="tki_autres_categories_new")
     *
     * @return void
     */
    public function new(Request $request)
    {
        $form = $this->getFormFactory()->createBuilder(TkiAutresCategorieType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $autresCategorie = $form->getData();

            $this->getEntityManager()->persist($autresCategorie);
            $this->getEntityManager()->flush();

            $this->redirectToRoute("tki_all_categorie_index");
        }

        $this->getTwig()->render(
            'admin/tik/autresCategories/new.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/admin/tki-autres-categories-edit/{id}", name="tki_autres_categories_edit")
     *
     * @param Request $request
     * @param int $id
     * @return void
     */
    public function edit(Request $request, int $id)
    {
        $autresCategorie = $this->getEntityManager()->getRepository(TkiAutresCategorie::class)->find($id);

        $form = $this->getFormFactory()->createBuilder(TkiAutresCategorieType::class, $autresCategorie)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getEntityManager()->flush();
            $this->redirectToRoute("tki_all_categorie_index");
        }

        $this->getTwig()->render('admin/tik/autresCategories/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/tki-autres-categories-delete/{id}", name="tki_autres_categories_delete")
     *
     * @return void
     */
    public function delete($id)
    {
        $categorie = $this->getEntityManager()->getRepository(TkiAutresCategorie::class)->find($id);

        $this->getEntityManager()->remove($categorie);
        $this->getEntityManager()->flush();

        $this->redirectToRoute("tki_all_categorie_index");
    }
}
