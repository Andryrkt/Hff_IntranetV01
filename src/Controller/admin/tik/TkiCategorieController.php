<?php

namespace App\Controller\admin\tik;

use App\Controller\Controller;
use App\Entity\admin\tik\TkiCategorie;
use App\Form\admin\tik\TkiCategorieType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class TkiCategorieController extends Controller
{
    /**
     * @Route("/admin/tki-categorie-new", name="tki_categorie_new")
     */
    public function new(Request $request)
    {
        $form = self::$validator->createBuilder(TkiCategorieType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $categorie = $form->getData();
            // Récupérer les sous-catégories sélectionnées dans le formulaire
            $sousCategories = $form->get('sousCategories')->getData();

            // Ajouter manuellement chaque sous-catégorie à la catégorie
            foreach ($sousCategories as $sousCategorie) {
                $categorie->addSousCategorie($sousCategorie);
            }

            self::$em->persist($categorie);
            self::$em->flush();

            $this->redirectToRoute("tki_all_categorie_index");
        }

        self::$twig->display(
            'admin/tik/categorie/new.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/admin/tki-categorie-edit/{id}", name="tki_categorie_edit")
     *
     * @param Request $request
     * @param int $id
     * @return void
     */
    public function edit(Request $request, int $id)
    {
        $categorie = self::$em->getRepository(TkiCategorie::class)->find($id);

        $form = self::$validator->createBuilder(TkiCategorieType::class, $categorie)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $sousCategories = $form->get('sousCategories')->getData();

            // Supprimer les sous-catégories qui ne sont plus sélectionnées
            foreach ($categorie->getSousCategories() as $sousCategorie) {
                if (!$sousCategories->contains($sousCategorie)) {
                    $categorie->removeSousCategorie($sousCategorie);
                }
            }

            // Ajouter les nouvelles sous-catégories sélectionnées
            foreach ($sousCategories as $sousCategorie) {
                if (!$categorie->getSousCategories()->contains($sousCategorie)) {
                    $categorie->addSousCategorie($sousCategorie);
                }
            }

            self::$em->flush();
            $this->redirectToRoute("tki_all_categorie_index");
        }

        self::$twig->display('admin/tik/categorie/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/tki-categorie-delete/{id}", name="tki_categorie_delete")
     *
     * @return void
     */
    public function delete($id)
    {
        $categorie = self::$em->getRepository(TkiCategorie::class)->find($id);

        if ($categorie) {
            $SousCategories = $categorie->getSousCategories();
            foreach ($SousCategories as $sousCategorie) {
                $categorie->removeSousCategorie($sousCategorie);
                self::$em->persist($sousCategorie); // Persist the permission to register the removal
            }

            // Clear the collection to ensure Doctrine updates the join table
            $categorie->getSousCategories()->clear();

            // Flush the entity manager to ensure the removal of the join table entries
            self::$em->flush();

            self::$em->remove($categorie);
            self::$em->flush();
        }

        $this->redirectToRoute("tki_all_categorie_index");
    }
}
