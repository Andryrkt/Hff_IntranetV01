<?php

namespace App\Controller\da;

use App\Controller\Controller;
use App\Form\da\CdeFrnListType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ListCdeFrnController extends Controller
{
    /** 
     * @Route(path="/demande-appro/liste-commande-fournisseurs", name="list_cde_frn") 
     **/
    public function listCdeFrn(Request $request)
    {
        $data = [];

        $form = self::$validator->createBuilder(CdeFrnListType::class)->getForm();

        $form->handleRequest($request);
        $criteria = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }

        self::$twig->display('da/list-cde-frn.html.twig', [
            'data' => $data,
            'form' => $form->createView(),
        ]);
    }
}
