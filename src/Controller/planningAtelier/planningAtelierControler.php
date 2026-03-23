<?php

namespace App\Controller\planningAtelier;

use App\Controller\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\planningAtelier\planningAtelierModel;
use App\Entity\planningAtelier\planningAtelierSearch;
use App\Form\planningAtelier\planningAtelierSearchType;

/**
 * @Route("/planningAte")
 */
class planningAtelierControler extends Controller
{
    private planningAtelierSearch $planningAtelierSearch;
    private planningAtelierModel $planningAtelierModel;
    public function __construct()
    {
        parent::__construct();
        $this->planningAtelierSearch = new planningAtelierSearch();
        $this->planningAtelierModel = new planningAtelierModel();
    }
    /**
     * @route("/planningAtelier", name = "planningAtelier_vue")
     * 
     * @return void
     */
    public function planningAtelierEncours(Request $request)
    {
        $form = $this->getFormFactory()->createBuilder(
            PlanningAtelierSearchType::class,
            $this->planningAtelierSearch,
            ['method' => 'GET']
        )->getForm();
        $form->handleRequest($request);
        $criteria = $this->planningAtelierSearch;

        $output = [];
        $filteredDates = [];
        $dates = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
            $start = $criteria->getDateDebut();
            $end = $criteria->getDateFin();

            $result = $this->planningAtelierModel->recupData($criteria);
            $interval = new \DateInterval('P1D');
            $period = new \DatePeriod($start, $interval, (clone $end)->modify('+1 day'));

            foreach ($period as $date) {
                $dates[] = $date;
                $filteredDates[] = $date->format('Y-m-d');
            }
            $output = $this->recupdata($result, $dates, $output);

            $this->getSessionService()->set('data_export_planningAtelier_excel', $output);
            $this->getSessionService()->set('dates_export_planningAtelier_excel', $dates);
        }
        return $this->render('planningAtelier/planningAtelier.html.twig', [
            'form' => $form->createView(),
            'dates' => $dates,
            'filteredDates' => $filteredDates,
            'planning' => $output
        ]);
    }

    public function recupdata($result, $dates, $output)
    {
        foreach ($result as $item) {
            $key = $item['agenceem'] . '|' . $item['section'] . '|' . $item['intitule'] . '|' . $item['numor'] . '|' . $item['itv'] . '|' . $item['ressource'] . '|' . $item['nbjour'];

            if (!isset($output[$key])) {
                $output[$key] = [
                    "agenceem" => $item["agenceem"],
                    "section" => $item["section"],
                    "intitule" => $item["intitule"],
                    "numor" => $item["numor"],
                    "itv" => $item["itv"],
                    "ressource" => $item["ressource"],
                    "nbjour" => $item["nbjour"],
                    "nbTotalJ" => 0,
                    "presence" => [] // clef: 'Y-m-d', valeur: ['matin' => bool, 'apm' => bool]
                ];
            }
            $output[$key]['nbTotalJ'] += $item["nbjour"];

            $debut = new \DateTime($item["datedebut"]);
            $fin = new \DateTime($item["datefin"]);

            foreach ($dates as $date) {
                $dateStr = $date->format('Y-m-d');

                $matin_debut = new \DateTime("$dateStr 08:00:00");
                $matin_fin   = new \DateTime("$dateStr 12:00:00");
                $aprem_debut = new \DateTime("$dateStr 13:30:00");
                $aprem_fin   = new \DateTime("$dateStr 17:30:00");

                if (!isset($output[$key]['presence'][$dateStr])) {
                    $output[$key]['presence'][$dateStr] = ['matin' => false, 'apm' => false];
                }

                if ($fin >= $matin_debut && $debut < $matin_fin) {
                    $output[$key]['presence'][$dateStr]['matin'] = true;
                }
                if ($fin >= $aprem_debut && $debut < $aprem_fin) {
                    $output[$key]['presence'][$dateStr]['apm'] = true;
                }
            }
        }
        return $output;
    }
    /**
     * @Route("/export_excel_planningAtelier", name= "export_planningAtelier")
     */
    public function exportExcel()
    {
        $data = $this->getSessionService()->get('data_export_planningAtelier_excel', []);
        $dates = $this->getSessionService()->get('dates_export_planningAtelier_excel', []);


        $data = $this->transformerDataPourExcel($data, $dates);

        [$headerRow1, $headerRow2] = $this->generateTwoRowHeader($dates);
        // Insérer en haut les 2 lignes de header
        array_unshift($data, $headerRow2);
        array_unshift($data, $headerRow1);

        $this->exporterDonneesExcel($data, count($dates));
    }

    private function transformerDataPourExcel(array $data, array $dates): array
    {
        $result = [];

        foreach ($data as $ligne) {
            $row = [
                $ligne['agenceem'],
                $ligne['section'],
                $ligne['intitule'],
                $ligne['numor'],
                $ligne['itv'],
                $ligne['ressource'],
                $ligne['nbTotalJ']
            ];

            foreach ($dates as $date) {
                $dateStr = $date->format('Y-m-d');
                if (isset($ligne['presence'][$dateStr])) {
                    $row[] = $ligne['presence'][$dateStr]['matin'] ? 'X' : '';
                    $row[] = $ligne['presence'][$dateStr]['apm'] ? 'X' : '';
                } else {
                    $row[] = '';
                    $row[] = '';
                }
            }

            $result[] = $row;
        }

        return $result;
    }


    private function generateTwoRowHeader(array $dates): array
    {
        $fixedHeaders = ['Agence Travaux', 'Section', 'Intitulé Travaux', 'numOR', 'Itv', 'Ressource', 'Nb jour'];
        $headerRow1 = $fixedHeaders;
        $headerRow2 = array_fill(0, count($fixedHeaders), '');

        foreach ($dates as $date) {
            $label = $date->format('l d/m');
            $headerRow1[] = $label;
            $headerRow1[] = ''; // pour fusion
            $headerRow2[] = 'mtn';
            $headerRow2[] = 'apm';
        }

        return [$headerRow1, $headerRow2];
    }

    private function exporterDonneesExcel(array $data, int $nbDates)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Écrire les données ligne par ligne
        $rowIndex = 1;
        foreach ($data as $row) {
            $sheet->fromArray($row, null, "A$rowIndex");
            $rowIndex++;
        }

        // Fusion des cellules de la première ligne pour les dates
        $colStart = 8; // A=1, donc H=8 → début des dates
        for ($i = 0; $i < $nbDates; $i++) {
            $col1 = Coordinate::stringFromColumnIndex($colStart + $i * 2);
            $col2 = Coordinate::stringFromColumnIndex($colStart + $i * 2 + 1);
            $sheet->mergeCells("$col1" . "1:$col2" . "1");
        }

        // Téléchargement
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="export.xlsx"');
        $writer->save('php://output');
        exit();
    }
}
