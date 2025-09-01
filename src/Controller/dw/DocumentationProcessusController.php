<?php

namespace App\Controller\dw;

use App\Controller\Controller;
use App\Entity\dw\DocInternesearch;
use App\Entity\dw\DwProcessusProcedure;
use App\Form\dw\DocInterneSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\BaseController;

/**
 * @Route("/documentation")
 */
class DocumentationProcessusController extends BaseController
{
    /**
     * @Route("/documentation-interne", name="documentation_interne")
     */
    public function documentationInterne(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $docInterneSearch = new DocInternesearch;

        $form = $this->getFormFactory()->createBuilder(DocInterneSearchType::class, $docInterneSearch, [
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

        $paginationData = $this->getEntityManager()->getRepository(DwProcessusProcedure::class)->findPaginatedAndFiltered($page, $limit, $docInterneSearch);

        return $this->render('dw/documentationInterne.html.twig', [
            'form' => $form->createView(),
            'data' => $paginationData['data'],
            'currentPage' => $paginationData['currentPage'],
            'totalPages'    => $paginationData['lastPage'],
            'resultat'    => $paginationData['totalItems'],
            'criteria'    => $criteria,
        ]);
    }
}
