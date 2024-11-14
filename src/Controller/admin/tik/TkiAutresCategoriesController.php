<?php

namespace App\Controller\admin\tik;

use App\Controller\Controller;
use App\Entity\admin\tik\TkiAutresCategorie;
use Symfony\Component\HttpFoundation\Request;
use App\Form\admin\tik\TkiAutresCategorieType;
use Symfony\Component\Routing\Annotation\Route;

class TkiAutresCategoriesController extends Controller
{
    /**
     * @Route("/admin/tki-autres-categories-liste", name="tki_autres_categories_index")
     *
     * @return void
     */
    public function index()
    {
        $data = self::$em->getRepository(TkiAutresCategorie::class)->findBy([], ['id'=>'DESC']);

        self::$twig->display('admin/tik/autresCategories/list.html.twig', 
        [
            'data' => $data
        ]);
    }

    /**
     * @Route("/admin/tki-autres-categories-new", name="tki_autres_categories_new")
     *
     * @return void
     */
    public function new(Request $request)
    {
        $form = self::$validator->createBuilder(TkiAutresCategorieType::class)->getForm();
        
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $autresCategories = $form->getData();

            $selectedSousCategories = $form->get('sousCategories')->getData();

                foreach ($selectedSousCategories as $sousCategorie) {
                    $autresCategories->addSousCategories($sousCategorie);
                }

                self::$em->persist($autresCategories);
                self::$em->flush();

                $this->redirectToRoute("tki_autres_categories_index");
        }

        self::$twig->display('admin/tik/autresCategories/new.html.twig', 
        [
            'form' => $form->createView()
        ]);
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
        $categorie = self::$em->getRepository(TkiAutresCategorie::class)->find($id);
        
        $form = self::$validator->createBuilder(TkiAutresCategorieType::class, $categorie)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            self::$em->flush();
            $this->redirectToRoute("tki_autres_categories_index");
        }

        self::$twig->display('admin/tik/autresCategories/edit.html.twig', [
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
        $categorie = self::$em->getRepository(TkiAutresCategorie::class)->find($id);
        
                self::$em->remove($categorie);
                self::$em->flush();
        
        $this->redirectToRoute("tki_autres_categories_index");
    }
}