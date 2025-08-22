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
        $nomPrenomChefService = $this->getUser()->getChefService()->getNom() . ' ' . $this->getUser()->getChefService()->getPrenoms();
        $user = new User();
        $user->setSuperieur($nomPrenomChefService);

        $form = self::$validator->createBuilder(UserType::class, $user)->getForm();

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

            self::$em->persist($utilisateur);
            self::$em->flush();

            $this->redirectToRoute("utilisateur_index");
        }

        //$this->logUserVisit('utilisateur_new'); // historisation du page visité par l'utilisateur

        self::$twig->display('admin/utilisateur/new.html.twig', [
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

        $user = self::$em->getRepository(User::class)->find($id);
        if ($this->getUser()->getChefService()) {
            $nomPrenomChefService = $this->getUser()->getChefService()->getNom() . ' ' . $this->getUser()->getChefService()->getPrenoms();
            $user->setSuperieur($nomPrenomChefService);
        }


        $form = self::$validator->createBuilder(UserType::class, $user)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            self::$em->flush();
            return $this->redirectToRoute("utilisateur_index");
        }

        //$this->logUserVisit('utilisateur_update', ['id' => $id]); // historisation du page visité par l'utilisateur 

        self::$twig->display('admin/utilisateur/edit.html.twig', [
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

        $data = self::$em->getRepository(User::class)->findBy([], ['id' => 'DESC']);
        $data = $this->transformIdEnObjetEntitySuperieur($data);

        //$this->logUserVisit('utilisateur_index'); // historisation du page visité par l'utilisateur

        self::$twig->display('admin/utilisateur/list.html.twig', [
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
        $user = self::$em->getRepository(User::class)->find($id);


        // Supprimer les relations manuellement avant suppression
        foreach ($user->getRoles() as $role) {
            $user->removeRole($role);
        }

        foreach ($user->getApplications() as $application) {
            $user->removeApplication($application);
        }

        foreach ($user->getAgencesAutorisees() as $agence) {
            $user->removeAgenceAutorise($agence);
        }

        foreach ($user->getServiceAutoriser() as $service) {
            $user->removeServiceAutoriser($service);
        }

        foreach ($user->getPermissions() as $permission) {
            $user->removePermission($permission);
        }

        foreach ($user->getUserLoggers() as $logger) {
            self::$em->remove($logger);
        }

        // foreach ($user->getCommentaireDitOrs() as $commentaire) {
        //     self::$em->remove($commentaire);
        // }

        // foreach ($user->getSupportInfoUser() as $support) {
        //     self::$em->remove($support);
        // }

        // foreach ($user->getTikPlanningUser() as $planning) {
        //     self::$em->remove($planning);
        // }

        // Supprimer l'utilisateur
        self::$em->remove($user);
        self::$em->flush();

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

        $data = self::$em->getRepository(User::class)->find($id);

        //$this->logUserVisit('utilisateur_show', ['id' => $id]); // historisation du page visité par l'utilisateur 

        self::$twig->display('admin/utilisateur/details.html.twig', [
            'data' => $data
        ]);
    }
}
