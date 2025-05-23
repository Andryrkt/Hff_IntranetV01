<?php

namespace App\Controller\dw;

use App\Controller\Controller;
use App\Entity\dw\DocInternesearch;
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
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $docInterneSearch = new DocInternesearch;

        $form = self::$validator->createBuilder(DocInterneSearchType::class, $docInterneSearch, [
            'method' => 'GET'
        ])->getForm();
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $docInterneSearch = $form->getData();
        }

        $criteria = [];
        $criteria = $docInterneSearch->toArray();
        $page = $request->query->getInt('page', 1);
        $limit = 10;
        
        $paginationData = self::$em->getRepository(DwProcessusProcedure::class)->findPaginatedAndFiltered($page, $limit, $docInterneSearch);

        self::$twig->display('dw/documentationInterne.html.twig', [
            'form' => $form->createView(),
            'data' => $paginationData['data'],
            'currentPage' => $paginationData['currentPage'],
            'totalPages'    => $paginationData['lastPage'],
            'resultat'    => $paginationData['totalItems'],
            'criteria'    => $criteria,
        ]);
    }
}
