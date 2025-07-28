<?php

namespace App\Service\dit\transfer;

use App\Model\dit\transfer\AncienDitExterneModel;
use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

class RecupDataService
{
    private $ancienDitExternModel;

    public function __construct()
    {
        $this->ancienDitExternModel = new AncienDitExterneModel();
    }

    /**
     * Methode qui recupère les données à transferer dans une base de donnée
     *
     * @return array
     */
    public function recupDansBaseDeDonnerDit(): array
    {
        return $this->ancienDitExternModel->recupDit();
    }

    /**
     * Methode qui recupère les données dans un fichier Excel et le transforme en tableau
     *  où les clés sont le première ligne pour chaque tableau
     *
     * @return array
     */
    public function recupDansExcel(): array
    {
        try {

            // Demander à l'utilisateur d'entrer le chemin du fichier
            $filePath = readline("Entrez le chemin du fichier Excel (.xlsx) : ");

            // Vérifier si le fichier existe
            if (! file_exists($filePath)) {
                throw new Exception("Le fichier $filePath n'existe pas.");
            }

            $spreadsheet = IOFactory::load($filePath);

            // Lister les noms des feuilles disponibles
            $sheetNames = $spreadsheet->getSheetNames();
            echo "Feuilles disponibles dans le fichier Excel :\n";
            foreach ($sheetNames as $index => $name) {
                echo "$index : $name\n"; // Affiche l'index et le nom
            }

            // Demander à l'utilisateur d'entrer le nom ou le numéro de la feuille
            $choice = readline("Voulez-vous entrer un [N]uméro ou un [NOM] de feuille ? (N/NOM) : ");
            $sheet = null;

            if (strtoupper($choice) === 'N') {
                // L'utilisateur entre un numéro de feuille
                $sheetIndex = (int)readline("Entrez le numéro de la feuille (0 pour la première feuille) : ");
                if (isset($sheetNames[$sheetIndex])) {
                    $sheet = $spreadsheet->getSheet($sheetIndex);
                } else {
                    throw new Exception("Le numéro de feuille $sheetIndex n'existe pas.");
                }
            } else {
                // L'utilisateur entre un nom de feuille
                $sheetName = readline("Entrez le nom de la feuille : ");
                $sheet = $spreadsheet->getSheetByName($sheetName);
                if ($sheet === null) {
                    throw new Exception("La feuille '$sheetName' n'existe pas.");
                }
            }

            // Initialiser un tableau pour stocker les données
            $data = [];

            // Récupérer la première ligne comme en-têtes
            $headers = [];
            $firstRow = true;

            foreach ($sheet->getRowIterator() as $row) {
                $rowData = [];
                foreach ($row->getCellIterator() as $cell) {
                    $rowData[] = $cell->getValue();
                }

                if ($firstRow) {
                    // Utiliser la première ligne comme en-têtes
                    $headers = $rowData;
                    $firstRow = false; // Marquer la première ligne comme traitée
                } else {
                    // Associer les valeurs aux en-têtes
                    $data[] = array_combine($headers, $rowData);
                }
            }

            return $data;

        } catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }

    /**
     * Methode qui recupère les données dans un fichier CSV et le transforme en tableau
     *  où les clés sont le première ligne pour chaque tableau
     *
     * @return array
     */
    public function recupDansCsv(): array
    {
        try {
            // Demander à l'utilisateur d'entrer le chemin du fichier
            $filePath = readline("Entrez le chemin du fichier CSV (.csv) : ");

            // Vérifier si le fichier existe
            if (! file_exists($filePath)) {
                throw new Exception("Le fichier $filePath n'existe pas.");
            }

            $reader = new Csv();
            $spreadsheet = $reader->load($filePath);

            // Récupérer la première feuille
            $sheet = $spreadsheet->getActiveSheet();

            // Initialiser un tableau pour stocker les données
            $data = [];
            $headers = [];
            $firstRow = true;

            foreach ($sheet->getRowIterator() as $row) {
                $rowData = [];
                foreach ($row->getCellIterator() as $cell) {
                    $rowData[] = $cell->getValue();
                }

                if ($firstRow) {
                    // Utiliser la première ligne comme en-têtes
                    $headers = $rowData;
                    $firstRow = false;
                } else {
                    // Associer les valeurs aux en-têtes
                    $data[] = array_combine($headers, $rowData);
                }
            }

            return $data;

        } catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }
}
