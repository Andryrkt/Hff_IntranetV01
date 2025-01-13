<?php

namespace App\Controller\admin;


use App\Entity\Role;
use App\Form\RoleType;
use App\Entity\Permission;
use App\Controller\Controller;
use App\Entity\admin\Societte;
use App\Form\admin\SocietteType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SocietteController extends Controller
{
    /**
     * @Route("/admin/societte", name="societte_index")
     *
     * @return void
     */
    public function index()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

    $data = self::$em->getRepository(Societte::class)->findBy([], ['id'=>'DESC']);


    self::$twig->display('admin/societte/list.html.twig', 
    [
        'data' => $data
    ]);
    }

    /**
         * @Route("/admin/societte/new", name="societte_new")
         */
        public function new(Request $request)
        {
            //verification si user connecter
        $this->verifierSessionUtilisateur();

            $form = self::$validator->createBuilder(SocietteType::class)->getForm();
    
            $form->handleRequest($request);
    
            if($form->isSubmitted() && $form->isValid())
            {
                $societte= $form->getData();
                

                self::$em->persist($societte);
                self::$em->flush();

                $this->redirectToRoute("societte_index");
            }
    
            self::$twig->display('admin/societte/new.html.twig', 
            [
                'form' => $form->createView()
            ]);
        }


                /**
     * @Route("/admin/societte/edit/{id}", name="societte_update")
     *
     * @return void
     */
    public function edit(Request $request, $id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $user = self::$em->getRepository(Societte::class)->find($id);
        
        $form = self::$validator->createBuilder(SocietteType::class, $user)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            self::$em->flush();
            $this->redirectToRoute("societte_index");
            
        }

        self::$twig->display('admin/societte/edit.html.twig', 
        [
            'form' => $form->createView(),
        ]);

    }

    /**
    * @Route("/admin/societte/delete/{id}", name="societte_delete")
    *
    * @return void
    */
    public function delete($id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        
        $societte = self::$em->getRepository(Societte::class)->find($id);

        if ($societte) {
            $typeReparations = $societte->getTypeReparations();
            foreach ($typeReparations as $typeReparation) {
                $societte->removeTypeReparation($typeReparation);
                self::$em->persist($typeReparation); // Persist the permission to register the removal
            }

            // Clear the collection to ensure Doctrine updates the join table
            $societte->getTypeReparations()->clear();

            // Flush the entity manager to ensure the removal of the join table entries
            self::$em->flush();
        
                self::$em->remove($societte);
                self::$em->flush();
        }
        
        $this->redirectToRoute("societte_index");
    }
}