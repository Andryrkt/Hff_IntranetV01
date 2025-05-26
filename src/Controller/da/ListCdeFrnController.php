<?php

namespace App\Controller\da;

use App\Controller\Controller;
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
        self::$twig->display('da/list-cde-frn.html.twig', [
            'data' => $data
        ]);
    }
}
