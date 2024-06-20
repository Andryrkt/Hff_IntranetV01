<?php

namespace App\Controller\admin;


use App\Entity\Role;
use App\Controller\Controller;
use App\Entity\Permission;
use App\Entity\Societte;
use App\Form\RoleType;
use App\Form\SocietteType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SocietteController extends Controller
{
    /**
     * @Route("/admin/societte", name="societte_index")
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

    $data = self::$em->getRepository(Societte::class)->findBy([], ['id'=>'DESC']);


    self::$twig->display('admin/societte/list.html.twig', [
        'infoUserCours' => $infoUserCours,
        'boolean' => $boolean,
        'data' => $data
    ]);
    }

    /**
         * @Route("/admin/societte/new", name="societte_new")
         */
        public function new(Request $request)
        {
            $this->SessionStart();
            $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
            $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
            $text = file_get_contents($fichier);
            $boolean = strpos($text, $_SESSION['user']);
    
            $form = self::$validator->createBuilder(SocietteType::class)->getForm();
    
            $form->handleRequest($request);
    
            if($form->isSubmitted() && $form->isValid())
            {
                $societte= $form->getData();
                

                self::$em->persist($societte);
                self::$em->flush();

                $this->redirectToRoute("societte_index");
            }
    
            self::$twig->display('admin/societte/new.html.twig', [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'form' => $form->createView()
            ]);
        }


                /**
     * @Route("/admin/societte/edit/{id}", name="societte_update")
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

        $user = self::$em->getRepository(Societte::class)->find($id);
        
        $form = self::$validator->createBuilder(SocietteType::class, $user)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {

            self::$em->flush();
            $this->redirectToRoute("societte_index");
            
        }

        self::$twig->display('admin/societte/edit.html.twig', [
            'form' => $form->createView(),
            'infoUserCours' => $infoUserCours,
            'boolean' => $boolean
        ]);

    }

    /**
    * @Route("/admin/societte/delete/{id}", name="societte_delete")
    *
    * @return void
    */
    public function delete($id)
    {
        $societte = self::$em->getRepository(Societte::class)->find($id);

        if ($societte) {
            $typeReparations = $societte->getTypeReparations();
            foreach ($typeReparations as $typeReparation) {
                $societte->removeTypeReparation($typeReparation);
                self::$em->persist($typeReparation); // Persist the permission to register the removal
            }

            // Clear the collection to ensure Doctrine updates the join table
            $societte->getTypeReparations()->clear();

            // Flush the entity manager to ensure the removal of the join table entries
            self::$em->flush();
        
                self::$em->remove($societte);
                self::$em->flush();
        }
        
        $this->redirectToRoute("societte_index");
    }
}