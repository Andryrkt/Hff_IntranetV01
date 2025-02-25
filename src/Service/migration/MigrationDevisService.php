<?php

namespace App\Service\migration;


use App\Model\migration\MigrationDevisModel;
use App\Model\dit\migration\MigrationDevisModels;
use App\Service\TableauEnStringService;
use Symfony\Component\Console\Helper\ProgressBar;

class MigrationDevisService
{
    
    public function migrationDevis($output)
    {
        $dataDebuts = $this->assignationValue( $this->recupData());
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
        $numDevis = ['17099540','17099541'];
        $migrationDevisModel = new MigrationDevisModels();
        return $migrationDevisModel->recupDevisSoumisValidation(TableauEnStringService::TableauEnString(',',$numDevis));
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
                'statut' => '',
                'dateHeureSoumission' => (new \DateTime())->format('Y-m-d H:i:s')?? '',
                'montantForfait' => 0.00,
                'natureOperation' =>$data['nature_operation']?? '',
                'devisVenteOuForfait' => '',
                'devise' => $data['devise']??'',
                'montantVente' => 0.00
            ];
        }
        return $donners;
    }
}