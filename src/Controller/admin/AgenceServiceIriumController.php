<?php

namespace App\Controller\admin;

use App\Controller\Controller;
use App\Entity\admin\AgenceServiceIrium;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\admin\utilisateur\AgenceServiceIriumType;
/**
 * @Route("/admin/agServIrium")
 */
class AgenceServiceIriumController extends Controller
{
    /**
     * @Route("/", name="AgServIrium_index")
     *
     * @return void
     */
    public function index()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $data = $this->getEntityManager()->getRepository(AgenceServiceIrium::class)->findBy([], ['id' => 'DESC']);

        return $this->render(
            'admin/AgenceServiceIrium/list.html.twig',
            [
                'data' => $data
            ]
        );
    }

    /**
     * @Route("/new", name="AgServIrium_new")
     *
     * @return void
     */
    public function new(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $form = $this->getFormFactory()->createBuilder(AgenceServiceIriumType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $AgenceServiceAutoriser = $form->getData();
            $this->getEntityManager()->persist($AgenceServiceAutoriser);

            $this->getEntityManager()->flush();
            $this->redirectToRoute("AgServIrium_index");
        }

        return $this->render(
            'admin/AgenceServiceIrium/new.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }


    /**
     * @Route("/edit/{id}", name="AgServIrium_update")
     *
     * @return void
     */
    public function edit(Request $request, $id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $user = $this->getEntityManager()->getRepository(AgenceServiceIrium::class)->find($id);

        $form = $this->getFormFactory()->createBuilder(AgenceServiceIriumType::class, $user)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            $this->getEntityManager()->flush();
            $this->redirectToRoute("AgServIrium_index");
        }

        return $this->render(
            'admin/AgenceServiceIrium/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/delete/{id}", name="AgServIrium_delete")
     *
     * @return void
     */
    public function delete($id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $user = $this->getEntityManager()->getRepository(AgenceServiceIrium::class)->find($id);

        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();

        $this->redirectToRoute("AgServIrium_index");
    }
}
