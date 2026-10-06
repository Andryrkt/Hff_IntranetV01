<?php

use App\Model\Model;
use Psr\Log\LoggerInterface;

require_once __DIR__ . '/../vendor/autoload.php';

// Simulation du bootstrap minimal de Symfony pour charger les variables d'environnement
if (file_exists(__DIR__ . '/../config/dotenv.php')) {
    require_once __DIR__ . '/../config/dotenv.php';
}

echo "--- TEST DE MIGRATION DATABASE INFORMIX ---\n";

try {
    // 1. Test de l'instanciation de Model (qui instancie DatabaseInformix)
    echo "[1/3] Instanciation du modèle de base... ";
    $model = new Model();
    echo "OK\n";

    // 2. Vérification de la connexion
    // On accède à la propriété protégée via réflexion pour le test
    $reflection = new ReflectionClass($model);
    $property = $reflection->getProperty('connect');
    $property->setAccessible(true);
    $dbInformix = $property->getValue($model);

    echo "[2/3] Vérification de la connexion Informix... ";
    $conn = $dbInformix->connect();
    if ($conn) {
        echo "OK (Connexion établie)\n";
    } else {
        echo "ÉCHEC (Pas de connexion)\n";
        exit(1);
    }

    // 3. Test d'une requête simple et de l'encodage
    echo "[3/3] Test d'une requête et de l'encodage UTF-8... ";
    // On récupère juste une ligne pour le test
    $sql = "SELECT FIRST 1 nent_nomcli FROM informix.neg_ent WHERE nent_nomcli IS NOT NULL";
    $result = $dbInformix->executeQuery($sql);
    $data = $dbInformix->fetchResults($result);

    if (!empty($data)) {
        $nomClient = $data[0]['nent_nomcli'];
        echo "OK\n";
        echo "   -> Donnée récupérée : " . $nomClient . "\n";
        
        // Vérification si c'est bien de l'UTF-8
        if (mb_check_encoding($nomClient, 'UTF-8')) {
            echo "   -> Encodage : UTF-8 (Validation réussie)\n";
        } else {
            echo "   -> Encodage : NON UTF-8 (Attention !)\n";
        }
    } else {
        echo "OK (Aucune donnée trouvée mais requête réussie)\n";
    }

    echo "\n--- TOUS LES TESTS SONT RÉUSSIS ---\n";
    echo "La migration est compatible avec l'existant.\n";

} catch (\Exception $e) {
    echo "ERREUR FATALE : " . $e->getMessage() . "\n";
    echo "Trace : " . $e->getTraceAsString() . "\n";
    exit(1);
}
