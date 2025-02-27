<?php

namespace App\Controller\admin\user;


use App\Controller\Controller;
use App\Entity\ProfilUserEntity;
use App\Entity\admin\utilisateur\ProfilUser;
use Symfony\Component\HttpFoundation\Request;
use App\Form\admin\utilisateur\ProfilUserType;
use Symfony\Component\Routing\Annotation\Route;


class ProfilUserController extends Controller
{

    /**
     * Undocumented function
     *  @Route("/admin/user", name="user_index")
     * @param Request $request
     * @return void
     */
    public function index(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $form = self::$validator->createBuilder(ProfilUserType::class)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $profilUser = $form->getData();
            self::$em->persist($profilUser);

            // Sauvegarder l'entité dans la base de données
            self::$em->flush();
            $this->redirectToRoute("user_list");

            //$this->profilUser->insertData($this->nomTable, $profilUser);

        }

        self::$twig->display(
            'admin/user/profilUser.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/user/list", name="user_list")
     *
     * @return void
     */
    public function list()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $data =  self::$em->getRepository(ProfilUser::class)->findBy([], ['id' => 'DESC']);

        self::$twig->display(
            'admin/user/listProfilUser.html.twig',
            [
                'data' => $data
            ]
        );
    }

    /**
     * @Route("/admin/user/edit/{id}", name="user_update")
     *
     * @return void
     */
    public function edit(Request $request, $id)
    {

        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $user = self::$em->getRepository(ProfilUser::class)->find($id);


        //$user = $this->profilUser->find($this->nomTable, "ID_Profil = {$id}", ProfilUser::class);



        $form = self::$validator->createBuilder(ProfilUserType::class, $user)->getForm();


        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            self::$em->flush();
            $this->redirectToRoute("user_list");
            // $profilUser = $form->getData();
            //dd($user);
            // $this->profilUser->update($this->nomTable, $profilUser, "ID_Profil = {$id}");

        }

        self::$twig->display(
            'admin/user/editProfilUser.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/user/delete/{id}", name="user_delete")
     *
     * @return void
     */
    public function delete($id)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $user = self::$em->getRepository(ProfilUser::class)->find($id);

        self::$em->remove($user);
        self::$em->flush();

        // $condition = "ID_Profil = {$id}";
        // $this->profilUser->delete($this->nomTable, $condition);
        $this->redirectToRoute("user_list");
    }
}
