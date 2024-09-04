<?php

namespace App\Controller\admin;

use App\Controller\Controller;
use App\Form\AgenceServiceIriumType;
use App\Entity\admin\AgenceServiceIrium;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/admin/agServIrium")
 */
class AgenceServiceIriumController extends Controller
{
    /**
     * @Route("/", name="AgServIrium_index")
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

        $data = self::$em->getRepository(AgenceServiceIrium::class)->findBy([], ['id'=>'DESC']);

        self::$twig->display('admin/AgenceServiceIrium/list.html.twig', [
            'infoUserCours' => $infoUserCours,
            'boolean' => $boolean,
            'data' => $data
        ]);
    }

    /**
     * @Route("/new", name="AgServIrium_new")
     *
     * @return void
     */
    public function new(Request $request)
    {
        $this->SessionStart();
        $infoUserCours = $this->profilModel->getINfoAllUserCours($_SESSION['user']);
        $fichier = "../Hffintranet/Views/assets/AccessUserProfil_Param.txt";
        $text = file_get_contents($fichier);
        $boolean = strpos($text, $_SESSION['user']);

        $form = self::$validator->createBuilder(AgenceServiceIriumType::class)->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $AgenceServiceAutoriser = $form->getData();
            self::$em->persist($AgenceServiceAutoriser);

            self::$em->flush();
            $this->redirectToRoute("AgServIrium_index");
        }

        self::$twig->display('admin/AgenceServiceIrium/new.html.twig', [
            'infoUserCours' => $infoUserCours,
            'boolean' => $boolean,
            'form' => $form->createView()
        ]);
    }


        /**
 * @Route("/edit/{id}", name="AgServIrium_update")
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

    $user = self::$em->getRepository(AgenceServiceIrium::class)->find($id);
    
    $form = self::$validator->createBuilder(AgenceServiceIriumType::class, $user)->getForm();

    $form->handleRequest($request);

     // Vérifier si le formulaire est soumis et valide
    if ($form->isSubmitted() && $form->isValid()) {

        self::$em->flush();
        $this->redirectToRoute("AgServIrium_index");
        
    }

    self::$twig->display('admin/AgenceServiceIrium/edit.html.twig', [
        'form' => $form->createView(),
        'infoUserCours' => $infoUserCours,
        'boolean' => $boolean
    ]);

}

/**
* @Route("/delete/{id}", name="AgServIrium_delete")
*
* @return void
*/
public function delete($id)
{
    $user = self::$em->getRepository(AgenceServiceIrium::class)->find($id);

    self::$em->remove($user);
    self::$em->flush();
    
    $this->redirectToRoute("AgServIrium_index");
}
}