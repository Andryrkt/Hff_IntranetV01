<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use OpenSpout\Writer\Common\Creator\WriterEntityFactory;

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

    /**
     * Export en streaming (OpenSpout) : chaque ligne est écrite au fur et à mesure.
     *
     * @param iterable $rows lignes (tableaux de valeurs), entête comprise
     */
    public function streamSpreadsheet(iterable $rows, string $filename = "donnees"): void
    {
        setcookie('fileDownload', 'true', 0, '/');

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToBrowser("$filename.xlsx"); // envoie aussi les headers HTTP

        foreach ($rows as $row) {
            $writer->addRow(WriterEntityFactory::createRowFromArray($row));
        }

        $writer->close();
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
