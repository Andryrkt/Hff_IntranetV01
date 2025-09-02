<?php


namespace App\Controller\admin;

use App\Controller\Controller;


use App\Entity\admin\utilisateur\Permission;
use Symfony\Component\HttpFoundation\Request;
use App\Form\admin\utilisateur\PermissionType;
use Symfony\Component\HttpFoundation\Response;
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

        $data = $this->getEntityManager()->getRepository(Permission::class)->findBy([], ['id' => 'DESC']);

        return $this->render(
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


        $form = $this->getFormFactory()->createBuilder(PermissionType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $permission = $form->getData();

            $this->getEntityManager()->persist($permission);
            $this->getEntityManager()->flush();
            $this->redirectToRoute("permission_index");
        }

        return $this->render(
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

        $permission = $this->getEntityManager()->getRepository(Permission::class)->find($id);

        $form = $this->getFormFactory()->createBuilder(PermissionType::class, $permission)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            $this->getEntityManager()->flush();
            $this->redirectToRoute("permission_index");
        }

        return $this->render(
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

        $permission = $this->getEntityManager()->getRepository(Permission::class)->find($id);

        if ($permission) {
            $roles = $permission->getRoles();
            foreach ($roles as $role) {
                $permission->removeRole($role);
                $this->getEntityManager()->persist($role); // Persist the permission to register the removal
            }

            // Clear the collection to ensure Doctrine updates the join table
            $permission->getRoles()->clear();

            // Flush the entity manager to ensure the removal of the join table entries
            $this->getEntityManager()->flush();

            $this->getEntityManager()->remove($permission);
            $this->getEntityManager()->flush();
        }

        $this->redirectToRoute("permission_index");
    }
}
