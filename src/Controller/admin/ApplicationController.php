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
                dd($application);
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
}