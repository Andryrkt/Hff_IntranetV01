<?php

namespace App\Model\magasin\devis\Pointage;

use App\Dto\Magasin\Devis\Pointage\EnvoyerAuClientDto;
use App\Mapper\Magasin\Devis\Pointage\PointageMapper;
use App\Model\Informix\UpdateQueryBuilder;

use App\Model\Model;

class PointageModel extends Model
{
    public function updatePointage(EnvoyerAuClientDto $dto)
    {
        $donnees = PointageMapper::toArrayEnvoyerAuClient($dto);

        $updateBuilder = new UpdateQueryBuilder("{$this->dbIrium}:Informix.devis_soumis_a_validation_neg");

        // Définir les données à mettre à jour
        $updateBuilder->setData($donnees);

        // Ajouter les conditions WHERE
        $updateBuilder->where('numero_devis', $dto->numeroDevis);
        $updateBuilder->where('numero_version', $dto->numeroVersion);

        // Changer l'opérateur des conditions (optionnel)
        $updateBuilder->setConditionOperator('AND');

        // Construire et exécuter la requête
        try {
            $result = $updateBuilder->build();

            $this->connect->connect();
            try {
                $this->connect->executeQuery($result['sql'], $result['params']);
            } finally {
                $this->connect->close();
            }
        } catch (\Exception $e) {
            // Vous pouvez logger l'erreur ici
            throw $e;
        }
    }
}
