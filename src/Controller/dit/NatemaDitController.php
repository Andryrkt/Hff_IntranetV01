<?php

namespace App\Controller\dit;

use App\Controller\Controller;
use App\Entity\dit\DemandeIntervention;
use App\Form\dit\NatemaDitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class NatemaDitController extends Controller
{
    public function index()
    {
        $data = self::$em->getRepository(DemandeIntervention::class)->findBy([], ['id' => 'DESC']);


        self::$twig->display('natemadit/list.html.twig', [
            'data' => $data,
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

        $form = self::$validator->createBuilder(NatemaDitType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $utilisateur = $form->getData();

            $selectedApplications = $form->get('applications')->getData();

            foreach ($selectedApplications as $application) {
                $utilisateur->addApplication($application);
            }


            self::$em->persist($utilisateur);
            self::$em->flush();


            $this->redirectToRoute("natemadit_index");
        }

        self::$twig->display('natemadit/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
