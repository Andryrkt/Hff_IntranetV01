<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelService
{
    public function createSpreadsheet(array $data, $filename = "donnees")
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Ajouter des données
        foreach ($data as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex + 1, $value);
            }
        }

        // $response = new StreamedResponse(function() use ($spreadsheet) {
        //     $writer = new Xlsx($spreadsheet);
        //     $writer->save('php://output');
        // });

        // $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        // $response->headers->set('Content-Disposition', 'attachment;filename="export.xlsx"');
        // $response->headers->set('Cache-Control', 'max-age=0');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }

    public function createSpreadsheetEnregistrer(array $data, string $filePath)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Ajouter des données
        foreach ($data as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex + 1, $value);
            }
        }

        // Définir le chemin et nom du fichier à enregistrer
        // $filename = 'donnees_' . date('Ymd_His') . '.xlsx';
        // $filePath = __DIR__ . '/exports/' . $filename; // Assure-toi que le dossier 'exports/' existe et est accessible en écriture

        // Créer le dossier si nécessaire
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        // Sauvegarder le fichier sur le disque
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath; // Tu peux retourner le chemin pour le réutiliser (par ex. pour un lien de téléchargement)
    }

    public function createSpreadsheetMode(array $data, $startCell = 'A1')
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Initialiser l'index de ligne et de colonne
        [$startColumn, $startRow] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::coordinateFromString($startCell);
        $startColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($startColumn);

        // Ajouter des données en partant de la cellule spécifique
        foreach ($data as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $sheet->setCellValueByColumnAndRow($startColumnIndex + $colIndex, $startRow + $rowIndex, $value);
            }
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="donnees.xlsx"');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }
}
