<?php

namespace App\Controller\planningAtelier;

use App\Controller\Controller;
use App\Dto\PlanningAtelier\PlanningAtelierSearchDto;
use App\Form\planningAtelier\planningAtelierSearchType;
use App\Model\planningAtelier\planningAtelierModel;
use App\Service\planningAtelier\PlanningAtelierService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/planningAte")
 */
class planningAtelierControler extends Controller
{

    private PlanningAtelierService $service;
    private planningAtelierModel $model;

    public function __construct()
    {
        parent::__construct();
        $this->service = new PlanningAtelierService();
        $this->model = new planningAtelierModel();
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
            null,
            ['method' => 'GET']
        )->getForm();
        $form->handleRequest($request);
        $dto = $form->getData() ?? new PlanningAtelierSearchDto();

        $output = [];
        $dates = [];
        $filteredDates = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getSessionService()->set('planning_atelier_search_criteria', $dto);

            $codeSociete = 'HFF';

            $startStr = $dto->dateDebut ? $dto->dateDebut->format('Y-m-d') : null;
            $endStr = $dto->dateFin ? $dto->dateFin->format('Y-m-d') : null;

            if (!$startStr && !$endStr) {
                [$startStr, $endStr] = $this->model->getMinMaxDates($codeSociete, $dto);
            }

            $result = $this->model->getList($codeSociete, $dto);
            $processedData = $this->service->process($result, $startStr, $endStr);

            $output = $processedData['planning'];
            $dates = $processedData['dates'];
            $filteredDates = $processedData['filteredDates'];

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

    /**
     * @Route("/export_excel_planningAtelier", name= "export_planningAtelier")
     */
    public function exportExcel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $data = $this->getSessionService()->get('data_export_planningAtelier_excel');
        $dates = $this->getSessionService()->get('dates_export_planningAtelier_excel');

        $data = $this->service->processExcelData($data, $dates);
        $dateCount = count($dates);

        $rowIdx = 1;
        foreach ($data as $row) {
            $sheet->fromArray($row, null, "A$rowIdx");
            $rowIdx++;
        }

        $colStart = 8;
        for ($i = 0; $i < $dateCount; $i++) {
            $col1 = Coordinate::stringFromColumnIndex($colStart + $i * 2);
            $col2 = Coordinate::stringFromColumnIndex($colStart + $i * 2 + 1);
            $sheet->mergeCells("$col1" . "1:$col2" . "1");
        }

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="export.xlsx"');
        setcookie('fileDownload', 'true', 0, '/');
        $writer->save('php://output');
        exit();
    }

}
