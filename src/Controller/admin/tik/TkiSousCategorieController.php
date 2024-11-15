<?php
namespace App\Controller\admin\tik;

use App\Controller\Controller;
use App\Entity\admin\tik\TkiSousCategorie;
use App\Form\admin\tik\TkiSousCategorieType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class TkiSousCategorieController extends Controller
{
    /**
     * @Route("/admin/tki-sous-categorie-liste", name="tki_sous_categorie_index")
     */
    public function index()
    {
        $data = self::$em->getRepository(TkiSousCategorie::class)->findBy([], ['id'=>'DESC']);

        self::$twig->display('admin/tik/sousCategorie/list.html.twig', 
        [
            'data' => $data
        ]);

    }

    /**
     * @Route("/admin/tki-sous-categorie-new", name="tki_sous_categorie_new")
     */
    public function new(Request $request)
    {
        $form = self::$validator->createBuilder(TkiSousCategorieType::class)->getForm();
        
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $sousCategorie = $form->getData();
            
            $selectedCategories   = $form->get('categories')->getData();
            $selectedAutresCategories = $form->get('autresCategories')->getData();

                foreach ($selectedCategories as $Categorie) {
                    $sousCategorie->addCategories($Categorie);
                }

                foreach ($selectedAutresCategories as $autresCategorie) {
                    $sousCategorie->addAutresCategories($autresCategorie);
                }

                self::$em->persist($sousCategorie);
                self::$em->flush();

                $this->redirectToRoute("tki_sous_categorie_index");
        }
        
        self::$twig->display('admin/tik/sousCategorie/new.html.twig', 
        [
            'form' => $form->createView()
        ]);
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
        $user = self::$em->getRepository(TkiSousCategorie::class)->find($id);
        
        $form = self::$validator->createBuilder(TkiSousCategorieType::class, $user)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            self::$em->flush();
            $this->redirectToRoute("tki_sous_categorie_index");
        }

        self::$twig->display('admin/tik/sousCategorie/edit.html.twig', [
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
        $sousCategorie = self::$em->getRepository(TkiSousCategorie::class)->find($id);

        if ($sousCategorie) {
            $autresCategories = $sousCategorie->getSousCategories();
            foreach ($autresCategories as $autreCategorie) {
                $autreCategorie->removePermission($autreCategorie);
                self::$em->persist($autreCategorie); // Persist the permission to register the removal
            }

            // Clear the collection to ensure Doctrine updates the join table
            $sousCategorie->getSousCategories()->clear();

            // Flush the entity manager to ensure the removal of the join table entries
            self::$em->flush();
        
                self::$em->remove($sousCategorie);
                self::$em->flush();
        }
        
        $this->redirectToRoute("tki_sous_categorie_index");
    }
}
