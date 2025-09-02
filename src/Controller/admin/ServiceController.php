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

        $data = $this->getEntityManager()->getRepository(Service::class)->findBy([], ['id'=>'DESC']);


        return $this->render('admin/service/list.html.twig', 
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

            $form = $this->getFormFactory()->createBuilder(ServiceType::class)->getForm();
    
            $form->handleRequest($request);
    
            if($form->isSubmitted() && $form->isValid())
            {
                $service= $form->getData();
                    
                $this->getEntityManager()->persist($service);
                $this->getEntityManager()->flush();
                $this->redirectToRoute("service_index");
            }
    
            return $this->render('admin/service/new.html.twig', 
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
        
        $permission = $this->getEntityManager()->getRepository(Service::class)->find($id);
        
        $form = $this->getFormFactory()->createBuilder(ServiceType::class, $permission)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            $this->getEntityManager()->flush();
            $this->redirectToRoute("service_index");
            
        }

        return $this->render('admin/service/edit.html.twig', 
        [
            'form' => $form->createView(),
        ]);

    }

  
}