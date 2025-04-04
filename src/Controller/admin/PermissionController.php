<?php

namespace App\Controller\admin;



use App\Controller\Controller;
use App\Entity\admin\utilisateur\Permission;
use Symfony\Component\HttpFoundation\Request;
use App\Form\admin\utilisateur\PermissionType;
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
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $data = self::$em->getRepository(Permission::class)->findBy([], ['id' => 'DESC']);

        self::$twig->display(
            'admin/permission/list.html.twig',
            [
                'data' => $data
            ]
        );
    }

    /**
     * @Route("/admin/permission/new", name="permission_new")
     */
    public function new(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();


        $form = self::$validator->createBuilder(PermissionType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $permission = $form->getData();

            self::$em->persist($permission);
            self::$em->flush();
            $this->redirectToRoute("permission_index");
        }

        self::$twig->display(
            'admin/permission/new.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/admin/permission/edit/{id}", name="permission_update")
     *
     * @return void
     */
    public function edit(Request $request, $id)
    {

        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $permission = self::$em->getRepository(Permission::class)->find($id);

        $form = self::$validator->createBuilder(PermissionType::class, $permission)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            self::$em->flush();
            $this->redirectToRoute("permission_index");
        }

        self::$twig->display(
            'admin/permission/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/permission/delete/{id}", name="permission_delete")
     *
     * @return void
     */
    public function delete($id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

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
