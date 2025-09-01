<?php

namespace App\Controller\admin\tik;

use App\Controller\Controller;
use App\Entity\admin\tik\TkiSousCategorie;
use App\Form\admin\tik\TkiSousCategorieType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\BaseController;


class TkiSousCategorieController extends BaseController
{
    /**
     * @Route("/admin/tki-sous-categorie-new", name="tki_sous_categorie_new")
     */
    public function new(Request $request)
    {
        $form = $this->getFormFactory()->createBuilder(TkiSousCategorieType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sousCategorie = $form->getData();

            // Récupérer les catégories et autres catégories sélectionnées
            $autresCategories = $form->get('autresCategories')->getData();

            // Ajouter chaque catégorie et autre catégorie manuellement

            foreach ($autresCategories as $autreCategorie) {
                $sousCategorie->addAutresCategorie($autreCategorie);
            }

            $this->getEntityManager()->persist($sousCategorie);
            $this->getEntityManager()->flush();

            $this->redirectToRoute("tki_all_categorie_index");
        }

        $this->getTwig()->render(
            'admin/tik/sousCategorie/new.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/admin/tki-sous-categorie-edit/{id}", name="tki_sous_categorie_edit")
     *
     * @param Request $request
     * @param int $id
     * @return void
     */
    public function edit(Request $request, int $id)
    {
        $sousCategorie = $this->getEntityManager()->getRepository(TkiSousCategorie::class)->find($id);

        $form = $this->getFormFactory()->createBuilder(TkiSousCategorieType::class, $sousCategorie)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les catégories et autres catégories sélectionnées dans le formulaire
            $autresCategories = $form->get('autresCategories')->getData();

            // Synchroniser les autres catégories
            foreach ($sousCategorie->getAutresCategories() as $autreCategorie) {
                if (!$autresCategories->contains($autreCategorie)) {
                    $sousCategorie->removeAutresCategorie($autreCategorie);
                }
            }
            foreach ($autresCategories as $autreCategorie) {
                if (!$sousCategorie->getAutresCategories()->contains($autreCategorie)) {
                    $sousCategorie->addAutresCategorie($autreCategorie);
                }
            }

            $this->getEntityManager()->flush();
            $this->redirectToRoute("tki_all_categorie_index");
        }

        $this->getTwig()->render('admin/tik/sousCategorie/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/tki-sous-categorie-delete/{id}", name="tki_sous_categorie_delete")
     *
     * @return void
     */
    public function delete($id)
    {
        $sousCategorie = $this->getEntityManager()->getRepository(TkiSousCategorie::class)->find($id);

        if ($sousCategorie) {
            $autresCategories = $sousCategorie->getAutresCategories();
            foreach ($autresCategories as $autreCategorie) {
                $sousCategorie->removeAutresCategorie($autreCategorie);
                $this->getEntityManager()->persist($autreCategorie); // Persist the permission to register the removal
            }

            // Clear the collection to ensure Doctrine updates the join table
            $sousCategorie->getAutresCategories()->clear();

            // Flush the entity manager to ensure the removal of the join table entries
            $this->getEntityManager()->flush();

            $this->getEntityManager()->remove($sousCategorie);
            $this->getEntityManager()->flush();
        }

        $this->redirectToRoute("tki_all_categorie_index");
    }
}
