<?php


namespace App\Controller\admin;


use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Entity\admin\historisation\pageConsultation\PageHff;
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
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $data = $this->getEntityManager()->getRepository(Application::class)->findAll();
        $preparedData = $this->prepareForDisplay($data);

        return $this->render(
            'admin/application/list.html.twig',
            [
                'data' => $preparedData
            ]
        );
    }

    /**
     * @Route("/admin/application/new", name="application_new")
     */
    public function new(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

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
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $application = $this->getEntityManager()->getRepository(Application::class)->find($id);

        $form = $this->getFormFactory()->createBuilder(ApplicationType::class, $application)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $application = $form->getData();

            $this->getEntityManager()->persist($application);
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
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        /** @var Application $application */
        $application = $this->getEntityManager()->getRepository(Application::class)->find($id);

        if ($application) {
            /** @var PageHff[] $pages */
            $pages = $application->getPages();
            // Détacher les pages
            foreach ($pages as $page) {
                $page->setApplication(null);
            }

            $this->getEntityManager()->remove($application);
            $this->getEntityManager()->flush();
        }

        $this->redirectToRoute("application_index");
    }

    private function prepareForDisplay(array $data)
    {
        $preparedData = [];

        /** @var Application $application */
        foreach ($data as $application) {
            $baseData = [
                'id'         => $application->getId(),
                'nom'        => $application->getNom(),
                'codeApp'    => $application->getCodeApp(),
                'derniereId' => $application->getDerniereId(),
                'urlUpdate'  => $this->getUrlGenerator()->generate(
                    'application_update',
                    ['id' => $application->getId()]
                ),
                'urlDelete'  => $this->getUrlGenerator()->generate(
                    'application_delete',
                    ['id' => $application->getId()]
                ),
            ];

            $pages = $application->getPages();

            if ($pages->isEmpty()) {
                $preparedData[] = $baseData + ['pageName' => ''];
                continue;
            }

            foreach ($pages as $page) {
                $preparedData[] = $baseData + [
                    'pageName' => $page->getNom()
                ];
            }
        }

        return $preparedData;
    }
}
