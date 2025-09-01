<?php

use Symfony\Component\HttpFoundation\Response;

namespace App\Controller\admin;

use App\Controller\Controller;
use App\Entity\admin\utilisateur\Fonction;
use App\Form\admin\utilisateur\FonctionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\BaseController;

class FonctionController extends BaseController
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

        $data = $this->getEntityManager()->getRepository(Fonction::class)->findBy([], ['id'=>'DESC']);
    
    
        return new \Symfony\Component\HttpFoundation\Response($this->getTwig()->render('admin/fonction/list.html.twig', 
        [
            'data' => $data
        ]));
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
        
        $form = $this->getFormFactory()->createBuilder(FonctionType::class)->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $fonction= $form->getData();
                
            $this->getEntityManager()->persist($fonction);
            $this->getEntityManager()->flush();
            $this->redirectToRoute("fonction_index");
        }

        $this->getTwig()->render('admin/fonction/new.html.twig', 
        [
            'form' => $form->createView()
        ]);
    }

}