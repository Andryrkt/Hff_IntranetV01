<?php

namespace App\Controller\admin;



use App\Entity\admin\Service;
use App\Controller\Controller;
use App\Form\admin\ServiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ServiceController extends Controller
{
    /**
     * @Route("/admin/service", name="service_index")
     *
     * @return void
     */
    public function index()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $data = self::$em->getRepository(Service::class)->findBy([], ['id'=>'DESC']);


        self::$twig->display('admin/service/list.html.twig', 
        [
            'data' => $data
        ]);
    }

    /**
         * @Route("/admin/service/new", name="service_new")
         */
        public function new(Request $request)
        {    
            //verification si user connecter
        $this->verifierSessionUtilisateur();

            $form = self::$validator->createBuilder(ServiceType::class)->getForm();
    
            $form->handleRequest($request);
    
            if($form->isSubmitted() && $form->isValid())
            {
                $service= $form->getData();
                    
                self::$em->persist($service);
                self::$em->flush();
                $this->redirectToRoute("service_index");
            }
    
            self::$twig->display('admin/service/new.html.twig', 
            [
                'form' => $form->createView()
            ]);
        }

                   /**
     * @Route("/admin/service/edit/{id}", name="service_update")
     *
     * @return void
     */
    public function edit(Request $request, $id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        
        $permission = self::$em->getRepository(Service::class)->find($id);
        
        $form = self::$validator->createBuilder(ServiceType::class, $permission)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            self::$em->flush();
            $this->redirectToRoute("service_index");
            
        }

        self::$twig->display('admin/service/edit.html.twig', 
        [
            'form' => $form->createView(),
        ]);

    }

  
}