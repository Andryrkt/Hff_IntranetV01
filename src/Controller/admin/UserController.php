<?php

namespace App\Controller\admin;

use App\Controller\Controller;
use App\Dto\admin\UserDTO;
use App\Entity\admin\utilisateur\Profil;
use App\Entity\admin\utilisateur\User;
use App\Factory\admin\UserFactory;
use App\Form\admin\utilisateur\UserType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends Controller
{
    private UserFactory $userFactory;

    public function __construct(UserFactory $userFactory)
    {
        $this->userFactory = $userFactory;
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
        $preparedData = $this->prepareDataForListDisplay($data);

        return $this->render('admin/utilisateur/list.html.twig', [
            'rows' => $preparedData
        ]);
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

        return $this->render('admin/utilisateur/details.html.twig', [
            'data' => $data
        ]);
    }

    /**
     * @Route("/admin/utilisateur/new", name="utilisateur_new")
     */
    public function new(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        $dto = new UserDTO();
        $form = $this->getFormFactory()->createBuilder(UserType::class, $dto)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateur = $this->userFactory->createFromDto($dto);

            $this->getEntityManager()->persist($utilisateur);
            $this->getEntityManager()->flush();

            $this->redirectToRoute("utilisateur_index");
        }

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
        $dto = $this->userFactory->createDTOFromUser($user);
        $form = $this->getFormFactory()->createBuilder(UserType::class, $dto)->getForm();
        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $this->userFactory->updateFromDTO($dto, $user);

            $this->getEntityManager()->flush();
            return $this->redirectToRoute("utilisateur_index");
        }

        return $this->render('admin/utilisateur/edit.html.twig', [
            'form' => $form->createView(),
        ]);
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
     * Fonction pour préparer les données avant de donner à twig
     * @param  User[] $dataUsers
     * @return array
     */
    private function prepareDataForListDisplay(array $dataUsers): array
    {
        $rows = [];
        $urlGenerator = $this->getUrlGenerator();

        foreach ($dataUsers as $user) {
            $id = $user->getId();
            $agenceServiceIrium = $user->getAgenceServiceIrium();
            $profils = $user->getProfils();

            $rows[] = [
                'username'   => $user->getNomUtilisateur(),
                'matricule'  => $user->getMatricule(),
                'email'      => $user->getMail(),
                'codeSage'   => $agenceServiceIrium->getServiceSagePaie(),
                'profils'    => $profils,
                'url_show'   => $urlGenerator->generate('utilisateur_show', ['id' => $id]),
                'url_edit'   => $urlGenerator->generate('utilisateur_update', ['id' => $id]),
                'url_delete' => $urlGenerator->generate('utilisateur_delete', ['id' => $id]),
            ];
        }

        return $rows;
    }
}
