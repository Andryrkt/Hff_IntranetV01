<?php

namespace App\Controller\dw;

use App\Controller\Controller;
use App\Entity\dw\DwProcessusProcedure;
use App\Form\dw\DocInterneSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DocumentationProcessusController extends Controller
{
    /**
     * @Route("/documentation-interne", name="documentation_interne")
     */
    public function documentationInterne(Request $request)
    {
        $data = self::$em->getRepository(DwProcessusProcedure::class)->findAll();

        $form = self::$validator->createBuilder(DocInterneSearchType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {}

        self::$twig->display('dw/documentationInterne.html.twig', [
            'form' => $form->createView(),
            'data' => $data
        ]);
    }
}
