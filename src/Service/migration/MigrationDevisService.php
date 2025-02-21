<?php

namespace App\Service\migration;

use App\Model\migration\MigrationDevisModel;
use Symfony\Component\Console\Helper\ProgressBar;

class MigrationDevisService
{
    
    public function migrationDevis($output)
    {
        $dataDebuts = $this->assignationValue($this->recupData());
        $total = count($dataDebuts);
        $progressBar = new ProgressBar($output, $total);
        $progressBar->start();
        foreach ($dataDebuts as $dataDebut) {
           $this->insertData($dataDebut);
           // Avancer la barre de progression
           $progressBar->advance();
        }
        $output->writeln("\nNombre de résultats : " . $total);
        $progressBar->finish();
        $output->writeln("\nTerminé !");
    }

    public function recupData()
    {
        $nomTableDebut= '';
        $nomDesColonnes = "tata,toto,titi";
        $condition = [];
        $migrationDataModel = new MigrationDevisModel($nomTableDebut);
        return  $migrationDataModel->selectDevisMIgration($condition, $nomDesColonnes);
    }

    public function insertData(array $dataDebut = [])
    {
        $nomTableArriver = 'devis_soumis_a_validation';
        $migrationDataModel = new MigrationDevisModel($nomTableArriver, $dataDebut);
        $migrationDataModel->insertDevisMigration();
    }

    public function assignationValue($datas)
    {
        $donners = [];
        foreach ($datas as $data) {
            $donners[] = [
                'numeroDit' =>'',
                'numeroDevis' => '',
                'numeroItv' =>'',
                'nombreLigneItv' => '',
                'montantItv' => 0.00,
                'numeroVersion' => 1,
                'montantPiece' => 0.00,
                'montantMo' => 0.00,
                'montantAchatLocaux' => 0.00,
                'montantFraisDivers' => 0.00,
                'montantLubrifiants' => 0.00,
                'libelleItv' => '',
                'statut' => '',
                'dateHeureSoumission' => new \DateTime(),
                'montantForfait' => 0.00,
                'natureOperation' =>'',
                'devisVenteOuForfait' => '',
                'devise' => '',
                'montantVente' => 0.00
            ];
        }
        return $donners;
    }
}