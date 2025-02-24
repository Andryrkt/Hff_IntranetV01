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
use App\Entity\inventaire\InventaireDetailSearch;
use App\Form\inventaire\InventaireDetailSearchType;
use App\Form\inventaire\InventaireSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\VarDumper\Cloner\Data;
use TCPDF;

class InventaireController extends Controller
{
    use FormatageTrait;
    use Transformation;
    private InventaireModel $inventaireModel;
    private InventaireSearch $inventaireSearch;
    private InventaireDetailSearch $inventaireDetailSearch;

    public function __construct()
    {
        parent::__construct();
        $this->inventaireModel = new InventaireModel();
        $this->inventaireSearch = new InventaireSearch();
        $this->inventaireDetailSearch = new InventaireDetailSearch();
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
    public function inventaireDetail($numinv, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        $form = self::$validator->createBuilder(
            InventaireDetailSearchType::class,
            $this->inventaireDetailSearch,
            [
                'method' => 'GET'
            ]
        )->getForm();
        $form->handleRequest($request);

        $criteria = $this->inventaireDetailSearch;
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria =  $form->getdata();
        }
        $criteriaTAb = [];
        //transformer l'objet InventaireDetailSearch en tableau
        $criteriaTAb = $criteria->toArray();
        //recupères les données du criteria dans une session nommé inventaire_detail_search_criteria
        $this->sessionService->set('inventaire_detail_search_criteria', $criteriaTAb);

        $countSequence = $this->inventaireModel->countSequenceInvent($numinv);
        $dataDetail = $this->dataDetail($countSequence, $numinv);
        // dump($dataDetail);
        self::$twig->display('inventaire/inventaireDetail.html.twig', [
            'form' => $form->createView(),
            'data' => $dataDetail
        ]);
    }

    /**
     * @Route("/export_excel_liste_inventaire", name = "export_liste_inventaire")
     */
    public function exportExcelListe()
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

    /**
     * @Route("/export_excel_liste_inventaire_detail/{numinv}", name = "export_liste_inventaire_detail")
     */
    public function exportExcelDetail($numinv)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        $countSequence = $this->inventaireModel->countSequenceInvent($numinv);
        $data = $this->dataDetail($countSequence, $numinv);
        // dd($data);
        $header = [
            'numinv' => 'Numéro',
            'cst' => 'CST',
            'refp' => 'Reférence',
            'desi' => 'Description',
            'casier' => 'Casier',
            'stock_theo' => 'Qté théorique',
            'qte_comptee_1' => 'Cpt 1',
            'qte_comptee_2' => 'Cpt 2',
            'qte_comptee_3' => 'Cpt 3',
            'ecart' => 'Ecart',
            'pourcentage_nbr_ecart' => '% nbr écart',
            'pmp' => 'PMP',
            'montant_inventaire' => 'Mont. Inventaire',
            'montant_ajuste' => 'Mont. Ajusté',
        ];

        array_unshift($data, $header);

        $this->exportDonneesExcel($data);
    }
    /**
     * @Route("/export_pdf_liste_inventaire_detail/{numinv}", name = "export_pdf_liste_inventaire_detail")
     */
    public function exportPdfListe($numinv)
    {
        // Vérification si l'utilisateur est connecté
        $this->verifierSessionUtilisateur();
        $countSequence = $this->inventaireModel->countSequenceInvent($numinv);
        $data = $this->dataDetail($countSequence, $numinv);
        // dd($data);
        // Génération du PDF
        $this->exportDonneesPdf($data);
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
                    'nbr_ref' => $listInvent[$i]['nbre_ref'],
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

    public function dataDetail($countSequence, $numinv)
    {
        $criteriaTab = $this->sessionService->get('inventaire_detail_search_criteria');
        $numinvCriteria = ($criteriaTab['numinv'] === "" || $criteriaTab['numinv'] === null) ? $numinv : $criteriaTab['numinv'];

        if ($numinv !== $numinvCriteria) {
            $this->redirectToRoute('detail_inventaire', ['numinv' => $numinvCriteria]);
        }

        $data = [];
        $detailInvent = $this->inventaireModel->inventaireDetail($numinv);
        // dump($detailInvent);
        for ($j = 0; $j < count($detailInvent); $j++) {
            $data[] = [
                "numinv" => $numinv,
                "cst" => $detailInvent[$j]["cst"],
                "refp" => $detailInvent[$j]["refp"],
                "desi" => $detailInvent[$j]["desi"],
                "casier" => $detailInvent[$j]["casier"],
                "stock_theo" => $detailInvent[$j]["stock_theo"],
                "qte_comptee_1" => 0,
                "qte_comptee_2" => 0,
                "qte_comptee_3" => 0,
                "ecart" => $detailInvent[$j]["ecart"],
                "pourcentage_nbr_ecart" => $detailInvent[$j]["pourcentage_nbr_ecart"],
                "pmp" => $this->formatNumber($detailInvent[$j]["pmp"]),
                "montant_inventaire" => $this->formatNumber($detailInvent[$j]["montant_inventaire"]),
                "montant_ajuste" => $this->formatNumber($detailInvent[$j]["montant_ajuste"]),
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


    public function exportDonneesPdf($data)
    {
        $pdf = new TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->setPageOrientation('L');
        $pdf->SetAuthor('Votre Nom');
        $pdf->SetTitle('Écart sur inventaire');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 10);
        $pdf->AddPage();

        // Ajout du logo
        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/Hffintranet/Views/assets/logoHff.jpg';
        $pdf->Image($logoPath, 10, 10, 50);
        $pdf->Ln(15);

        // Titre principal
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Écart sur inventaire', 0, 1, 'C');
        $pdf->Ln(2);

        // Sous-titre
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 10, 'INVENTAIRE N°:'.$data[0]['numinv'], 0, 1, 'C');
        $pdf->Cell(0, 10, 'du : 13/02/2025', 0, 1, 'C');
        $pdf->Ln(5);

        // Création du tableau
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->Cell(25, 7, 'CST', 1, 0, 'C', 1);
        $pdf->Cell(35, 7, 'Référence', 1, 0, 'C', 1);
        $pdf->Cell(50, 7, 'Description', 1, 0, 'C', 1);
        $pdf->Cell(25, 7, 'Casier', 1, 0, 'C', 1);
        $pdf->Cell(20, 7, 'Qté théorique', 1, 0, 'C', 1);
        $pdf->Cell(20, 7, 'Cpt 1', 1, 0, 'C', 1);
        $pdf->Cell(20, 7, 'Cpt 2', 1, 0, 'C', 1);
        $pdf->Cell(20, 7, 'Cpt 3', 1, 0, 'C', 1);
        $pdf->Cell(20, 7, 'Écart', 1, 0, 'C', 1);
        $pdf->Cell(30, 7, 'P.M.P', 1, 0, 'C', 1);
        $pdf->Cell(30, 7, 'Montant écart', 1, 1, 'C', 1);

        // Remplissage du tableau avec les données
        $pdf->SetFont('helvetica', '', 10);
        $fill = 0;
        foreach ($data as $row) {
            $pdf->Cell(25, 6, $row['cst'], 1, 0, 'C', $fill);
            $pdf->Cell(35, 6, $row['refp'], 1, 0, 'C', $fill);
            $pdf->Cell(50, 6, $row['desi'], 1, 0, 'C', $fill);
            $pdf->Cell(25, 6, $row['casier'], 1, 0, 'C', $fill);
            $pdf->Cell(20, 6, $row['stock_theo'], 1, 0, 'C', $fill);
            $pdf->Cell(20, 6, $row['qte_comptee_1'], 1, 0, 'C', $fill);
            $pdf->Cell(20, 6, $row['qte_comptee_2'], 1, 0, 'C', $fill);
            $pdf->Cell(20, 6, $row['qte_comptee_3'], 1, 0, 'C', $fill);
            $pdf->Cell(20, 6, $row['ecart'], 1, 0, 'C', $fill);
            $pdf->Cell(30, 6, str_replace('.',' ',$row['pmp']), 1, 0, 'R', $fill);
            $pdf->Cell(30, 6, str_replace('.',' ',$row['montant_ajuste']), 1, 1, 'R', $fill);
            $fill = !$fill; // Alterner les couleurs des lignes
        }

        // Affichage du total
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(205, 7, 'Total écart', 1, 0, 'R', 1);
        $pdf->Cell(30, 7, str_replace('.',' ',array_sum(array_column($data,'montant_ajuste'))), 1, 1, 'R', 1);

        // Sortie du fichier PDF
        $pdf->Output('ecart_inventaire.pdf', 'D');
    }
}
