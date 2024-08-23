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
    private function transformIdEnObjetEntitySuperieur(array $data): array
    {
   
        $superieurs = [];
        foreach ($data as  $values) {
            
                foreach ($values->getSuperieurs() as  $value) {
                    if(empty($value)){
                        return $data;
                    } else {
                        $superieurs[] = self::$em->getRepository(user::class)->find($value);
                    }
                }
                $values->setSuperieurs($superieurs);
                $superieurs = [];
        }
        return $data;
    
    }
    

     /**
     * @Route("/admin/utilisateur", name="utilisateur_index")
     *
     * @return void
     */
    public function index()
    {

        $data = self::$em->getRepository(User::class)->findBy([], ['id'=>'DESC']);
        $data = $this->transformIdEnObjetEntitySuperieur($data);

        self::$twig->display('admin/utilisateur/list.html.twig', [
            'data' => $data
        ]);
    }

    /**
     * @Route("/admin/utilisateur/new", name="utilisateur_new")
     */
    public function new(Request $request)
    {
        $user = new User();

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

            // Récupérer les IDs des supérieurs depuis le formulaire
            $superieurEntities = $form->get('superieurs')->getData();
            
            $superieurIds = array_map(function($superieur) {
                return $superieur->getId();
            }, $superieurEntities);
           
            // Mettre à jour les supérieurs de l'utilisateur
            $user->setSuperieurs($superieurIds);
            self::$em->persist($utilisateur);
    
            self::$em->flush();
           

            $this->redirectToRoute("utilisateur_index");
        }

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


    $user = self::$em->getRepository(User::class)->find($id);
    // Conversion de l'utilisateur en objet s'il est en tableau
    $user = $this->arrayToObjet($user);
    

    $form = self::$validator->createBuilder(UserType::class, $user)->getForm();

    $form->handleRequest($request);

    // Vérifier si le formulaire est soumis et valide
    if ($form->isSubmitted() && $form->isValid()) {

        if ($user->getSuperieurs() === null) {
            $user->setSuperieurs([]);
        }
        // Récupérer les IDs des supérieurs depuis le formulaire
        $superieurEntities = $form->get('superieurs')->getData();
        $superieurIds = array_map(function($superieur) {
            return $superieur->getId();
        }, $superieurEntities);

        // Mettre à jour les supérieurs de l'utilisateur
        $user->setSuperieurs($superieurIds);

        self::$em->flush();
        return $this->redirectToRoute("utilisateur_index");
    }

    self::$twig->display('admin/utilisateur/edit.html.twig', [
        'form' => $form->createView(),
    ]);
}

/**
 * @Route("/admin/utilisateur/delete/{id}", name="utilisateur_delete")
 *
 * @return void
 */
public function delete(Request $request, $id)
{

    $user = self::$em->getRepository(User::class)->find($id);

    if ($user === null) {
        throw new \Exception('Utilisateur non trouvé');
    }

    // Supprimer les références ManyToMany
    foreach ($user->getPermissions() as $permission) {
        $user->removePermission($permission);
    }
    foreach ($user->getApplications() as $application) {
        $user->removeApplication($application);
    }
    foreach ($user->getSociettes() as $societte) {
        $user->removeSociette($societte);
    }
    foreach ($user->getRoles() as $role) {
        $user->removeRole($role);
    }
    foreach ($user->getAgencesAutorisees() as $agence) {
        $user->removeAgenceAutorise($agence);
    }
    foreach ($user->getServiceAutoriser() as $service) {
        $user->removeServiceAutoriser($service);
    }

    // Supprimer les références OneToMany
    foreach ($user->getCasiers() as $casier) {
        $user->removeCasier($casier);
    }

    // Supprimer les références ManyToOne
    $user->setPersonnels(null);
    $user->setFonction(null);
    $user->setAgenceServiceIrium(null);

    self::$em->flush();

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
    $data = self::$em->getRepository(User::class)->find($id);

    self::$twig->display('admin/utilisateur/details.html.twig', [
        'data' => $data
    ]);
}
}