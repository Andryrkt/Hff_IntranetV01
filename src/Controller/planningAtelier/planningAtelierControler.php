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
            planningAtelierSearchType::class,
            $this->planningAtelierSearch,
            ['method' => 'GET']
        )->getForm();
        $form->handleRequest($request);
        $criteria = $this->planningAtelierSearch;

        $output = [];
        $filteredDates = [];
        $dates = [];
        $paginationQuery = $request->query->all();
        unset($paginationQuery['page']);

        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
            $start = $criteria->getDateDebut();
            $end = $criteria->getDateFin();

            $result = $this->planningAtelierModel->recupData($criteria);
            if (!$start && !$end) {
                [$start, $end] = $this->extractMinMaxDateFromResult($result);
            }

            $interval = new \DateInterval('P1D');
            if ($start && $end) {
                $period = new \DatePeriod($start, $interval, (clone $end)->modify('+1 day'));
                foreach ($period as $date) {
                    $dates[] = $date;
                    $filteredDates[] = $date->format('Y-m-d');
                }
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
            $key = $item['agence_em'] . '|' . $item['section'] . '|' . $item['intitule'] . '|' . $item['num_or'] . '|' . $item['itv'] . '|' . $item['ressource'];

            if (!isset($output[$key])) {
                $output[$key] = [
                    "agenceem" => $item["agence_em"],
                    "section" => $item["section"],
                    "intitule" => $item["intitule"],
                    "numor" => $item["num_or"],
                    "itv" => $item["itv"],
                    "ressource" => $item["ressource"],
                    "nbjour" => $item["nb_jour"],
                    "nbTotalJ" => 0,
                    "presence" => [] // clef: 'Y-m-d', valeur: ['matin' => bool, 'apm' => bool]
                ];
            }
            $output[$key]['nbTotalJ'] += $item["nb_jour"];
            $debut = new \DateTime($item["date_debut"]);
            $dateStr = $debut->format('Y-m-d');
            //? Initialisation de la présence pour cette date si elle n'existe pas encore
            if (!isset($output[$key]['presence'][$dateStr])) {
                $output[$key]['presence'][$dateStr] = [
                    'matin' => false,
                    'apm' => false,
                    'heure' => NULL,
                    'hmtn' => NULL,
                    'hapm' => NULL
                ];
            }
            $output[$key]['presence'][$dateStr] = $this->calculatePresence($output[$key]['presence'][$dateStr], $item);
        }
        return $output;
    }

    private function calculatePresence($presenceData, $item)
    {
        $data = $presenceData;
        $debut = new \DateTime($item["date_debut"]);
        $fin = new \DateTime($item["date_fin"]);
        $hdebut = new \Datetime($item["hpointee_debut"]);
        $hfin = new \Datetime($item["hpointee_fin"]);
        $dateStr = $debut->format('Y-m-d');
        $matin_debut = new \DateTime("$dateStr 08:00:00");
        $matin_fin   = new \DateTime("$dateStr 12:00:00");
        $aprem_debut = new \DateTime("$dateStr 13:30:00");
        $aprem_fin   = new \DateTime("$dateStr 17:30:00");

        //? Calcul de la planning pour la matinée
        if ($fin >= $matin_debut && $debut < $matin_fin) {
            $data['matin'] = true;
        }
        //? Calcul de la planning pour l'après-midi
        if ($fin >= $aprem_debut && $debut < $aprem_fin) {
            $data['apm'] = true;
        }
        if (!isset($item["hpointee"]))
            return $data;
        $hpointee = (int)$item["hpointee"];
        if ($data['heure'] === NULL) {
            $data['heure'] = $hpointee;
        }
        if ($debut < $fin && $hfin < $fin && $hdebut < $hfin) {
            $data['heure'] += $hpointee;
        }
        $isFullDay = $hdebut <= $matin_debut && $hfin >= $aprem_fin;
        if ($hdebut <= $matin_fin && $hfin >= $matin_debut && !$isFullDay) {
            $data['hmtn'] = $hpointee;
        }
        if ($hdebut <= $aprem_fin && $hfin >= $aprem_debut && !$isFullDay) {
            $data['hapm'] = $hpointee;
        }

        return $data;

    }

    private function extractMinMaxDateFromResult(array $result): array
    {
        $minDate = null;
        $maxDate = null;

        foreach ($result as $item) {
            if (empty($item['date_debut']) || empty($item['date_fin'])) {
                continue;
            }

            $debut = new \DateTime($item['date_debut']);
            $fin = new \DateTime($item['date_fin']);

            if ($minDate === null || $debut < $minDate) {
                $minDate = $debut;
            }
            if ($maxDate === null || $fin > $maxDate) {
                $maxDate = $fin;
            }
        }

        return [$minDate, $maxDate];
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
                $ligne['agence_em'],
                $ligne['section'],
                $ligne['intitule'],
                $ligne['num_or'],
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
