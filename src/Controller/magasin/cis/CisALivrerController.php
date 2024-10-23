<?php

namespace App\Controller\magasin\cis;

use App\Controller\Controller;
use App\Model\magasin\cis\CisALivrerModel;
use App\Form\magasin\cis\ALivrerSearchtype;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\magasin\cis\ALivrerTrait;

class CisALivrerController extends Controller
{
    use ALivrerTrait;

    /**
     * @Route("/cis-liste-a-livrer", name="cis_liste_a_livrer")
     */
    public function listCisALivrer(Request $request)
    {
        $cisATraiterModel = new CisALivrerModel();

        $agenceUser = $this->agenceUser(self::$em);

        $form = self::$validator->createBuilder(ALivrerSearchtype::class, ['agenceUser' => $agenceUser], [
            'method' => 'GET'
        ])->getForm();



        $form->handleRequest($request);
        $criteria = [
            "agenceUser" => $agenceUser
        ];
        if($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        } 


        $data = $cisATraiterModel->listOrALivrer($criteria);

        self::$twig->display('magasin/cis/listALivrer.html.twig', [
            'data' => $data,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/export-excel-cis-a-livrer", name="export_excel_cis_a_livrer")
     */
    public function exportExcel()
    {}

    
}