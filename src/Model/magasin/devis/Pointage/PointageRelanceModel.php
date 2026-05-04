<?php

namespace App\Model\magasin\devis\Pointage;

use App\Mapper\Magasin\Devis\Pointage\PointageRelanceMapper;
use App\Model\Informix\InsertQueryBuilder;
use App\Model\Informix\UpdateQueryBuilder;
use App\Model\Model;

class PointageRelanceModel extends Model
{
    /**
     * Methode pour enregistrer les données du formulaire 
     * de pointage relance dans la table pointage_relance 
     *  dans la base de donnée
     *
     * @param array $data
     * @return void
     */
    public function enregistrerPointageRelance($pointageRelanceEntity): void
    {
        // Convertir le DTO en tableau associatif pour l'insertion
        $donnees = PointageRelanceMapper::toArrayPointageRelance($pointageRelanceEntity);

        // Convertir vers l'encodage Informix (ISO-8859-1)
        $donnees = $this->convertirVersInformix($donnees);

        // Construire la requête d'insertion et l'exécuter
        $builder = new InsertQueryBuilder("{$this->dbIrium}:Informix.pointage_relance");
        $builder->setData($donnees);
        $result = $builder->build();

        // Exécuter la requête d'insertion
        // S'assurer que la connexion est ouverte
        $this->connect->connect();
        try {
            $this->connect->executeQuery($result['sql'], $result['params']);
        } finally {
            // ne fermez ici que si vous êtes sûr que c'est la dernière opération
            $this->connect->close();
        }
    }

    /**
     * MOdification du statut relance dans la 
     * table devis_soumis_a_validation_neg
     *
     * @param array $data
     * @return void
     */
    public function updateDevis($pointageRelanceEntity, $numeroVersionDevis)
    {
        $donnees = PointageRelanceMapper::toArrayUpdatePointageRelance();

        // Convertir vers l'encodage Informix (ISO-8859-1)
        $donnees = $this->convertirVersInformix($donnees);

        $updateBuilder = new UpdateQueryBuilder("{$this->dbIrium}:Informix.devis_soumis_a_validation_neg");

        // Définir les données à mettre à jour
        $updateBuilder->setData($donnees);

        // Ajouter les conditions WHERE
        $updateBuilder->where('numero_devis', $pointageRelanceEntity->getNumeroDevis());
        $updateBuilder->where('numero_version', $numeroVersionDevis);

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

    /**
     * Récupère le numéro version dans la table pointage_relance
     *
     * @param string $numeroDevis
     * @param string $codeSociete
     * @return integer
     */
    public function getNumeroVersionPointageRelance(string $numeroDevis, string $codeSociete): int
    {
        $statement = "SELECT FIRST 1 MAX(numero_version) as version 
        FROM {$this->dbIrium}:Informix.pointage_relance pr 
        WHERE pr.numero_devis = '$numeroDevis'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), 'version')[0] ?? 0;
    }

    /**
     * Récupére le numéro vérsion dans la table devis_soumis_a_validation_neg
     *
     * @param string $numeroDevis
     * @param string $codeSociete
     * @return integer
     */
    public function getNumeroVersionDevis(string $numeroDevis, string $codeSociete): int
    {
        $statement = "SELECT FIRST 1 MAX(numero_version) as version 
        FROM {$this->dbIrium}:Informix.devis_soumis_a_validation_neg dneg 
        WHERE dneg.numero_devis = '$numeroDevis'
        ";

        $result = $this->connect->executeQuery($statement);

        $data = $this->connect->fetchResults($result);

        return array_column($this->convertirEnUtf8($data), 'version')[0] ?? 0;
    }


    public function getRelancePourStop(string $numeroDevis, string $codeSociete): array
    {
        $this->connect->connect();

        try {
            $statement = "SELECT statut_dw, statut_bc, stop_progression_global, motif_stop_global
                    FROM {$this->dbIrium}:Informix.devis_soumis_a_validation_neg dneg
                    WHERE dneg.numero_devis = '$numeroDevis' 
                    AND dneg.numero_version = (SELECT MAX(numero_version) FROM {$this->dbIrium}:Informix.devis_soumis_a_validation_neg WHERE numero_devis = '$numeroDevis')
            ";

            $result = $this->connect->executeQuery($statement);
            $rows = $this->connect->fetchScalarResults($result);

            return $rows;
        } finally {
            $this->connect->close();
        }
    }
}
