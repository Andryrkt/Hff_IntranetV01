<?php

namespace App\Controller\admin;

use App\Entity\User;
use App\Entity\Agence;
use App\Form\UserType;
use App\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class UserController extends Controller
{

     /**
     * @Route("/admin/utilisateur", name="utilisateur_index")
     *
     * @return void
     */
    public function index()
    {
        $this->SessionStart();
    $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
    $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
    $text = file_get_contents($fichier);
    $boolean = strpos($text, $_SESSION['user']);

    $data = self::$em->getRepository(User::class)->findBy([], ['id'=>'DESC']);

   

    self::$twig->display('admin/utilisateur/list.html.twig', [
        'infoUserCours' => $infoUserCours,
        'boolean' => $boolean,
        'data' => $data
    ]);
    }

    /**
     * @Route("/admin/utilisateur/new", name="utilisateur_new")
     */
    public function new(Request $request)
    {
        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);

        $user = new User();
        $user->setAgences(self::$em->getRepository(Agence::class)->find(1));

        $form = self::$validator->createBuilder(UserType::class, $user)->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $utilisateur= $form->getData(); 
                
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

        self::$twig->display('admin/utilisateur/new.html.twig', [
            'infoUserCours' => $infoUserCours,
            'boolean' => $boolean,
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

        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);

        $user = self::$em->getRepository(User::class)->find($id);
      
        $form = self::$validator->createBuilder(UserType::class, $user)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            self::$em->flush();
            $this->redirectToRoute("utilisateur_index");
            
        }

        self::$twig->display('admin/utilisateur/edit.html.twig', [
            'form' => $form->createView(),
            'infoUserCours' => $infoUserCours,
            'boolean' => $boolean
        ]);

    }

   

}