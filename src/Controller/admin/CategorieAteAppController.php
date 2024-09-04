<?php

namespace App\Controller\admin;


use App\Controller\Controller;
use App\Form\CategorieAteAppType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\admin\dit\CategorieAteApp;

class CategorieAteAppController extends Controller
{
    /**
     * @Route("/admin/categorieAte", name="categorieAte_index")
     *
     * @return void
     */
    public function index()
    {
        $data = self::$em->getRepository(CategorieAteApp::class)->findBy([], ['id'=>'DESC']);
    
        //  dd($data[0]->getDerniereId());
        self::$twig->display('admin/categorieAte/list.html.twig', [
            
            'data' => $data
        ]);
    }

    /**
         * @Route("/admin/categorieAte/new", name="categorieAte_new")
         */
        public function new(Request $request)
        {
            $this->SessionStart();
            $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
            $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
            $text = file_get_contents($fichier);
            $boolean = strpos($text, $_SESSION['user']);
    
            $form = self::$validator->createBuilder(CategorieAteAppType::class)->getForm();
    
            $form->handleRequest($request);
    
            if($form->isSubmitted() && $form->isValid())
            {
                $categorieAte= $form->getData();
                
                self::$em->persist($categorieAte);
                self::$em->flush();
                $this->redirectToRoute("categorieAte_index");
            }
    
            self::$twig->display('admin/categorieAte/new.html.twig', [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'form' => $form->createView()
            ]);
        }

   
}