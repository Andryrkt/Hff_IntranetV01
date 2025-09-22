<?php

namespace App\Service\migration;

use App\Model\dit\migration\MigrationDataModel;

class MigrationDataDitService
{
    private MigrationDataModel $migrationDataModel;

    public function __construct(MigrationDataModel $migrationDataModel)
    {
        $this->migrationDataModel = $migrationDataModel;
    }

    public function migrationDataDit($output)
    {
        $ancienDits = $this->migrationDataModel->getDitMigrer();
        dd($ancienDits);
        if (empty($ancienDits)) {
            return "Aucune donnée à insérer.";
        }

        foreach ($ancienDits as $ancienDit) {
            $this->migrationDataModel->insertDit($ancienDit);
        }
        return "Insertion terminée avec succès.";
    }
}
