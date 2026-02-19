<?php

namespace App\Controller\admin\appStructure;

use App\Controller\Controller;
use App\Entity\admin\ApplicationProfil;
use App\Form\admin\ProfilType;
use App\Entity\admin\utilisateur\Profil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends Controller
{
    /**
     * @Route("/admin/profil/list", name="profil_index")
     *
     * @return void
     */
    public function index()
    {
        $data = $this->getEntityManager()->getRepository(Profil::class)->findAll();
        $preparedData = $this->prepareForDisplay($data);

        return $this->render(
            'admin/profil/list.html.twig',
            [
                'data' => $preparedData
            ]
        );
    }

    /**
     * @Route("/admin/profil/new", name="profil_new")
     */
    public function new(Request $request)
    {
        $profil = new Profil();
        $form = $this->getFormFactory()->createBuilder(ProfilType::class, $profil)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedApps = $form->get('applications')->getData();

            // Créer les nouveaux liens
            foreach ($selectedApps as $app) {
                $applicationProfil = new ApplicationProfil($profil, $app);
                $profil->addApplicationProfil($applicationProfil);
                $this->getEntityManager()->persist($applicationProfil);
            }

            $this->getEntityManager()->persist($profil);
            $this->getEntityManager()->flush();

            $this->redirectToRoute("profil_index");
        }

        return $this->render(
            'admin/profil/new.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/admin/profil/edit/{id}", name="profil_update")
     *
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function edit(Request $request, $id)
    {
        $em = $this->getEntityManager();

        /** @var Profil $profil */
        $profil = $em->getRepository(Profil::class)->find($id);
        $applicationsLinked = $profil->getApplications()->toArray();

        $form = $this->getFormFactory()->createBuilder(ProfilType::class, $profil)->getForm();

        // Pré-remplir le champ 'applications'
        $form->get('applications')->setData($applicationsLinked);
        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $selectedApps = $form->get('applications')->getData(); // Collection d'Application

            // Supprimer les liens qui ne sont plus sélectionnés
            foreach ($profil->getApplicationProfils() as $ap) {
                if (!in_array($ap->getApplication(), $selectedApps, true)) {
                    $profil->removeApplicationProfil($ap);
                    $em->remove($ap); // cascade remove assure suppression automatique
                }
            }

            // Ajouter les nouveaux liens
            foreach ($selectedApps as $app) {
                $exists = false;
                foreach ($profil->getApplicationProfils() as $ap) {
                    if ($ap->getApplication() === $app) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $applicationProfil = new ApplicationProfil($profil, $app);
                    $profil->addApplicationProfil($applicationProfil);
                    $em->persist($applicationProfil);
                }
            }

            $em->persist($profil);
            $em->flush();

            $this->redirectToRoute("profil_index");
        }

        return $this->render(
            'admin/profil/edit.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    private function prepareForDisplay(array $data)
    {
        $preparedData = [];

        /** @var Profil $profil */
        foreach ($data as $profil) {
            $baseData = [
                'urlUpdate' => $this->getUrlGenerator()->generate(
                    'profil_update',
                    ['id' => $profil->getId()]
                ),
                'reference'   => $profil->getReference(),
                'designation' => $profil->getDesignation(),
                'societe'     => $profil->getSociete()->getCodeSociete() . ' - ' . $profil->getSociete()->getNom(),
            ];

            $applications = $profil->getApplications();

            if ($applications->isEmpty()) {
                $preparedData[] = $baseData + ['codeApp' => '', 'nomApp' => ''];
                continue;
            }

            foreach ($applications as $application) {
                $preparedData[] = $baseData + [
                    'codeApp' => $application->getCodeApp(),
                    'nomApp'  => $application->getNom()
                ];
            }
        }

        return $preparedData;
    }
}
