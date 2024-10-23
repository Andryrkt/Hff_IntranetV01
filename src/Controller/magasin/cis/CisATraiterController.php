<?php

namespace App\Controller\magasin\cis;

use App\Controller\Controller;
use App\Model\magasin\cis\CisATraiterModel;
use App\Form\magasin\cis\ATraiterSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\magasin\cis\AtraiterTrait;

class CisATraiterController extends Controller
{
    use AtraiterTrait;
    /**
     * @Route("/cis-liste-a-traiter", name="cis_liste_a_traiter")
     */
    public function listCisATraiter(Request $request)
    {
        $cisATraiterModel = new CisATraiterModel();

        $agenceUser = $this->agenceUser(self::$em);

        $form = self::$validator->createBuilder(ATraiterSearchType::class, ['agenceUser' => $agenceUser], [
            'method' => 'GET'
        ])->getForm();

        $form->handleRequest($request);
        $criteria = [
            "agenceUser" => $agenceUser
        ];
    if($form->isSubmitted() && $form->isValid()) {
        $criteria = $form->getData();
    } 
        
        $data = $cisATraiterModel->listOrATraiter($criteria);

        self::$twig->display('magasin/cis/listATraiter.html.twig', [
            'data' => $data,
            'form' =>$form->createView()
        ]);
    }
}