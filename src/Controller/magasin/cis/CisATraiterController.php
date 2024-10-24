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

        //enregistrer les critère de recherche dans la session
        $this->sessionService->set('cis_a_traiter_search_criteria', $criteria);

        self::$twig->display('magasin/cis/listATraiter.html.twig', [
            'data' => $data,
            'form' =>$form->createView()
        ]);
    }
    
    /**
     * @Route("/export-excel-a-traiter-cis", name="export_excel_a_traiter_cis")
     */
    public function exportExcel()
    {
        $cisATraiterModel = new CisATraiterModel();

        //recupères les critère dans la session 
        $criteria = $this->sessionService->get('cis_a_traiter_search_criteria', []);

        $entities = $cisATraiterModel->listOrATraiter($criteria);

        // Convertir les entités en tableau de données
        $data = [];
        $data[] = ['N° DIT', 'N° CIS', 'Date CIS', 'Ag/Serv Travaux', 'N° Or', 'Date Or', "Ag/Serv Débiteur / client", 'N° Intv', 'N° lig', 'Cst', 'Réf.', 'Désignations', 'Qté dem']; 
        foreach ($entities as $entity) {
            $data[] = [
                $entity['numdit'],
                $entity['numcis'],
                $entity['datecis'],
                $entity['agenceservicetravaux'],
                $entity['numor'],
                $entity['dateor'],
                $entity['agenceservicedebiteur'],
                $entity['nitv'],
                $entity['numligne'],
                $entity['cst'],
                $entity['ref'],
                $entity['designations'],
                $entity['qte_dem']
            ];
        }

         $this->excelService->createSpreadsheet($data);
    }
}