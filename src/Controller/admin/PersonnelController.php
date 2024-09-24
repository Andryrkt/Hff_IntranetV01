<?php

namespace App\Controller\admin;

use App\Controller\Controller;
use App\Entity\admin\Personnel;
use App\Form\admin\PersonnelType;
use App\Form\admin\PersonnelSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PersonnelController extends Controller
{
    /**
     * @Route("/admin/personnel", name="personnel_index")
     *
     * @return void
     */
    public function index(Request $request)
    {

    $data = self::$em->getRepository(Personnel::class)->findBy([], ['id'=>'DESC']);

    $criteria = [
        
        'matricule' => $request->query->get('matricule', ''),
        
    ];

    $form = self::$validator->createBuilder(PersonnelSearchType::class, null, [ 'method' => 'GET'])->getForm();

    $form->handleRequest($request);


    if($form->isSubmitted() && $form->isValid()) {
        $criteria['matricule'] = $form->get('matricule')->getData();
    } 

    $page = $request->query->getInt('page', 1);
        $limit = 10;

        $repository= self::$em->getRepository(Personnel::class);
        $data = $repository->findPaginatedAndFiltered($page, $limit, $criteria);
        $totalBadms = $repository->countFiltered($criteria);

        $totalPages = ceil($totalBadms / $limit);

    self::$twig->display('admin/Personnel/list.html.twig', [
        'form' => $form->createView(),
        'data' => $data,
        'currentPage' => $page,
        'totalPages' =>$totalPages,
        'criteria' => $criteria,
        'resultat' => $totalBadms,
    ]);
    }

     /**
         * @Route("/admin/personnel/new", name="personnnel_new")
         */
        public function new(Request $request)
        {    
            $form = self::$validator->createBuilder(PersonnelType::class)->getForm();
    
            $form->handleRequest($request);
    
            if($form->isSubmitted() && $form->isValid())
            {
                $personnel= $form->getData();
                self::$em->persist($personnel);
    
                self::$em->flush();
                $this->redirectToRoute("personnel_index");
            }
    
            self::$twig->display('admin/Personnel/new.html.twig', 
            [
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