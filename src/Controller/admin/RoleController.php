<?php

namespace App\Controller\admin;


use App\Entity\Role;
use App\Controller\Controller;
use App\Form\RoleType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RoleController extends Controller
{
    /**
     * @Route("/admin/role", name="role_index")
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

    $data = self::$em->getRepository(Role::class)->findBy([], ['id'=>'DESC']);


    self::$twig->display('admin/role/list.html.twig', [
        'infoUserCours' => $infoUserCours,
        'boolean' => $boolean,
        'data' => $data
    ]);
    }

    /**
         * @Route("/admin/role/new", name="role_new")
         */
        public function new(Request $request)
        {
            $this->SessionStart();
            $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
            $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
            $text = file_get_contents($fichier);
            $boolean = strpos($text, $_SESSION['user']);
    
            $form = self::$validator->createBuilder(RoleType::class)->getForm();
    
            $form->handleRequest($request);
    
            if($form->isSubmitted() && $form->isValid())
            {
                $role= $form->getData();
                self::$em->persist($role);
    
                self::$em->flush();
                $this->redirectToRoute("role_index");
            }
    
            self::$twig->display('admin/role/new.html.twig', [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'form' => $form->createView()
            ]);
        }

}