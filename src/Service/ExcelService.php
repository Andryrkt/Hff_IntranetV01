<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelService
{
    private function buildSpreadsheet(array $data): Spreadsheet
    {
        ini_set('memory_limit', '512M');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $rowIndex = 1;
        foreach ($data as $row) {
            $sheet->fromArray($row, null, "A$rowIndex");
            $rowIndex++;
        }

        return $spreadsheet;
    }

    public function createSpreadsheet(array $data, string $filename = "donnees"): void
    {
        $spreadsheet = $this->buildSpreadsheet($data);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename.xlsx\"");
        setcookie('fileDownload', 'true', 0, '/');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }

    public function createSpreadsheetEnregistrer(array $data, string $filePath): string
    {
        $spreadsheet = $this->buildSpreadsheet($data);

        $dir = dirname($filePath);
        if (!is_dir($dir)) mkdir($dir, 0775, true);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $spreadsheet->disconnectWorksheets();

        return $filePath;
    }
}
