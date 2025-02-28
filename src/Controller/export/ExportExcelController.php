<?php

namespace App\Controller\export;

use App\Controller\Controller;
use App\Controller\Traits\FormatageTrait;
use App\Model\export\ExportExcelModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\Routing\Annotation\Route;

class ExportExcelController extends Controller
{
    use FormatageTrait;
    /**
     * @Route("/Export/Excel/specifique", name="export_excel_specifique")
     */
    public function detailExcel()
    {
        $model = new ExportExcelModel;
        $allData = $model->recuperationConstructeur();
        $dataExcel[] = [
            "constructeur" => "constructeur",
            "referencepiece" => "referencepiece",
            "natmouv" => "natmouv",
            "qte" => "qte",
            "prix" => "prix",
            "datemouv" => "datemouv",
            "ident" => "ident",
            "natop" => "natop",
            "nomtiers" => "nomtiers",
            "numfac" => "numfac",
            "dateop" => "dateop",
            "module" => "module",
        ];
        foreach ($allData as $data) {
            $rows = $model->recuperationDonneeConstructeur($data['referencepiece'], $data['constructeur']);
            foreach ($rows as $row) {
                $dataExcel[] = [
                    'constructeur' => $row['constructeur'],
                    'referencepiece' => $row['referencepiece'],
                    'natmouv' => $row['natmouv'] ?? '',
                    'qte' => $row['qte'] ?? '',
                    'prix' => $row['prix'] ?? '',
                    'datemouv' => $row['datemouv'] !== null ? $this->formatageDate($row['datemouv']) : '',
                    'ident' => $row['ident'] ?? '',
                    'natop' => $row['natop'] ?? '',
                    'nomtiers' => $row['nomtiers'] ?? '',
                    'numfac' => $row['numfac'] ?? '',
                    'dateop' => $row['dateop']  !== null ? $this->formatageDate($row['dateop']) : '',
                    'module' => $row['module'] ?? '',
                ];
            }
        }

        $this->exporterDonneesExcel($dataExcel);
    }

    private function exporterDonneesExcel($data)
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
        header('Content-Disposition: attachment; filename="export-excel.xlsx"');
        $writer->save('php://output');
        exit();
    }
}
