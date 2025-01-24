<?php

namespace App\Controller\admin;


use App\Entity\admin\Agence;
use App\Controller\Controller;
use App\Form\admin\AgenceType;
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
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $data = self::$em->getRepository(Agence::class)->findBy([], ['id'=>'DESC']);

        self::$twig->display('admin/agence/list.html.twig', 
        [
            'data' => $data
        ]);
    }

    /**
         * @Route("/admin/agence/new", name="agence_new")
         */
        public function new(Request $request)
        {
            //verification si user connecter
            $this->verifierSessionUtilisateur();

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
    //verification si user connecter
    $this->verifierSessionUtilisateur();
    
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
    ]);
}

}