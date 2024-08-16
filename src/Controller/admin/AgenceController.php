<?php

namespace App\Controller\admin;


use App\Entity\Role;
use App\Controller\Controller;
use App\Entity\Agence;
use App\Entity\Permission;
use App\Form\AgenceType;
use App\Form\RoleType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AgenceController extends Controller
{
    /**
     * @Route("/admin/agence", name="agence_index")
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

    $data = self::$em->getRepository(Agence::class)->findBy([], ['id'=>'DESC']);


    self::$twig->display('admin/agence/list.html.twig', [
        'infoUserCours' => $infoUserCours,
        'boolean' => $boolean,
        'data' => $data
    ]);
    }

    /**
         * @Route("/admin/agence/new", name="agence_new")
         */
        public function new(Request $request)
        {
            $this->SessionStart();
            $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
            $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
            $text = file_get_contents($fichier);
            $boolean = strpos($text, $_SESSION['user']);
    
            $form = self::$validator->createBuilder(AgenceType::class)->getForm();
    
            $form->handleRequest($request);
    
            if($form->isSubmitted() && $form->isValid())
            {
                $role= $form->getData();
                

                $selectedService = $form->get('services')->getData();

                foreach ($selectedService as $permission) {
                    $role->addService($permission);
                }

                self::$em->persist($role);
                self::$em->flush();

                $this->redirectToRoute("agence_index");
            }
    
            self::$twig->display('admin/agence/new.html.twig', [
                'infoUserCours' => $infoUserCours,
                'boolean' => $boolean,
                'form' => $form->createView()
            ]);
        }


   /**
 * @Route("/admin/agence/edit/{id}", name="agence_update")
 *
 * @param Request $request
 * @param int $id
 * @return Response
 */
public function edit(Request $request, $id)
{
    $this->SessionStart();
    $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
    $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
    $text = file_get_contents($fichier);
    $boolean = strpos($text, $_SESSION['user']);

    $agence = self::$em->getRepository(Agence::class)->find($id);



    $form = self::$validator->createBuilder(AgenceType::class, $agence)->getForm();

 

    $form->handleRequest($request);

    // Vérifier si le formulaire est soumis et valide
    if ($form->isSubmitted() && $form->isValid()) {
        self::$em->flush();
        return $this->redirectToRoute("agence_index");
    }

    // Debugging: Vérifiez que createView() ne retourne pas null
    $formView = $form->createView();
    if ($formView === null) {
        throw new \Exception('FormView is null');
    }

    self::$twig->display('admin/agence/edit.html.twig', [
        'form' => $form->createView(),
        'infoUserCours' => $infoUserCours,
        'boolean' => $boolean
    ]);
}

}