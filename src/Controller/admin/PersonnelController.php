<?php

namespace App\Controller\admin;

use App\Entity\Personnel;
use App\Controller\Controller;
use App\Form\PersonnelType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PersonnelController extends Controller
{
    /**
     * @Route("/admin/personnel", name="personnel_index")
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

    $data = self::$em->getRepository(Personnel::class)->findBy([], ['id'=>'DESC']);


    self::$twig->display('admin/Personnel/list.html.twig', [
        'infoUserCours' => $infoUserCours,
        'boolean' => $boolean,
        'data' => $data
    ]);
    }

     /**
         * @Route("/admin/personnel/new", name="personnnel_new")
         */
        public function new(Request $request)
        {
            $this->SessionStart();
            $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
            $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
            $text = file_get_contents($fichier);
            $boolean = strpos($text, $_SESSION['user']);
    
            $form = self::$validator->createBuilder(PersonnelType::class)->getForm();
    
            $form->handleRequest($request);
    
            if($form->isSubmitted() && $form->isValid())
            {
                $personnel= $form->getData();
                self::$em->persist($personnel);
    
                self::$em->flush();
                $this->redirectToRoute("personnel_index");
            }
    
            self::$twig->display('admin/Personnel/new.html.twig', [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'form' => $form->createView()
            ]);
        }
    

       /**
 * @Route("/admin/personnel/edit/{id}", name="personnel_update")
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

    $user = self::$em->getRepository(Personnel::class)->find($id);
    
    $form = self::$validator->createBuilder(PersonnelType::class, $user)->getForm();

    $form->handleRequest($request);

     // Vérifier si le formulaire est soumis et valide
    if ($form->isSubmitted() && $form->isValid()) {

        self::$em->flush();
        $this->redirectToRoute("personnel_index");
        
    }

    self::$twig->display('admin/Personnel/edit.html.twig', [
        'form' => $form->createView(),
        'infoUserCours' => $infoUserCours,
        'boolean' => $boolean
    ]);

}

/**
* @Route("/admin/personnel/delete/{id}", name="personnel_delete")
*
* @return void
*/
public function delete($id)
{
    $user = self::$em->getRepository(Personnel::class)->find($id);

    self::$em->remove($user);
    self::$em->flush();
    
    $this->redirectToRoute("personnel_index");
}

/**
 * @Route("/admin/personnel/show/{id}", name="personnel_show")
 */
public function show($id)
{
    $this->SessionStart();
    $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
    $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
    $text = file_get_contents($fichier);
    $boolean = strpos($text, $_SESSION['user']);

    $user = self::$em->getRepository(Personnel::class)->find($id);

    self::$twig->display('admin/Personnel/show.html.twig', [
        
        'infoUserCours' => $infoUserCours,
        'boolean' => $boolean,
        'personnel' => $user
    ]);
}
}