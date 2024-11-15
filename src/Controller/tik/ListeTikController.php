<?php

namespace App\Controller\tik;

use App\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\tik\DemandeSupportInformatique;
use App\Entity\tik\TikSearch;
use App\Form\tik\TikSearchType;
use Symfony\Component\Routing\Annotation\Route;

class ListeTikController extends Controller
{
    /**
     * @Route("/tik-liste", name="liste_tik_index")
     */
    public function index(Request $request)
    {
        $tikSearch = new TikSearch();
        
        //création et initialisation du formulaire de la recherche
        $form = self::$validator->createBuilder(TikSearchType::class, $tikSearch, [
            'method' => 'GET',
        ])->getForm();

        $form->handleRequest($request);
        $criteria =[];
        if($form->isSubmitted() && $form->isValid())
        {
            // $criteria$form->getData());
        }
        // dd($tikSearch);
        //recupère le numero de page
        $page = $request->query->getInt('page', 1);
        //nombre de ligne par page
        $limit = 10;

        
        $paginationData = self::$em->getRepository(DemandeSupportInformatique::class)->findPaginatedAndFiltered($page, $limit, $tikSearch);
    
        self::$twig->display('tik/demandeSupportInformatique/list.html.twig', [
            'data' => $paginationData['data'],
            'currentPage' => $paginationData['currentPage'],
            'totalPages' =>$paginationData['lastPage'],
            'resultat' => $paginationData['totalItems'],
            'form' => $form->createView(),
            'criteria' => $criteria,
        ]);
    }
}