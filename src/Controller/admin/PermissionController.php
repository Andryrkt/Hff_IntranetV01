<?php

namespace App\Controller\admin;



use App\Controller\Controller;
use App\Entity\Permission;
use App\Form\PermissionType;
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

                dd($role);
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

                   /**
     * @Route("/admin/permission/edit/{id}", name="permission_update")
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

        $permission = self::$em->getRepository(Permission::class)->find($id);
        
        $form = self::$validator->createBuilder(PermissionType::class, $permission)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            self::$em->flush();
            $this->redirectToRoute("permission_index");
            
        }

        self::$twig->display('admin/permission/edit.html.twig', [
            'form' => $form->createView(),
            'infoUserCours' => $infoUserCours,
            'boolean' => $boolean
        ]);

    }

    /**
    * @Route("/admin/permission/delete/{id}", name="permission_delete")
    *
    * @return void
    */
    public function delete($id)
    {
        $permission = self::$em->getRepository(Permission::class)->find($id);

        if ($permission) {
            $roles = $permission->getRoles();
            foreach ($roles as $role) {
                $permission->removeRole($role);
                self::$em->persist($role); // Persist the permission to register the removal
            }

            // Clear the collection to ensure Doctrine updates the join table
            $permission->getRoles()->clear();

            // Flush the entity manager to ensure the removal of the join table entries
            self::$em->flush();
        
                self::$em->remove($permission);
                self::$em->flush();
        }
        
        $this->redirectToRoute("permission_index");
    }
}