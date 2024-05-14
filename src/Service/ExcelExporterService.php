<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExporterService {
    
    public function exportData($data) {
        // Créer un nouveau Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Définir les en-têtes et les colonnes
        $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T'];
        $headers = ['Description', 'Sous Type document', 'Numero Ordre Mission', 'Date Demande', 'Motif Deplacement', 'Matricule', 'Nom', 'Prenom', 'Mode Paiement', 'Agence - Service', 'Date Debut', 'Date Fin', 'Nombre Jour', 'Client', 'Fiche', 'Lieu Intervention', 'NumVehicule', 'Total Autres Depenses', 'Total General Payer', 'Devis' ];
        $rowCount = 1;

        foreach ($headers as $key => $value) {
            $sheet->setCellValue($columns[$key] . $rowCount, $value);
        }

        foreach ($data as $row) {
            $rowCount++;
            $sheet->setCellValue('A' . $rowCount, $row['Description']);
            $sheet->setCellValue('B' . $rowCount, $row['Sous_type_document']);
            $sheet->setCellValue('C' . $rowCount, $row['Numero_Ordre_Mission']);
            $sheet->setCellValue('D' . $rowCount, $row['Date_Demande']);
            $sheet->setCellValue('E' . $rowCount, $row['Motif_Deplacement']);
            $sheet->setCellValue('F' . $rowCount, $row['Matricule']);
            $sheet->setCellValue('G' . $rowCount, $row['Nom']);
            $sheet->setCellValue('H' . $rowCount, $row['Prenom']);
            $sheet->setCellValue('I' . $rowCount, $row['Mode_Paiement']);
            $sheet->setCellValue('J' . $rowCount, $row['LibelleCodeAgence_Service']);
            $sheet->setCellValue('K' . $rowCount, $row['Date_Debut']);
            $sheet->setCellValue('L' . $rowCount, $row['Date_Fin']);
            $sheet->setCellValue('M' . $rowCount, $row['Nombre_Jour']);
            $sheet->setCellValue('N' . $rowCount, $row['Client']);
            $sheet->setCellValue('O' . $rowCount, $row['Fiche']);
            $sheet->setCellValue('P' . $rowCount, $row['Lieu_Intervention']);
            $sheet->setCellValue('Q' . $rowCount, $row['NumVehicule']);
            $sheet->setCellValue('R' . $rowCount, $row['Total_Autres_Depenses']);
            $sheet->setCellValue('S' . $rowCount, $row['Total_General_Payer']);
            $sheet->setCellValue('T' . $rowCount, $row['Devis']);
        }

        // Envoyer le fichier pour téléchargement
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="donnees.xlsx"');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }
}
