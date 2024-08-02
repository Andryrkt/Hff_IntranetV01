<?php

namespace App\Controller\admin;

use App\Controller\Controller;
use App\Entity\Fonction;
use App\Form\FonctionType;
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
        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);
    
        $data = self::$em->getRepository(Fonction::class)->findBy([], ['id'=>'DESC']);
    
    
        self::$twig->display('admin/fonction/list.html.twig', [
            'infoUserCours' => $infoUserCours,
            'boolean' => $boolean,
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
        $this->SessionStart();
            $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
            $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
            $text = file_get_contents($fichier);
            $boolean = strpos($text, $_SESSION['user']);
    
            $form = self::$validator->createBuilder(FonctionType::class)->getForm();
    
            $form->handleRequest($request);
    
            if($form->isSubmitted() && $form->isValid())
            {
                $fonction= $form->getData();
                    
                self::$em->persist($fonction);
                self::$em->flush();
                $this->redirectToRoute("fonction_index");
            }
    
            self::$twig->display('admin/fonction/new.html.twig', [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'form' => $form->createView()
            ]);
    }

}