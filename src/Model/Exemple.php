<?php



// Informations de connexion à la base de données

use App\Model\DatabaseInformix;

$hostname = 'IPS_HFFPROD';
$port = '9088';
$database = 'ips_hffprod@ol_iriumprod';
$username = 'informix';
$password = 'informix';

// Créer une instance de la classe Database
$database = new DatabaseInformix($hostname, $port, $database, $username, $password);

// Exemple de requête
$query = "SELECT * FROM MAT_MAT ";
$result = $database->query($query);

// Manipuler les résultats de la requête
if ($result) {
    foreach ($result as $row) {
        print_r($row);
    }
} else {
    echo "Erreur lors de l'exécution de la requête";
}

// Fermer la connexion à la base de données
$database->close();
