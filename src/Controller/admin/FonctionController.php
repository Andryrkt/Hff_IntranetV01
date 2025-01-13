<?php

namespace App\Controller\admin;

use App\Controller\Controller;
use App\Entity\admin\utilisateur\Fonction;
use App\Form\admin\utilisateur\FonctionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FonctionController extends Controller
{
    /**
     * @Route("/admin/fonction", name="fonction_index")
     *
     * @return void
     */
    public function index()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $data = self::$em->getRepository(Fonction::class)->findBy([], ['id'=>'DESC']);
    
    
        self::$twig->display('admin/fonction/list.html.twig', 
        [
            'data' => $data
        ]);
    }

    /**
     * @Route("/admin/fonction/new", name="fonction_new")
     *
     * @return void
     */
    public function new(Request $request)
    {    
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        
        $form = self::$validator->createBuilder(FonctionType::class)->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $fonction= $form->getData();
                
            self::$em->persist($fonction);
            self::$em->flush();
            $this->redirectToRoute("fonction_index");
        }

        self::$twig->display('admin/fonction/new.html.twig', 
        [
            'form' => $form->createView()
        ]);
    }

}