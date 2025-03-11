<?php

namespace App\Controller\inventaire;

use TCPDF;
use DateTime;
use App\Controller\Controller;
use App\Controller\Traits\FormatageTrait;
use App\Controller\Traits\Transformation;
use App\Entity\Bordereau\BordereauSearch;
use App\Model\inventaire\InventaireModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Entity\inventaire\InventaireSearch;
use App\Form\bordereau\BordereauSearchType;
use Symfony\Component\VarDumper\Cloner\Data;
use App\Form\inventaire\InventaireSearchType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\genererPdf\GeneretePdfBordereau;
use App\Entity\inventaire\InventaireDetailSearch;
use App\Service\genererPdf\GeneretePdfInventaire;
use App\Form\inventaire\InventaireDetailSearchType;
use PhpOffice\PhpSpreadsheet\Calculation\TextData\Replace;

class InventaireController extends Controller
{
    use FormatageTrait;
    use Transformation;
    private InventaireModel $inventaireModel;
    private InventaireSearch $inventaireSearch;
    private BordereauSearch $bordereauSearch;
    private InventaireDetailSearch $inventaireDetailSearch;
    private GeneretePdfInventaire $generetePdfInventaire;
    private GeneretePdfBordereau $generetePdfBordereau;
    public function __construct()
    {
        parent::__construct();
        $this->inventaireModel = new InventaireModel();
        $this->inventaireSearch = new InventaireSearch();
        $this->inventaireDetailSearch = new InventaireDetailSearch();
        $this->generetePdfInventaire = new GeneretePdfInventaire();
        $this->bordereauSearch = new BordereauSearch;
        $this->generetePdfBordereau = new GeneretePdfBordereau;
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
            $data = $this->recupDataList($listInvent, true);
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
        $data = $this->dataDetailExcel($countSequence, $numinv);
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
            'pourcentage_ecart' => '% mont. écart',
            'dateInv' => 'Date invetaire'
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
        $this->generetePdfInventaire->genererPDF($data);
    }
    /**
     * @Route("/downloadfile/{filename}", name = "download_file")
     */
    public function downFile($filename)
    {
        $filePath  =   $_ENV['BASE_PATH_FICHIER'] . '/inventaire/' . $filename;
        if (!file_exists($filePath)) {
            die("⚠️ Le fichier n'existe pas : " . $filePath);
        }
        
        if (!is_readable($filePath)) {
            die("⚠️ Le serveur n'a pas la permission de lire le fichier !");
        }
        
        // Forcer le téléchargement du fichier Excel
        header("Content-Description: File Transfer");
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header("Content-Disposition: attachment; filename=\"" . basename($filePath) . "\"");
        header("Expires: 0");
        header("Cache-Control: must-revalidate");
        header("Pragma: public");
        header("Content-Length: " . filesize($filePath));
        
        readfile($filePath);
        exit;
        
    }

    public function recupDataList($listInvent, $uploadExcel = false)
    {
        $data = [];
        if (!empty($listInvent)) {
            for ($i = 0; $i < count($listInvent); $i++) {
                $numIntvMax = $this->inventaireModel->maxNumInv($listInvent[$i]['numero_inv']);
                $invLigne = $this->inventaireModel->inventaireLigneEC($numIntvMax[0]['numinvmax']);
                $data[$i] = [
                    'numero' => $listInvent[$i]['numero_inv'],
                    'description' => $listInvent[$i]['description'],
                    'ouvert' => (new DateTime($listInvent[$i]['ouvert_le']))->format('d/m/Y'),
                    'nbr_casier' => $listInvent[$i]['nbre_casier'],
                    'nbr_ref' => $listInvent[$i]['nbre_ref'],
                    'qte_comptee' => str_replace("."," ",$this->formatNumber($listInvent[$i]['qte_comptee']) ),
                    'statut' => $listInvent[$i]['statut'],
                    'montant' => str_replace("."," ",$this->formatNumber($listInvent[$i]['montant']) ),
                    'nbre_ref_ecarts_positif' => $invLigne[0]['nbre_ref_ecarts_positif'],
                    'nbre_ref_ecarts_negatifs' => $invLigne[0]['nbre_ref_ecarts_negatifs'],
                    'total_nbre_ref_ecarts' => $invLigne[0]['total_nbre_ref_ecarts'],
                    'pourcentage_ref_avec_ecart' => $invLigne[0]['pourcentage_ref_avec_ecart'] == "0%" ? "" : $invLigne[0]['pourcentage_ref_avec_ecart'],
                    'montant_ecart' => str_replace("."," ",$this->formatNumber($invLigne[0]['montant_ecart']) ),
                    'pourcentage_ecart' => $invLigne[0]['pourcentage_ecart'] == "0%" ? "" : $invLigne[0]['pourcentage_ecart'],
                ];
                if ($uploadExcel) {
                    $data[$i]['excel'] = $this->parcourFichier($data[$i]['numero']);
                }
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
        if (!empty($detailInvent)) {
            // dump($detailInvent);
            for ($j = 0; $j < count($detailInvent); $j++) {
                $data[] = [
                    "numinv" => $numinv,
                    "cst" => $detailInvent[$j]["cst"],
                    "refp" => $detailInvent[$j]["refp"],
                    "desi" => $detailInvent[$j]["desi"],
                    "casier" => $detailInvent[$j]["casier"],
                    "stock_theo" => $detailInvent[$j]["stock_theo"],
                    "qte_comptee_1" => "",
                    "qte_comptee_2" => "",
                    "qte_comptee_3" => "",
                    "ecart" => $detailInvent[$j]["ecart"],
                    "pourcentage_nbr_ecart" => $detailInvent[$j]["pourcentage_nbr_ecart"],
                    "pmp" => str_replace(".", " ", $this->formatNumber($detailInvent[$j]["pmp"])),
                    "montant_inventaire" => str_replace(".", " ", $this->formatNumber($detailInvent[$j]["montant_inventaire"])),
                    "montant_ajuste" => str_replace(".", " ", $this->formatNumber($detailInvent[$j]["montant_ajuste"])),
                    "pourcentage_ecart" => $detailInvent[$j]["pourcentage_ecart"] == "0%" ? " " : $detailInvent[$j]["pourcentage_ecart"],
                    "dateInv" => (new DateTime($detailInvent[$j]['dateinv']))->format('d/m/Y')
                ];
                if (!empty($countSequence)) {
                    for ($i = 0; $i < count($countSequence); $i++) {
                        $qteCompte =  $this->inventaireModel->qteCompte($numinv, $countSequence[$i]['nb_sequence'], $detailInvent[$j]['refp']);
                        $data[$j]["qte_comptee_" . ($i + 1)] = $qteCompte[0]['qte_comptee'] === "0" ? "" : $qteCompte[0]['qte_comptee'];
                    }
                }
            }
        }
        return $data;
    }
    public function dataDetailExcel($countSequence, $numinv)
    {
        $criteriaTab = $this->sessionService->get('inventaire_detail_search_criteria');
        $numinvCriteria = ($criteriaTab['numinv'] === "" || $criteriaTab['numinv'] === null) ? $numinv : $criteriaTab['numinv'];

        if ($numinv !== $numinvCriteria) {
            $this->redirectToRoute('detail_inventaire', ['numinv' => $numinvCriteria]);
        }

        $data = [];
        $detailInvent = $this->inventaireModel->inventaireDetail($numinv);
        if (!empty($detailInvent)) {
            // dump($detailInvent);
            for ($j = 0; $j < count($detailInvent); $j++) {
                $data[] = [
                    "numinv" => $numinv,
                    "cst" => $detailInvent[$j]["cst"],
                    "refp" => $detailInvent[$j]["refp"],
                    "desi" => $detailInvent[$j]["desi"],
                    "casier" => $detailInvent[$j]["casier"],
                    "stock_theo" => $detailInvent[$j]["stock_theo"],
                    "qte_comptee_1" => "0",
                    "qte_comptee_2" => "0",
                    "qte_comptee_3" => "0",
                    "ecart" => $detailInvent[$j]["ecart"],
                    "pourcentage_nbr_ecart" => $detailInvent[$j]["pourcentage_nbr_ecart"],
                    "pmp" => $detailInvent[$j]["pmp"],
                    "montant_inventaire" => $detailInvent[$j]["montant_inventaire"],
                    "montant_ajuste" => $detailInvent[$j]["montant_ajuste"],
                    "pourcentage_ecart" => $detailInvent[$j]["pourcentage_ecart"],
                    "dateInv" => (new DateTime($detailInvent[$j]['dateinv']))->format('d/m/Y')
                ];
                if (!empty($countSequence)) {
                    for ($i = 0; $i < count($countSequence); $i++) {
                        $qteCompte =  $this->inventaireModel->qteCompte($numinv, $countSequence[$i]['nb_sequence'], $detailInvent[$j]['refp']);
                        $data[$j]["qte_comptee_" . ($i + 1)] = $qteCompte[0]['qte_comptee'];
                    }
                }
            }
        }
        return $data;
    }


    private function parcourFichier($numInvent)
    {
        $downloadDir  =   $_ENV['BASE_PATH_FICHIER'] . '/inventaire/';
        $searchPattern  = $downloadDir . '*INV' . $numInvent . '*.xlsx';
        $matchingFiles = glob($searchPattern);

        if (!empty($matchingFiles)) {
            return true;
        } else {
            return false;
        }
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
    /**
     * @Route("/bordereu_de_comptage/{numInv}", name = "bordereu_comptage")
     */
    public function bordereau_comptage($numInv, Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        $this->bordereauSearch->setNumInv($numInv);
        $form = self::$validator->createBuilder(
            BordereauSearchType::class,
            $this->bordereauSearch,
            [
                'method' => 'GET'
            ]
        )->getForm();
        $form->handleRequest($request);
        //initialisation criteria
        $criteria = $this->bordereauSearch;
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria =  $form->getdata();
        }
        //transformer l'objet zn tableau
        $criteriaTab = $criteria->toArray();
        $this->sessionService->set('bordereau_search_criteria', $criteriaTab);
        $data = $this->recupDataBordereau($numInv, $criteriaTab);
        self::$twig->display('bordereau/bordereau.html.twig', [
            'form' => $form->createView(),
            'data' => $data,
            'numinvpdf' => $numInv,
        ]);
    }

    /**
     * @Route("/export_pdf_bordereau/{numInv}", name = "export_pdf_bordereau")
     */
    public function pdfExport($numInv)
    {
        // Vérification si l'utilisateur est connecté
        $this->verifierSessionUtilisateur();
        $criteriaTab =  $this->sessionService->get('bordereau_search_criteria');
        $data = $this->recupDataBordereau($numInv, $criteriaTab);
        $this->generetePdfBordereau->genererPDF($data);
    }

    public function recupDataBordereau($numInv, $criteria)
    {
        $data = [];
        $listBordereau = $this->inventaireModel->bordereauListe($numInv, $criteria);
        if (!empty($listBordereau)) {
            for ($i = 0; $i < count($listBordereau); $i++) {
                $data[] = [
                    'numinv' => $listBordereau[$i]['numinv'],
                    'numBordereau' => $listBordereau[$i]['numbordereau'],
                    'ligne' => $listBordereau[$i]['ligne'],
                    'casier' => $listBordereau[$i]['casier'],
                    'cst' => $listBordereau[$i]['cst'],
                    'refp' => $listBordereau[$i]['refp'],
                    'descrip' => $listBordereau[$i]['descrip'],
                    'qte_theo' => $listBordereau[$i]['qte_theo'],
                    'qte_alloue' => $listBordereau[$i]['qte_alloue'],
                    'dateinv' => (new DateTime($listBordereau[$i]['dateinv']))->format('d/m/Y')
                ];
            }
        }

        return $data;
    }
}
