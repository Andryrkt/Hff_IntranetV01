<?php

namespace App\Service\migration;


use Exception;
use App\Service\TableauEnStringService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Model\migration\MigrationDevisModel;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use App\Model\dit\migration\MigrationDevisModels;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrationDevisService
{
    


public function migrationDevis($input, $output)
{
    $io = new SymfonyStyle($input, $output);
    
    // Récupération et transformation des données
    $dataDebuts = $this->assignationValue($this->recupData());
    $total = count($dataDebuts);
    
    if ($total === 0) {
        $io->warning("Aucune donnée à traiter.");
        return;
    }
    
    // Affichage du titre de l'insertion
    $io->section("\033[32m Début de l'insertion des données... \033[0m"); // Vert

    $progressBar = new ProgressBar($output, $total);
    $progressBar->start();

    foreach ($dataDebuts as $dataDebut) {
        $this->insertData($dataDebut);
        $progressBar->advance();
    }
    
    $progressBar->finish();
    $output->writeln("\n\033[32m Insertion terminée ! \033[0m"); // Vert
    $output->writeln("\nNombre de résultats : $total");

    // Affichage du titre de la mise à jour
    $io->section("\033[34m Début de la mise à jour des données... \033[0m"); // Bleu

    $progressBar = new ProgressBar($output, $total);
    $progressBar->start();

    foreach ($dataDebuts as $dataDebut) {
        $this->updateData($dataDebut);
        $progressBar->advance();
    }

    $progressBar->finish();
    $output->writeln("\n\033[34m Mise à jour terminée ! \033[0m"); // Bleu
    $output->writeln("\nNombre de résultats : $total");

    $io->success("Migration terminée avec succès !");
}


    public function recupData()
    {
        $numDevis = ['17099888', '17099890'];
        $migrationDevisModel = new MigrationDevisModels();
        return  $migrationDevisModel->recupDevisSoumisValidation(TableauEnStringService::TableauEnString(',',$numDevis));
    }

    public function insertData(array $dataDebut = [])
    {
        $nomTableArriver = 'devis_soumis_a_validation';
        $migrationDataModel = new MigrationDevisModel($nomTableArriver, $dataDebut);
        $migrationDataModel->insertDevisMigration();
    }

    public function updateData($datas)
    {

        $nomTableupdate = 'demande_intervention';
    
        // Vérifier si $datas n'est pas vide
        if (empty($datas)) {
            throw new \Exception("Aucune donnée à mettre à jour.");
        }
        // Instancier une seule fois MigrationDevisModel
        $migrationDeviModel = new MigrationDevisModel($nomTableupdate);


        
            $tabUpdate = ['statut_devis' => $datas['statut']];
            $condition = [
                'numero_devis_rattache' => $datas['numeroDevis'],
                'num_migr' => 4
            ];
        
            if (!empty($tabUpdate)) {
                $migrationDeviModel->updateGeneralise($nomTableupdate, $tabUpdate, $condition);
            }
        
        
    }
    

    public function assignationValue($datas)
    {
        $statuts = $this->affectationStatut();

        foreach ($datas as $i => $dataItem) { 
            $datas[$i]['statut'] = $statuts[$dataItem['numero_devis']]['statut'] ?? '';
        }

        $donners = [];
        foreach ($datas as $data) {
            $donners[] = [
                'numeroDit' =>$data['numero_dit']?? '',
                'numeroDevis' => $data['numero_devis']?? '',
                'numeroItv' =>$data['numero_itv']?? '',
                'nombreLigneItv' => $data['nombre_ligne']?? '',
                'montantItv' => $data['montant_itv']?? '',
                'numeroVersion' => 1,
                'montantPiece' => $data['montant_piece']?? '',
                'montantMo' => $data['montant_mo']?? '',
                'montantAchatLocaux' => $data['montant_achats_locaux']?? '',
                'montantFraisDivers' => $data['montant_divers']?? '',
                'montantLubrifiants' => $data['montant_lubrifiants']?? '',
                'libellelItv' => $data['libell_itv']?? '',
                'statut' => $data['statut'],
                'dateHeureSoumission' => (new \DateTime())->format('Y-m-d H:i:s')?? '',
                'montantForfait' => 0.00,
                'natureOperation' =>$data['nature_operation']?? '',
                'devisVenteOuForfait' => '',
                'devise' => $data['devise']??'',
                'montantVente' => 0.00,
                'num_migr' => 4
            ];
        }
        return $donners;
    }



private function  affectationStatut()
{
    $dataExcel = $this->recuperationDonnerExcel();
    $filteredDatas = array_filter($dataExcel, function ($entry) {
        return $entry["numero_devis_rattache"] !== "#N/A";
    });
    $donners = [];
    $statut = '';
    
        foreach ($filteredDatas as $filteredData) {
            $condition1 = ($filteredData['devis_position'] == 'EC' ||  $filteredData['devis_position'] == 'TE')&& $filteredData['numero_or'] == '#N/A';
            $condition2 = $filteredData['devis_position'] == 'TR' && $filteredData['numero_or'] <> '#N/A' && $filteredData['nbr_ligne_or'] <> '#N/A' && $filteredData['or_position'] = 'EC' && $filteredData['etat_allocation'] <> '#N/A';
            $condition3 =  $filteredData['devis_position'] == 'TR' && $filteredData['numero_or'] <> '#N/A' && $filteredData['nbr_ligne_or'] <> '#N/A' && $filteredData['or_position'] = 'EC' && $filteredData['etat_allocation'] <> 'alloue reserve livree';
            if($condition1) {
                $statut= 'Soumis à validation';
            } else if($condition2) {
                $statut= 'Validé';
            } else if($condition3) {
                $statut= 'Validé';
            } else {
                $statut ='';
            }

            $donners[$filteredData['numero_devis_rattache']] = [
                'statut' => $statut,
            ];
        }
    return $donners;
}
    
    private function recuperationDonnerExcel()
    {
        
        try {
            // Demander à l'utilisateur d'entrer le chemin du fichier
            $filePath = readline("Entrez le chemin du fichier Excel (.xlsx) : ");
        
            // Vérifier si le fichier existe
            if (!file_exists($filePath)) {
                throw new Exception("Le fichier '$filePath' n'existe pas.");
            }
            
            // Charger le fichier Excel
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
                $sheetIndex = (int)readline("Entrez le numéro de la feuille (0 pour la première feuille) : ");
                if (isset($sheetNames[$sheetIndex])) {
                    $sheet = $spreadsheet->getSheet($sheetIndex);
                } else {
                    throw new Exception("Le numéro de feuille $sheetIndex n'existe pas.");
                }
            } else {
                $sheetName = readline("Entrez le nom de la feuille : ");
                $sheet = $spreadsheet->getSheetByName($sheetName);
                if ($sheet === null) {
                    throw new Exception("La feuille '$sheetName' n'existe pas.");
                }
            }
        
            // Demander à l'utilisateur à partir de quelle ligne commencent les données
            $startRow = (int)readline("À partir de quelle ligne commencent les données ? (1 pour la première ligne) : ");
            if ($startRow < 1) {
                throw new Exception("Le numéro de ligne de début doit être supérieur ou égal à 1.");
            }
        
            // Demander la colonne de départ (ex: A, B, C, etc.)
            $startColumnLetter = strtoupper(readline("À partir de quelle colonne commencent les données ? (ex: A, B, C) : "));
            $startColumnIndex = Coordinate::columnIndexFromString($startColumnLetter);
        
            // Initialiser un tableau pour stocker les données
            $data = [];
            $headers = [];
            $firstRow = true;
            
            foreach ($sheet->getRowIterator($startRow) as $row) {
                $rowData = [];
                foreach ($row->getCellIterator($startColumnLetter) as $cell) {
                    $rowData[] = $cell->getValue();
                }
        
                if ($firstRow) {
                    $headers = $rowData;
                    $firstRow = false;
                } else {
                    // Vérifier que le nombre de colonnes correspond
                    if (count($rowData) == count($headers)) {
                        $data[] = array_combine($headers, $rowData);
                    } else {
                        echo "⚠️ Attention : La ligne " . $row->getRowIndex() . " a un nombre de colonnes différent de l'en-tête et sera ignorée.\n";
                    }
                }
            }
        
            // // Afficher les données récupérées
            // echo "Données récupérées :\n";
            // print_r($data);
        return $data;
        
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
            echo "Erreur lors de la lecture du fichier : " . $e->getMessage();
        } catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }
        

    }
}