<?php

use Symfony\Component\HttpFoundation\Response;

namespace App\Controller\dit;

use App\Controller\Controller;
use App\Form\dit\NatemaDitType;
use App\Entity\dit\DemandeIntervention;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\BaseController;

class NatemaDitController extends BaseController
{
    public function index()
    {
        $data = $this->getEntityManager()->getRepository(DemandeIntervention::class)->findBy([], ['id' => 'DESC']);


        return $this->render('natemadit/list.html.twig', [
            'data' => $data
        ]);
    }

    /**
     * @Route("/natemadit/new", name="natemadit_new")
     *
     * @param Request $request
     * @return void
     */
    public function new(Request $request)
    {

        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $form = $this->getFormFactory()->createBuilder(NatemaDitType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateur = $form->getData();

            $selectedApplications = $form->get('applications')->getData();

            foreach ($selectedApplications as $application) {
                $utilisateur->addApplication($application);
            }


            $this->getEntityManager()->persist($utilisateur);
            $this->getEntityManager()->flush();


            $this->redirectToRoute("natemadit_index");
        }

        return $this->render('natemadit/new.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
