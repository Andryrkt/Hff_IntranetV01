<?php


namespace App\Service\migration\magasin;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

trait devisMagasinMigrationTrait
{
    // Exemple d'utilisation
    private function getDataCsv()
    {
        try {
            $filePath = "C:\wamp64\www\Upload\magasin\migrations\statut devis à migrer test.csv";
            $tableaus = $this->lireCsvPropre($filePath, ';', 'UTF-8', true);

            $devisVp = [];
            $devisVd = [];
            $numeroDevisVp = [];
            $numeroDevisVd = [];
            foreach ($tableaus as $value) {
                if (!empty($value['statut_validation_devis_agence'])) {
                    $devisVd[$value['devis']] = $value;
                    $numeroDevisVd[] = $value['devis'];
                }
                $devisVp[$value['devis']] = $value;
                $numeroDevisVp[] = $value['devis'];
            }

            // Affichage pour test
            return [$devisVp, $devisVd, $numeroDevisVp, $numeroDevisVd];

            // Exemple : accéder à une valeur
            // echo $tableau[$i]['nom']; // première ligne, colonne "nom"

        } catch (\Exception $e) {
            echo 'Erreur : ' . $e->getMessage();
        }
    }


    /**
     * Lit un fichier CSV proprement : UTF-8, trim sur tout, pas de "b" devant les chaînes
     * 
     * @param string $fichierChemin Chemin complet vers le fichier CSV
     * @param string $separateur    Séparateur des colonnes (par défaut ',')
     * @param bool   $entetes       Si true, la première ligne est utilisée comme clés associatives
     * @return array Tableau contenant les données du CSV
     * @throws Exception Si le fichier n'existe pas ou n'est pas lisible
     */
    function lireCsvPropre(
        string $fichierChemin,
        string $separateur = ';',
        bool $entetes = true
    ): array {
        if (!file_exists($fichierChemin)) {
            throw new \Exception("Fichier introuvable : $fichierChemin");
        }

        $donnees = [];
        $handle = fopen($fichierChemin, 'r');
        if ($handle === false) {
            throw new \Exception("Impossible d'ouvrir le fichier");
        }

        // === Gérer le BOM UTF-8 si présent ===
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle); // Pas de BOM → on remet au début
        }

        // === Lire les en-têtes si demandé ===
        $enTetes = [];
        if ($entetes) {
            $enTetesBruts = fgetcsv($handle, 0, $separateur);
            if ($enTetesBruts === false || $enTetesBruts === null) {
                fclose($handle);
                return [];
            }
            // Trim et conversion en UTF-8 propre
            foreach ($enTetesBruts as $header) {
                $enTetes[] = trim(mb_convert_encoding($header, 'UTF-8', 'Windows-1252'));
            }
        }

        // === Lire les lignes ===
        while (($ligne = fgetcsv($handle, 0, $separateur)) !== false) {
            if (empty($ligne) || (count($ligne) === 1)) {
                continue; // ligne vide
            }

            if ($entetes) {
                $row = [];
                foreach ($enTetes as $index => $cle) {
                    $valeur = $ligne[$index] ?? '';
                    // Conversion propre + trim
                    $valeur = mb_convert_encoding($valeur, 'UTF-8', 'Windows-1252');
                    $row[$cle] = trim($valeur);
                }
                $donnees[] = $row;
            } else {
                // Sans en-têtes : tableau indexé avec trim
                $donnees[] = array_map(function ($v) {
                    return trim(mb_convert_encoding($v, 'UTF-8', 'Windows-1252'));
                }, $ligne);
            }
        }

        fclose($handle);
        return $donnees;
    }

    // ====================================================================
    private function getDataExcel()
    {
        try {
            $fichier = 'C:\wamp64\www\Upload\magasin\migrations\DEVIS A MIGRER DEMAT VENTE NEGOCE.xlsx';
            $donnees = $this->recuperationDonnerExcel();

            $devisVp = [];
            $devisVd = [];
            $numeroDevisVp = [];
            $numeroDevisVd = [];
            foreach ($donnees as $value) {
                if (!empty($value['STATUT DEVIS SI AGENCE'])) {
                    $devisVd[$value['numero_devis']] = $value;
                    $numeroDevisVd[] = $value['numero_devis'];
                }
                $devisVp[$value['numero_devis']] = $value;
                $numeroDevisVp[] = $value['numero_devis'];
            }

            // Affichage pour test
            return [$devisVp, $devisVd, $numeroDevisVp, $numeroDevisVd];



            return $donnees;
        } catch (\Exception $e) {
            echo 'Erreur : ' . $e->getMessage();
        }
    }

    private function recuperationDonnerExcel()
    {
        try {
            // Demander à l'utilisateur d'entrer le chemin du fichier
            $filePath = readline("Entrez le chemin du fichier Excel (.xlsx) : ");
            // Vérifier si le fichier existe
            if (!file_exists($filePath)) {
                throw new \Exception("Le fichier '$filePath' n'existe pas.");
            }
            // Charger le fichier Excel
            echo "Chargement du fichier Excel...\n";
            $spreadsheet = IOFactory::load($filePath);

            // Lister les noms des feuilles disponibles
            $sheetNames = $spreadsheet->getSheetNames();
            echo "Feuilles disponibles dans le fichier Excel :\n";
            foreach ($sheetNames as $index => $name) {
                echo "$index : $name\n";
            }

            // Demander à l'utilisateur d'entrer le nom ou le numéro de la feuille
            $choice = readline("Voulez-vous entrer un [N]uméro ou un [NOM] de feuille ? (N/NOM) : ");
            $sheet = null;
            if (strtoupper($choice) === 'N') {
                $sheetIndex = (int)readline("Entrez le numéro de la feuille (0 pour la première feuille) : ");
                if (isset($sheetNames[$sheetIndex])) {
                    $sheet = $spreadsheet->getSheet($sheetIndex);
                } else {
                    throw new \Exception("Le numéro de feuille $sheetIndex n'existe pas.");
                }
            } else {
                $sheetName = readline("Entrez le nom de la feuille : ");
                $sheet = $spreadsheet->getSheetByName($sheetName);
                if ($sheet === null) {
                    throw new \Exception("La feuille '$sheetName' n'existe pas.");
                }
            }

            // Demander à l'utilisateur à partir de quelle ligne commencent les données
            $startRow = (int)readline("À partir de quelle ligne commencent les données ? (1 pour la première ligne) : ");
            if ($startRow < 1) {
                throw new \Exception("Le numéro de ligne de début doit être supérieur ou égal à 1.");
            }

            // Demander la colonne de départ (ex: A, B, C, etc.)
            $startColumnLetter = strtoupper(readline("À partir de quelle colonne commencent les données ? (ex: A, B, C) : "));
            $startColumnIndex = Coordinate::columnIndexFromString($startColumnLetter);

            // Calculer le nombre total de lignes à traiter
            $totalRows = $sheet->getHighestRow() - $startRow + 1;
            echo "\nTraitement de $totalRows lignes...\n";

            // Initialiser un tableau pour stocker les données
            $data = [];
            $headers = [];
            $firstRow = true;
            $currentRow = 0;

            foreach ($sheet->getRowIterator($startRow) as $row) {
                $rowData = [];
                foreach ($row->getCellIterator($startColumnLetter) as $cell) {
                    $rowData[] = $cell->getCalculatedValue();
                }

                if ($firstRow) {
                    $headers = $rowData;
                    $firstRow = false;
                    echo "En-têtes récupérés : " . implode(", ", $headers) . "\n\n";
                } else {
                    // Vérifier que le nombre de colonnes correspond
                    if (count($rowData) == count($headers)) {
                        $data[] = array_combine($headers, $rowData);
                    } else {
                        echo "⚠️ Attention : La ligne " . $row->getRowIndex() . " a un nombre de colonnes différent de l'en-tête et sera ignorée.\n";
                    }
                }

                // Afficher la progression
                $currentRow++;
                $percentage = round(($currentRow / $totalRows) * 100);
                $barLength = 50;
                $filledLength = round(($percentage / 100) * $barLength);
                $bar = str_repeat("█", $filledLength) . str_repeat("░", $barLength - $filledLength);

                echo "\rProgression: [$bar] $percentage% ($currentRow/$totalRows lignes)";
                flush();
            }

            echo "\n✓ Récupération terminée avec succès ! " . count($data) . " lignes de données récupérées.\n";

            return $data;
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            echo "\n❌ Erreur lors de la lecture du fichier : " . $e->getMessage() . "\n";
        } catch (\Exception $e) {
            echo "\n❌ Erreur : " . $e->getMessage() . "\n";
        }
    }
}
