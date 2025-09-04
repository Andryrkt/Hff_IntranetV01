<?php


namespace App\Controller\admin;


use App\Entity\admin\Agence;
use App\Controller\Controller;
use App\Form\admin\AgenceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AgenceController extends Controller
{
    /**
     * @Route("/admin/agence/list-agence", name="agence_index")
     *
     * @return void
     */
    public function index()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $data = $this->getEntityManager()->getRepository(Agence::class)->findBy([], ['id' => 'DESC']);

        return $this->render(
            'admin/agence/list.html.twig',
            [
                'data' => $data
            ]
        );
    }

    /**
     * @Route("/admin/agence/new", name="agence_new")
     */
    public function new(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $form = $this->getFormFactory()->createBuilder(AgenceType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $role = $form->getData();


            $selectedService = $form->get('services')->getData();

            foreach ($selectedService as $permission) {
                $role->addService($permission);
            }

            $this->getEntityManager()->persist($role);
            $this->getEntityManager()->flush();

            $this->redirectToRoute("agence_index");
        }

        return $this->render('admin/agence/new.html.twig', [
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

        $agence = $this->getEntityManager()->getRepository(Agence::class)->find($id);

        $form = $this->getFormFactory()->createBuilder(AgenceType::class, $agence)->getForm();

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getEntityManager()->flush();
            return $this->redirectToRoute("agence_index");
        }

        // Debugging: Vérifiez que createView() ne retourne pas null
        $formView = $form->createView();
        if ($formView === null) {
            throw new \Exception('FormView is null');
        }

        return $this->render('admin/agence/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
