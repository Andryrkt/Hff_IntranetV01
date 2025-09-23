<?php

require 'vendor/autoload.php';

use Doctrine\ORM\Tools\SchemaTool;

// Assurez-vous d'avoir une instance de votre EntityManager
$entityManager = require __DIR__ . DIRECTORY_SEPARATOR . 'doctrineBootstrap.php';

// Initialisez le SchemaTool de Doctrine
$schemaTool = new SchemaTool($entityManager);

// Récupérez toutes les métadonnées de vos entités
$metadata = $entityManager->getMetadataFactory()->getAllMetadata();

// Obtenez les requêtes SQL nécessaires pour la mise à jour du schéma
$sqls = $schemaTool->getUpdateSchemaSql($metadata);

// Affichez les requêtes SQL
foreach ($sqls as $sql) {
    echo $sql . ";\n";
}

// Commentez la ligne qui exécute la mise à jour
// $schemaTool->updateSchema($metadata);

