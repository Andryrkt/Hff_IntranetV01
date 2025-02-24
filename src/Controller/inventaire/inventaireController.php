<?php

namespace App\Controller\inventaire;

use DateTime;
use App\Controller\Controller;
use App\Controller\Traits\FormatageTrait;
use App\Controller\Traits\Transformation;
use App\Model\inventaire\InventaireModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Entity\inventaire\InventaireSearch;
use App\Form\inventaire\InventaireSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class InventaireController extends Controller
{
    use FormatageTrait;
    use Transformation;
    private InventaireModel $inventaireModel;
    private InventaireSearch $inventaireSearch;

    public function __construct()
    {
        parent::__construct();
        $this->inventaireModel = new InventaireModel();
        $this->inventaireSearch = new InventaireSearch();
    }

    /**
     * @Route("/inventaire", name = "liste_inventaire")
     * 
     * @return void
     */
    public function listeInventaire(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();

        $form = self::$validator->createBuilder(
            InventaireSearchType::class,
            $this->inventaireSearch,
            [
                'method' => 'GET'
            ]
        )->getForm();

        $form->handleRequest($request);
        //initialisation criteria
        $criteria = $this->inventaireSearch;

        if ($form->isSubmitted() && $form->isValid()) {
            $criteria =  $form->getdata();
        }
        $criteriaTAb = [];
        //transformer l'objet ditSearch en tableau
        $criteriaTAb = $criteria->toArray();
        //recupères les données du criteria dans une session nommé dit_serch_criteria
        $this->sessionService->set('inventaire_search_criteria', $criteriaTAb);

        $data  = [];
        if ($request->query->get('action') !== 'oui') {
            $listInvent = $this->inventaireModel->listeInventaire($criteria);
            $data = $this->recupDataList($listInvent);
            // dump($data);
        }
        self::$twig->display('inventaire/inventaire.html.twig', [
            'form' => $form->createView(),
            'data' => $data
        ]);
    }
    /**
     * @Route("/detailInventaire/{numinv}",name = "detail_inventaire")
     */
    public function inventaireDetail($numinv)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        $countSequence = $this->inventaireModel->countSequenceInvent($numinv);
        $dataDetail = $this->dataDetail($countSequence, $numinv);
        // dump($dataDetail);
        self::$twig->display('inventaire/inventaireDetail.html.twig', [
            'data' => $dataDetail
        ]);
        
    }
    public function dataDetail($countSequence, $numinv)
    {
        $data = [];
        $detailInvent = $this->inventaireModel->inventaireDetail($numinv);
        for ($j = 0; $j < count($detailInvent); $j++) {
            $data[] = [
                "num" => $numinv,
                "cst" => $detailInvent[$j]["cst"],
                "refp" => $detailInvent[$j]["refp"],
                "desi" => $detailInvent[$j]["desi"],
                "casier" => $detailInvent[$j]["casier"],
                "stock_theo" => $detailInvent[$j]["stock_theo"],
                "ecart" => $detailInvent[$j]["ecart"],
                "pourcentage_nbr_ecart" => $detailInvent[$j]["pourcentage_nbr_ecart"],
                "pmp" => $this->formatNumber($detailInvent[$j]["pmp"]),
                "montant_inventaire" => $this->formatNumber($detailInvent[$j]["montant_inventaire"]),
                "montant_ajuste" => $this->formatNumber($detailInvent[$j]["montant_ajuste"]),
                "qte_comptee_1" => 0,
                "qte_comptee_2" => 0,
                "qte_comptee_3" => 0,
            ];
            if (!empty($countSequence)) {
                for ($i = 0; $i < count($countSequence); $i++) {
                    $qteCompte =  $this->inventaireModel->qteCompte($numinv, $countSequence[$i]['nb_sequence'], $detailInvent[$j]['refp']);
                    $data[$j]["qte_comptee_" . ($i + 1)] = $qteCompte[0]['qte_comptee'];
                }
            }
        }
        return $data;
    }
    /**
     * @Route("/export_excel_liste_inventaire", name = "export_liste_inventaire")
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        $criteriaTAb = $this->sessionService->get('inventaire_search_criteria');
        $this->inventaireSearch->arrayToObjet($criteriaTAb);
        $listInvent = $this->inventaireModel->listeInventaire($this->inventaireSearch);
        $data = $this->recupDataList($listInvent);
        $header = [
            'numero' => 'Numéro',
            'description' => 'Description',
            'ouvert' => 'Ouvert le',
            'nbr_casier' => ' Nbr casier',
            'nbr_ref' => 'Nbr Ref',
            'qte_comptee' => 'Qté comptée',
            'statut' => 'Statut',
            'montant' => 'Montant',
            'nbre_ref_ecarts_positif' => 'Nbr Ref écart > 0',
            'nbre_ref_ecarts_negatifs' => 'Nbr Ref écart < 0',
            'total_nbre_ref_ecarts' => 'Nbr Ref en écart',
            'pourcentage_ref_avec_ecart' => '% Ref avec écart',
            'montant_ecart' => 'Mont. écart',
            'pourcentage_ecart' => '% écart',


        ];

        array_unshift($data, $header);

        $this->exportDonneesExcel($data);
    }
    public function recupDataList($listInvent)
    {
        $data = [];
        if (!empty($listInvent)) {
            for ($i = 0; $i < count($listInvent); $i++) {
                $numIntvMax = $this->inventaireModel->maxNumInv($listInvent[$i]['numero_inv']);
                $invLigne = $this->inventaireModel->inventaireLigneEC($numIntvMax[0]['numinvmax']);
                $data[] = [
                    'numero' => $listInvent[$i]['numero_inv'],
                    'description' => $listInvent[$i]['description'],
                    'ouvert' => (new DateTime($listInvent[$i]['ouvert_le']))->format('d/m/Y'),
                    'nbr_casier' => $listInvent[$i]['nbre_casier'],
                    'nbr_ref' =>$listInvent[$i]['nbre_ref'],
                    'qte_comptee' => $this->formatNumber($listInvent[$i]['qte_comptee']),
                    'statut' => $listInvent[$i]['statut'],
                    'montant' => $this->formatNumber($listInvent[$i]['montant']),
                    'nbre_ref_ecarts_positif' => $invLigne[0]['nbre_ref_ecarts_positif'],
                    'nbre_ref_ecarts_negatifs' => $invLigne[0]['nbre_ref_ecarts_negatifs'],
                    'total_nbre_ref_ecarts' => $invLigne[0]['total_nbre_ref_ecarts'],
                    'pourcentage_ref_avec_ecart' => $invLigne[0]['pourcentage_ref_avec_ecart'],
                    'montant_ecart' => $this->formatNumber($invLigne[0]['montant_ecart']),
                    'pourcentage_ecart' => $invLigne[0]['pourcentage_ecart']
                ];
            }
        }
        return $data;
    }


    private function exportDonneesExcel($data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Ajout des données
        $rowIndex = 1;
        foreach ($data as $row) {
            $sheet->fromArray([$row], null, "A$rowIndex");
            $rowIndex++;
        }

        // Téléchargement du fichier
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="export.xlsx"');
        $writer->save('php://output');
        exit();
    }
}
