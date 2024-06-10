<?php

namespace App\Controller\admin;

use App\Entity\Application;
use App\Controller\Controller;
use App\Form\ApplicationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApplicationController extends Controller
{
    /**
     * @Route("/admin/application", name="application_index")
     *
     * @return void
     */
    public function index()
    {
        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);
    
        $data = self::$em->getRepository(Application::class)->findBy([], ['id'=>'DESC']);
    
        //  dd($data[0]->getDerniereId());
        self::$twig->display('admin/application/list.html.twig', [
            'infoUserCours' => $infoUserCours,
            'boolean' => $boolean,
            'data' => $data
        ]);
    }

    /**
         * @Route("/admin/application/new", name="application_new")
         */
        public function new(Request $request)
        {
            $this->SessionStart();
            $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
            $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
            $text = file_get_contents($fichier);
            $boolean = strpos($text, $_SESSION['user']);
    
            $form = self::$validator->createBuilder(ApplicationType::class)->getForm();
    
            $form->handleRequest($request);
    
            if($form->isSubmitted() && $form->isValid())
            {
                $application= $form->getData();
                
                self::$em->persist($application);
                self::$em->flush();
                $this->redirectToRoute("application_index");
            }
    
            self::$twig->display('admin/application/new.html.twig', [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'form' => $form->createView()
            ]);
        }

    /**
     * @Route("/admin/application/edit/{id}", name="application_update")
     *
     * @return void
     */
    public function edit(Request $request, $id)
    {

        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);

        $user = self::$em->getRepository(Application::class)->find($id);
        
        $form = self::$validator->createBuilder(ApplicationType::class, $user)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            self::$em->flush();
            $this->redirectToRoute("application_index");
            
        }

        self::$twig->display('admin/application/edit.html.twig', [
            'form' => $form->createView(),
            'infoUserCours' => $infoUserCours,
            'boolean' => $boolean
        ]);

    }

     /**
    * @Route("/admin/application/delete/{id}", name="application_delete")
    *
    * @return void
    */
    public function delete($id)
    {
        $application = self::$em->getRepository(Application::class)->find($id);

        if ($application) {
            $roles = $application->getUsers();
            foreach ($roles as $role) {
                $application->removeUser($role);
                self::$em->persist($role); // Persist the permission to register the removal
            }

            // Clear the collection to ensure Doctrine updates the join table
            $application->getUsers()->clear();

            // Flush the entity manager to ensure the removal of the join table entries
            self::$em->flush();
        
                self::$em->remove($application);
                self::$em->flush();
        }
        
        
        $this->redirectToRoute("application_index");
    }
}