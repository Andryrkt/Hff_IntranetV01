<?php

namespace App\Controller\admin;


use App\Entity\Permission;
use App\Controller\Controller;
use App\Entity\admin\utilisateur\Role;
use App\Form\admin\utilisateur\RoleType;
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
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $data = self::$em->getRepository(Role::class)->findBy([], ['id' => 'DESC']);

        self::$twig->display(
            'admin/role/list.html.twig',
            [
                'data' => $data
            ]
        );
    }

    /**
     * @Route("/admin/role/new", name="role_new")
     */
    public function new(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $form = self::$validator->createBuilder(RoleType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $role = $form->getData();


            $selectedPermissions = $form->get('permissions')->getData();

            foreach ($selectedPermissions as $permission) {
                $role->addPermission($permission);
            }

            self::$em->persist($role);
            self::$em->flush();

            $this->redirectToRoute("role_index");
        }

        self::$twig->display(
            'admin/role/new.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }


    /**
     * @Route("/admin/role/edit/{id}", name="role_update")
     *
     * @return void
     */
    public function edit(Request $request, $id)
    {

        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $user = self::$em->getRepository(Role::class)->find($id);

        $form = self::$validator->createBuilder(RoleType::class, $user)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            self::$em->flush();
            $this->redirectToRoute("role_index");
        }

        self::$twig->display('admin/role/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/role/delete/{id}", name="role_delete")
     *
     * @return void
     */
    public function delete($id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $role = self::$em->getRepository(Role::class)->find($id);

        if ($role) {
            $permissions = $role->getPermissions();
            foreach ($permissions as $permission) {
                $role->removePermission($permission);
                self::$em->persist($permission); // Persist the permission to register the removal
            }

            // Clear the collection to ensure Doctrine updates the join table
            $role->getPermissions()->clear();

            // Flush the entity manager to ensure the removal of the join table entries
            self::$em->flush();

            self::$em->remove($role);
            self::$em->flush();
        }

        $this->redirectToRoute("role_index");
    }
}
