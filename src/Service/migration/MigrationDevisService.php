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
        $numDevis = ['17097226','17097225','17097227','17097221','17097278','17097373','17097370','17097368','17097367','17097365','17097481','17097480','17097482','17097488','17097540','17097583','17097685','17097772','17097775','17097890','17097894','17097893','17097892','17097891','17097922','17097932','17097985','17098066','17098072','17098133','17098134','17098136','17098131','17098130','17098127','17098128','17098129','17098140','17098139','17098153','17098152','17098157','17098158','17098274','17098273','17098272','17098271','17098380','17098381','17098382','17098415','17098460','17098536','17098535','17098534','17098560','17098546','17098581','17098600','17098656','17098696','17098699','17098714','17098749','17098783','17098784','17098788','17098798','17098801','17098803','17098804','17098808','17098811','17098810','17098809','17098815','17098816','17098824','17098825','17098827','17098828','17098829','17098830','17098843','17098904','17098919','17098946','17098981','17099030','17099029','17099027','17099026','17099025','17099024','17099023','17099022','17099021','17099020','17099019','17099031','17099044','17099043','17099177','17099262','17099266','17099271','17099333','17099349','17099360','17099379','17099413','17099424','17099426','17099427','17099437','17099436','17099435','17099434','17099433','17099432','17099431','17099430','17099441','17099454','17099455','17099456','17099452','17099453','17099451','17099457','17099481','17099479','17099483','17099492','17099635','17099507','17099503','17099584','17099510','17099513','17099523','17099524','17099548','17099549','17099550','17099551','17099552','17099553','17099554','17099532','17099534','17099535','17099536','17099538','17099539','17099540','17099541','17099542','17099544','17099543','17099555','17099556','17099557','17099558','17099560','17099565','17099567','17099566','17099568','17099581','17099569','17099583','17099579','17099593','17099580','17099587','17099588','17099594','17099595','17099597','17099609','17099600','17099610','17099620','17099621','17099737','17099622','17099636','17099634','17099623','17099619','17099624','17099643','17099648','17099658','17099657','17099660','17099655','17099666','17099662','17099676','17099665','17099671','17099673','17099672','17099682','17099680','17099681','17099683','17099691','17099696','17099697','17099702','17099700','17099710','17099705','17099706','17099708','17099714','17099713','17099712','17099711','17099716','17099719','17099717','17099733','17099732','17099734','17099759','17099758','17099727','17099728','17099729','17099730','17099731','17099735','17099738','17099740','17099749','17099742','17099741','17099748','17099854','17099750','17099753','17099754','17099755','17099771','17099768','17099769','17099764','17099765','17099766','17099767','17099770','17099777','17099778','17099779','17099774','17099783','17099789','17099785','17099787','17099781','17099843','17099809','17099805','17099803','17099800','17099817','17099825','17099857','17099844','17099845','17099824','17099828','17099826','17099829','17099831','17099846','17099832','17099833','17099834','17099835','17099836','17099837','17099838','17099839','17099848','17099849','17099882','17099883','17099850','17099851','17099847','17099856','17099861','17099860','17099859','17099852','17099871','17099858','17099873','17099865','17099868','17099866','17099867','17099870','17099872','17099874','17099881','17099880','17099886','17099887','17099885','17099888','17099890','17099891','17099892','17099865','17099868','17099866','17099867','17099870','17099864','17099872','17099874','17099881','17099879','17099880','17099876','17099877','17099878','17099886','17099887','17099885','17099888','17099890','17099891','17099892'];
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
        // $numDevis = ['17097226','17097225','17097227','17097221','17097278','17097373','17097370','17097368','17097367','17097365','17097481','17097480','17097482','17097488','17097540','17097583','17097685','17097772','17097775','17097890','17097894','17097893','17097892','17097891','17097922','17097932','17097985','17098066','17098072','17098133','17098134','17098136','17098131','17098130','17098127','17098128','17098129','17098140','17098139','17098153','17098152','17098157','17098158','17098274','17098273','17098272','17098271','17098380','17098381','17098382','17098415','17098460','17098536','17098535','17098534','17098560','17098546','17098581','17098600','17098656','17098696','17098699','17098714','17098749','17098783','17098784','17098788','17098798','17098801','17098803','17098804','17098808','17098811','17098810','17098809','17098815','17098816','17098824','17098825','17098827','17098828','17098829','17098830','17098843','17098904','17098919','17098946','17098981','17099030','17099029','17099027','17099026','17099025','17099024','17099023','17099022','17099021','17099020','17099019','17099031','17099044','17099043','17099177','17099262','17099266','17099271','17099333','17099349','17099360','17099379','17099413','17099424','17099426','17099427','17099437','17099436','17099435','17099434','17099433','17099432','17099431','17099430','17099441','17099454','17099455','17099456','17099452','17099453','17099451','17099457','17099481','17099479','17099483','17099492','17099635','17099507','17099503','17099584','17099510','17099513','17099523','17099524','17099548','17099549','17099550','17099551','17099552','17099553','17099554','17099532','17099534','17099535','17099536','17099538','17099539','17099540','17099541','17099542','17099544','17099543','17099555','17099556','17099557','17099558','17099560','17099565','17099567','17099566','17099568','17099581','17099569','17099583','17099579','17099593','17099580','17099587','17099588','17099594','17099595','17099597','17099609','17099600','17099610','17099620','17099621','17099737','17099622','17099636','17099634','17099623','17099619','17099624','17099643','17099648','17099658','17099657','17099660','17099655','17099666','17099662','17099676','17099665','17099671','17099673','17099672','17099682','17099680','17099681','17099683','17099691','17099696','17099697','17099702','17099700','17099710','17099705','17099706','17099708','17099714','17099713','17099712','17099711','17099716','17099719','17099717','17099733','17099732','17099734','17099759','17099758','17099727','17099728','17099729','17099730','17099731','17099735','17099738','17099740','17099749','17099742','17099741','17099748','17099854','17099750','17099753','17099754','17099755','17099771','17099768','17099769','17099764','17099765','17099766','17099767','17099770','17099777','17099778','17099779','17099774','17099783','17099789','17099785','17099787','17099781','17099843','17099809','17099805','17099803','17099800','17099817','17099825','17099857','17099844','17099845','17099824','17099828','17099826','17099829','17099831','17099846','17099832','17099833','17099834','17099835','17099836','17099837','17099838','17099839','17099848','17099849','17099882','17099883','17099850','17099851','17099847','17099856','17099861','17099860','17099859','17099852','17099871','17099858','17099873','17099865','17099868','17099866','17099867','17099870','17099872','17099874','17099881','17099880','17099886','17099887','17099885','17099888','17099890','17099891','17099892','17099865','17099868','17099866','17099867','17099870','17099864','17099872','17099874','17099881','17099879','17099880','17099876','17099877','17099878','17099886','17099887','17099885','17099888','17099890','17099891','17099892'];

        // $statuts = $this->affectationStatut();

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