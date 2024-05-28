<?php

namespace App\Controller\admin;

use App\Controller\Controller;
use App\Entity\ProfilUser;
use App\Entity\ProfilUserEntity;
use App\Entity\User;
use App\Form\ProfilUserType;
use App\Form\UserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class UserController extends Controller
{

     /**
     * @Route("/admin/utilisateur", name="utilisateur_index")
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

    $data = self::$em->getRepository(User::class)->findBy([], ['id'=>'DESC']);


    self::$twig->display('admin/utilisateur/list.html.twig', [
        'infoUserCours' => $infoUserCours,
        'boolean' => $boolean,
        'data' => $data
    ]);
    }

    /**
         * @Route("/admin/utilisateur/new", name="utilisateur_new")
         */
        public function new(Request $request)
        {
            $this->SessionStart();
            $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
            $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
            $text = file_get_contents($fichier);
            $boolean = strpos($text, $_SESSION['user']);
    
            $form = self::$validator->createBuilder(UserType::class)->getForm();
    
            $form->handleRequest($request);
    
            if($form->isSubmitted() && $form->isValid())
            {
                $personnel= $form->getData();
                self::$em->persist($personnel);
    
                self::$em->flush();
                $this->redirectToRoute("utilisateur_index");
            }
    
            self::$twig->display('admin/utilisateur/new.html.twig', [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'form' => $form->createView()
            ]);
        }

}