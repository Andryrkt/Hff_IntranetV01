<?php

namespace App\Controller\admin;



use App\Entity\admin\Secteur;
use App\Controller\Controller;
use App\Form\admin\SecteurType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SecteurController extends Controller
{
    /**
     * @Route("/admin/secteur", name="secteur_index")
     *
     * @return void
     */
    public function index()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $data = self::$em->getRepository(Secteur::class)->findBy([], ['id'=>'DESC']);


        self::$twig->display('admin/secteur/list.html.twig', [
        
            'data' => $data
        ]);
    }

    /**
         * @Route("/admin/secteur/new", name="secteur_new")
         */
        public function new(Request $request)
        {
            //verification si user connecter
        $this->verifierSessionUtilisateur();
    
            $form = self::$validator->createBuilder(SecteurType::class)->getForm();
    
            $form->handleRequest($request);
    
            if($form->isSubmitted() && $form->isValid())
            {
                $secteur= $form->getData();
                    
                self::$em->persist($secteur);
                self::$em->flush();
                $this->redirectToRoute("secteur_index");
            }
    
            self::$twig->display('admin/secteur/new.html.twig', 
            [
                'form' => $form->createView()
            ]);
        }

                   /**
     * @Route("/admin/secteur/edit/{id}", name="secteur_update")
     *
     * @return void
     */
    public function edit(Request $request, $id)
    {

        //verification si user connecter
        $this->verifierSessionUtilisateur();
        
        $secteur = self::$em->getRepository(Secteur::class)->find($id);
        
        $form = self::$validator->createBuilder(SecteurType::class, $secteur)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            self::$em->flush();
            $this->redirectToRoute("secteur_index");
            
        }

        self::$twig->display('admin/secteur/edit.html.twig', 
        [
            'form' => $form->createView(),
        ]);

    }

   
}