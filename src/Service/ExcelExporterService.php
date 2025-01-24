<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;

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

            $row = $this->cleanData($row);

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


    public function exportToExcelBadm(array $data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Ajoutez les en-têtes
        $sheet->setCellValue('A1', 'Numéro Demande');
        $sheet->setCellValue('B1', 'Type de mouvement');
        $sheet->setCellValue('C1', 'Statut');
        $sheet->setCellValue('D1', 'Id Materiel');
        $sheet->setCellValue('E1', 'Date Demande');
        $sheet->setCellValue('F1', 'Agence et service emetteur');
        $sheet->setCellValue('G1', 'Casier emetteur');
        $sheet->setCellValue('H1', 'Agence et service Destinataire');
        $sheet->setCellValue('I1', 'Casier Destinataire');
        $sheet->setCellValue('J1', 'Motif matériel');
        $sheet->setCellValue('K1', 'Etat à l\'achat');
        $sheet->setCellValue('L1', 'Date Mise en location');
        $sheet->setCellValue('M1', 'Coût d\'aquisition');
        $sheet->setCellValue('N1', 'Amortissement');
        $sheet->setCellValue('O1', 'Valeur Net Comptable');
        $sheet->setCellValue('P1', 'Nom du Client');
        $sheet->setCellValue('Q1', 'Modalité de paiement');
        $sheet->setCellValue('R1', 'Prix de vente HT');
        $sheet->setCellValue('S1', 'Motif mise au rebut');
        $sheet->setCellValue('T1', 'Heure machine');
        $sheet->setCellValue('U1', 'Km machine');
      

        // Ajoutez d'autres en-têtes selon vos besoins

        // Ajoutez les données
        $row = 2;
        foreach ($data as $item) {
            
            $sheet->setCellValue('A' . $row, $item->getNumBadm());
            $sheet->setCellValue('B' . $row, $item->getTypeMouvement());
            $sheet->setCellValue('C' . $row, $item->getStatutDemande());
            $sheet->setCellValue('D' . $row, $item->getIdMateriel());
            $sheet->setCellValue('E' . $row, $item->getDateDemande()->format('d-m-Y'));
            $sheet->setCellValue('F' . $row, $item->getAgenceServiceEmetteur());
            $sheet->setCellValue('G' . $row, $item->getCasierEmetteur());
            $sheet->setCellValue('H' . $row, $item->getAgenceServiceDestinataire());
            $sheet->setCellValue('I' . $row, $item->getCasierDestinataire());
            $sheet->setCellValue('J' . $row, $item->getMotifMateriel());
            $sheet->setCellValue('K' . $row, $item->getEtatAchat());
            $sheet->setCellValue('L' . $row, $item->getDateMiseLocation()->format(''));
            $sheet->setCellValue('M' . $row, $item->getCoutAcquisition());
            $sheet->setCellValue('N' . $row, $item->getAmortissement());
            $sheet->setCellValue('O' . $row, $item->getValeurNetComptable());
            $sheet->setCellValue('P' . $row, $item->getNomClient());
            $sheet->setCellValue('Q' . $row, $item->getModalitePaiement());
            $sheet->setCellValue('R' . $row, $item->getPrixVenteHt());
            $sheet->setCellValue('S' . $row, $item->getMotifMiseRebut());
            $sheet->setCellValue('T' . $row, $item->getHeureMachine());
            $sheet->setCellValue('U' . $row, $item->getKmMachine());


            
            // Ajoutez d'autres colonnes selon vos besoins
            $row++;
        }

        // Envoyer le fichier pour téléchargement
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="donnees.xlsx"');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }


    private function cleanData($row) {
        // Correction de l'encodage des caractères spéciaux
        $row['Motif_Deplacement'] = str_replace('ÃƒÂª', 'ê', $row['Motif_Deplacement']);
        $row['Motif_Deplacement'] = str_replace('Ã©', 'é', $row['Motif_Deplacement']);
        
        // Remplacer les valeurs nulles par des chaînes vides
        foreach ($row as $key => $value) {
            if ($value === null) {
                $row[$key] = '';
            }
        }

        // Formatage des dates
        if (!empty($row['Date_Demande'])) {
            $date = \DateTime::createFromFormat('d-m-Y', $row['Date_Demande']);
            if ($date) {
                $row['Date_Demande'] = $date->format('d-m-Y');
            }
        }

        return $row;
    }
}

