<?php

namespace App\Controller\admin;


use App\Controller\Controller;
use App\Entity\admin\utilisateur\User;
use App\Form\admin\utilisateur\UserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends Controller
{
    /**
     * @Route("/admin/utilisateur/new", name="utilisateur_new")
     */
    public function new(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        $user = new User();
        if ($this->getUser()->getChefService()) {
            $nomPrenomChefService = $this->getUser()->getChefService()->getNom() . ' ' . $this->getUser()->getChefService()->getPrenoms();
            $user->setSuperieur($nomPrenomChefService);
        }

        $form = $this->getFormFactory()->createBuilder(UserType::class, $user)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateur = $form->getData();

            $selectedApplications = $form->get('applications')->getData();

            foreach ($selectedApplications as $application) {
                $utilisateur->addApplication($application);
            }

            $selectedRoles = $form->get('roles')->getData();

            foreach ($selectedRoles as $role) {
                $utilisateur->addRole($role);
            }

            $this->getEntityManager()->persist($utilisateur);
            $this->getEntityManager()->flush();

            $this->redirectToRoute("utilisateur_index");
        }

        //$this->logUserVisit('utilisateur_new'); // historisation du page visité par l'utilisateur

        return $this->render('admin/utilisateur/new.html.twig', [
            'form' => $form->createView()
        ]);
    }



    /**
     * @Route("/admin/utilisateur/edit/{id}", name="utilisateur_update")
     *
     * @return void
     */
    public function edit(Request $request, $id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $user = $this->getEntityManager()->getRepository(User::class)->find($id);
        if ($this->getUser()->getChefService()) {
            $nomPrenomChefService = $this->getUser()->getChefService()->getNom() . ' ' . $this->getUser()->getChefService()->getPrenoms();
            $user->setSuperieur($nomPrenomChefService);
        }


        $form = $this->getFormFactory()->createBuilder(UserType::class, $user)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            $this->getEntityManager()->flush();
            return $this->redirectToRoute("utilisateur_index");
        }

        //$this->logUserVisit('utilisateur_update', ['id' => $id]); // historisation du page visité par l'utilisateur 

        return $this->render('admin/utilisateur/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/utilisateur", name="utilisateur_index")
     *
     * @return void
     */
    public function index()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $data = $this->getEntityManager()->getRepository(User::class)->findBy([], ['id' => 'DESC']);
        $data = $this->transformIdEnObjetEntitySuperieur($data);

        //$this->logUserVisit('utilisateur_index'); // historisation du page visité par l'utilisateur

        return $this->render('admin/utilisateur/list.html.twig', [
            'data' => $data
        ]);
    }

    private function transformIdEnObjetEntitySuperieur(array $data): array
    {
        return $data;
    }


    /**
     * @Route("/admin/utilisateur/delete/{id}", name="utilisateur_delete")
     *
     * @return void
     */
    public function delete($id)
    {
        // Vérification de la session utilisateur
        $this->verifierSessionUtilisateur();

        // Récupération de l'utilisateur
        $user = $this->getEntityManager()->getRepository(User::class)->find($id);


        // Supprimer les relations manuellement avant suppression
        $user->getRoles()->clear();
        $user->getApplications()->clear();
        $user->getAgencesAutorisees()->clear();
        $user->getServiceAutoriser()->clear();
        $user->getPermissions()->clear();

        foreach ($user->getUserLoggers() as $logger) {
            $this->getEntityManager()->remove($logger);
        }

        // foreach ($user->getCommentaireDitOrs() as $commentaire) {
        //     $this->getEntityManager()->remove($commentaire);
        // }

        // foreach ($user->getSupportInfoUser() as $support) {
        //     $this->getEntityManager()->remove($support);
        // }

        // foreach ($user->getTikPlanningUser() as $planning) {
        //     $this->getEntityManager()->remove($planning);
        // }

        // Supprimer l'utilisateur
        $this->getEntityManager()->remove($user);
        $this->getEntityManager()->flush();

        return $this->redirectToRoute("utilisateur_index");
    }


    /**
     * @Route("/admin/utilisateur/show/{id}", name="utilisateur_show")
     *
     * @return void
     */
    public function show($id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $data = $this->getEntityManager()->getRepository(User::class)->find($id);

        //$this->logUserVisit('utilisateur_show', ['id' => $id]); // historisation du page visité par l'utilisateur 

        return $this->render('admin/utilisateur/details.html.twig', [
            'data' => $data
        ]);
    }
}
