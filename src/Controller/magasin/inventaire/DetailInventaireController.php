<?php

namespace App\Controller\magasin\inventaire;

use DateTime;
use App\Controller\Controller;
use App\Entity\admin\Application;
use App\Controller\Traits\FormatageTrait;
use App\Controller\Traits\Transformation;
use App\Model\inventaire\InventaireModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Controller\Traits\AutorisationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\inventaire\DetailInventaireSearch;
use App\Form\inventaire\detailInventaireSearchType;
use App\Controller\BaseController;

/**
 * @Route("/magasin/inventaire")
 */
class DetailInventaireController extends BaseController
{
    use FormatageTrait;
    use Transformation;
    use AutorisationTrait;

    private InventaireModel $InventaireModel;
    private DetailInventaireSearch $DetailInventaireSearch;
    private ?\DateTime $datefin = null;
    private ?\DateTime $dateDebut = null;

    public function __construct()
    {
        parent::__construct();
        $this->InventaireModel = new InventaireModel;
        $this->DetailInventaireSearch = new DetailInventaireSearch;
        $this->datefin = new \DateTime();
        $this->dateDebut = clone $this->datefin;
        $this->dateDebut->modify('first day of this month');
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
     * @Route("/inventaire_detail", name = "liste_detail_inventaire")
     * 
     * @return void
     */
    public function listeDetailInventaire(Request $request)
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        $this->autorisationAcces($this->getUser(), Application::ID_INV);

        $agence = $this->transformEnSeulTableauAvecKey($this->InventaireModel->recuperationAgenceIrium());
        $this->dateDebut->modify('first day of this month');
        $this->DetailInventaireSearch
            ->setAgence($agence['01-ANTANANARIVO'])
            ->setDateDebut($this->dateDebut)
            ->setDateFin($this->datefin)
        ;
        $form = $this->getFormFactory()->createBuilder(
            detailInventaireSearchType::class,
            $this->DetailInventaireSearch,
            [
                'method' => 'GET'
            ]
        )->getForm();
        $form->handleRequest($request);
        $criteria = $this->DetailInventaireSearch;

        $this->getSessionService()->set('detail_invetaire_search_criteria', $criteria);
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria =  $form->getdata();
        }

        $data = [];
        if ($request->query->get('action') !== 'oui') {
            $listInvent = $this->InventaireModel->ligneInventaire($criteria);
            $data = $this->recupData($listInvent);
        }
        return $this->render('inventaire/detailInventaire.html.twig', [
            'form' => $form->createView(),
            'data' => $data
        ]);
    }
    /**
     * @Route("/export_excel_detail_inventaire", name = "export_excel_detail_inventaire")
     */
    public function exportExcel()
    {
        //verification si user connecter
        $this->verifierSessionUtilisateur();
        $criteria = $this->getSessionService()->get('detail_invetaire_search_criteria');
        $listInvent = $this->InventaireModel->ligneInventaire($criteria);
        $data = $this->recupData($listInvent);
        $header = [
            'numinv' => 'Numéro',
            'date' => 'Date',
            'nbr_comptage' => 'Nbr comptage',
            'nb_bordereau' => 'Nbr bordereau',
            'ligne' => 'Ligne',
            'cst' => 'Constructeur',
            'ref' => 'Reférence',
            'desi' => 'Désignation',
            'casier' => 'Casier',
            'tsock' => 'Stock',
            'prix' => 'Prix',
            'valeur_stock' => 'Valeur stock',
            'comptage1' => 'Comptage1',
            'comptage2' => 'Comptage2',
            'comptage3' => 'Comptage3',
            'ecart' => 'Ecart',
            'montant_ecart' => 'Mont.ecart',
        ];
        array_unshift($data, $header);
        $this->exportDonneesExcel($data);
    }

    public function recupData($inventDispo)
    {
        $data = [];
        if (!empty($inventDispo)) {
            for ($i = 0; $i < count($inventDispo); $i++) {
                $data[$i] = [
                    'numinv' => $inventDispo[$i]['numinv'],
                    'date' => (new DateTime($inventDispo[$i]['date']))->format('d/m/Y'),
                    'nbr_comptage' => $inventDispo[$i]['nbr_comptage'],
                    'nb_bordereau' => $inventDispo[$i]['nb_bordereau'],
                    'ligne' => $inventDispo[$i]['ligne'],
                    'cst' => $inventDispo[$i]['cst'],
                    'ref' => $inventDispo[$i]['ref'],
                    'desi' => $inventDispo[$i]['desi'],
                    'casier' => $inventDispo[$i]['casier'],
                    'tsock' => $inventDispo[$i]['tsk'],
                    'prix' => str_replace(".", "", $this->formatNumber($inventDispo[$i]['prix'])),
                    'valeur_stock' => str_replace(".", "", $this->formatNumber($inventDispo[$i]['valeur_stock'])),
                    'comptage1' => $inventDispo[$i]['comptage1'],
                    'comptage2' => $inventDispo[$i]['comptage2'],
                    'comptage3' => $inventDispo[$i]['comptage3'],
                    'ecart' => $inventDispo[$i]['ecart'] == "0.00" ? "" : $inventDispo[$i]['ecart'],
                    'montant_ecart' => str_replace(".", "", $this->formatNumber($inventDispo[$i]['montant_ecart'])) == "0,0" ? "" : str_replace(".", "", $this->formatNumber($inventDispo[$i]['montant_ecart'])),
                ];
            }
        }
        return $data;
    }
}
