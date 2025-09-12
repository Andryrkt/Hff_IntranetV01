<?php


namespace App\Controller\admin;


use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Form\admin\ApplicationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApplicationController extends Controller
{
    /**
     * @Route("/admin/application", name="application_index")
     *
     * @return void
     */
    public function index()
    {    //verification si user connecter
        $this->verifierSessionUtilisateur();

        $data = $this->getEntityManager()->getRepository(Application::class)->findAll();

        //  dd($data[0]->getDerniereId());
        return $this->render(
            'admin/application/list.html.twig',
            [
                'data' => $data
            ]
        );
    }

    /**
     * @Route("/admin/application/new", name="application_new")
     */
    public function new(Request $request)
    {
        $form = $this->getFormFactory()->createBuilder(ApplicationType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $application = $form->getData();
            $this->getEntityManager()->persist($application);
            $this->getEntityManager()->flush();
            $this->redirectToRoute("application_index");
        }

        return $this->render(
            'admin/application/new.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/admin/application/edit/{id}", name="application_update")
     *
     * @return void
     */
    public function edit(Request $request, $id)
    {
        $user = $this->getEntityManager()->getRepository(Application::class)->find($id);

        $form = $this->getFormFactory()->createBuilder(ApplicationType::class, $user)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getEntityManager()->flush();
            $this->redirectToRoute("application_index");
        }

        return $this->render(
            'admin/application/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/application/delete/{id}", name="application_delete")
     *
     * @return void
     */
    public function delete($id)
    {
        $application = $this->getEntityManager()->getRepository(Application::class)->find($id);

        if ($application) {
            $roles = $application->getUsers();
            foreach ($roles as $role) {
                $application->removeUser($role);
                $this->getEntityManager()->persist($role); // Persist the permission to register the removal
            }

            // Clear the collection to ensure Doctrine updates the join table
            $application->getUsers()->clear();

            // Flush the entity manager to ensure the removal of the join table entries
            $this->getEntityManager()->flush();

            $this->getEntityManager()->remove($application);
            $this->getEntityManager()->flush();
        }

        $this->redirectToRoute("application_index");
    }
}
