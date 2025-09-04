<?php

namespace App\Controller\admin;


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

        $data = $this->getEntityManager()->getRepository(Role::class)->findBy([], ['id' => 'DESC']);

        return $this->render(
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

        $form = $this->getFormFactory()->createBuilder(RoleType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $role = $form->getData();


            $selectedPermissions = $form->get('permissions')->getData();

            foreach ($selectedPermissions as $permission) {
                $role->addPermission($permission);
            }

            $this->getEntityManager()->persist($role);
            $this->getEntityManager()->flush();

            $this->redirectToRoute("role_index");
        }

        return $this->render('admin/role/new.html.twig', 
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

        $user = $this->getEntityManager()->getRepository(Role::class)->find($id);

        $form = $this->getFormFactory()->createBuilder(RoleType::class, $user)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            $this->getEntityManager()->flush();
            $this->redirectToRoute("role_index");
        }

        return $this->render('admin/role/edit.html.twig', [
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

        $role = $this->getEntityManager()->getRepository(Role::class)->find($id);

        if ($role) {
            $permissions = $role->getPermissions();
            foreach ($permissions as $permission) {
                $role->removePermission($permission);
                $this->getEntityManager()->persist($permission); // Persist the permission to register the removal
            }

            // Clear the collection to ensure Doctrine updates the join table
            $role->getPermissions()->clear();

            // Flush the entity manager to ensure the removal of the join table entries
            $this->getEntityManager()->flush();

            $this->getEntityManager()->remove($role);
            $this->getEntityManager()->flush();
        }

        $this->redirectToRoute("role_index");
    }
}
