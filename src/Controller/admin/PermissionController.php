<?php

namespace App\Controller\admin;


use App\Entity\Role;
use App\Controller\Controller;
use App\Entity\Permission;
use App\Form\PermissionType;
use App\Form\RoleType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PermissionController extends Controller
{
    /**
     * @Route("/admin/permission", name="permission_index")
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

    $data = self::$em->getRepository(Permission::class)->findBy([], ['id'=>'DESC']);


    self::$twig->display('admin/permission/list.html.twig', [
        'infoUserCours' => $infoUserCours,
        'boolean' => $boolean,
        'data' => $data
    ]);
    }

    /**
         * @Route("/admin/permission/new", name="permission_new")
         */
        public function new(Request $request)
        {
            $this->SessionStart();
            $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
            $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
            $text = file_get_contents($fichier);
            $boolean = strpos($text, $_SESSION['user']);
    
            $form = self::$validator->createBuilder(PermissionType::class)->getForm();
    
            $form->handleRequest($request);
    
            if($form->isSubmitted() && $form->isValid())
            {
                $role= $form->getData();
                self::$em->persist($role);
    
                self::$em->flush();
                $this->redirectToRoute("permission_index");
            }
    
            self::$twig->display('admin/permission/new.html.twig', [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'form' => $form->createView()
            ]);
        }

}