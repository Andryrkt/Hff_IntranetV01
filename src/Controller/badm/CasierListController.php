<?php

namespace App\Controller\badm;

use App\Controller\Controller;
use App\Entity\cas\CasierValider;
use App\Form\cas\CasierSearchType;
use App\Controller\Traits\Transformation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CasierListController extends Controller
{

    use Transformation;
    
/**
 * @Route("/listCasier", name="liste_affichageListeCasier")
 */
    public function AffichageListeCasier(Request $request)
    {   
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        
        $form = self::$validator->createBuilder(CasierSearchType::class, null, [
            'method' => 'GET'
        ])->getForm();

        $form->handleRequest($request);

        $empty = false;
        $criteria = [];
        if($form->isSubmitted() && $form->isValid()) {
           $criteria = $form->getData();
        } 

        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        $paginationData = self::$em->getRepository(CasierValider::class)->findPaginatedAndFiltered($page, $limit, $criteria);


        if(empty($paginationData['data'])){
            $empty = true;
        }

        self::$twig->display(
            'badm/casier/listCasier.html.twig',
            [
                'casier' => $paginationData['data'],
                'form' => $form->createView(),
                'criteria' => $criteria,
                'currentPage' => $paginationData['currentPage'],
                'lastPage' => $paginationData['lastPage'],
                'resultat' => $paginationData['totalItems'],
                'empty' => $empty,
            ]
        );
    }

}
