<?php

namespace App\Controller\dw;

use App\Controller\Controller;
use App\Entity\dw\DwProcessusProcedure;
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

        self::$twig->display('dw/documentationInterne.html.twig', [
            'data' => $data
        ]);
    }
}
