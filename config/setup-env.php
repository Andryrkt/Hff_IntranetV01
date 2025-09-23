<?php

/**
 * Script d'aide pour la configuration sécurisée
 * 
 * Ce script aide à créer le fichier .env à partir du modèle
 * et vérifie que toutes les variables nécessaires sont présentes.
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php';

use Dotenv\Dotenv;

echo "=== Configuration Sécurisée Hffintranet ===\n\n";

// Vérifier si le fichier .env existe déjà
$envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
$envExampleFile = __DIR__ . DIRECTORY_SEPARATOR . 'env.example';

if (file_exists($envFile)) {
    echo "⚠️  Le fichier .env existe déjà.\n";
    echo "Voulez-vous le remplacer ? (y/N): ";
    $response = trim(fgets(STDIN));

    if (strtolower($response) !== 'y') {
        echo "Configuration annulée.\n";
        exit(0);
    }
}

// Copier le fichier d'exemple
if (!file_exists($envExampleFile)) {
    echo "❌ Erreur: Le fichier config/env.example n'existe pas.\n";
    exit(1);
}

if (copy($envExampleFile, $envFile)) {
    echo "✅ Fichier .env créé avec succès.\n\n";
} else {
    echo "❌ Erreur lors de la création du fichier .env.\n";
    exit(1);
}

// Charger les variables d'environnement
$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Variables obligatoires à vérifier
$requiredVars = [
    'DB_PASSWORD_SQLSERV' => 'Mot de passe de la base de données principale',
    'DB_PASSWORD_SQLSERV_4' => 'Mot de passe de la base Dote4',
    'DB_PASSWORD_INFORMIX' => 'Mot de passe de la base Informix',
    'MAILER_PASSWORD' => 'Mot de passe du serveur email',
    'LDAP_HOST' => 'Serveur LDAP',
    'LDAP_DOMAIN' => 'Domaine LDAP',
];

echo "Vérification des variables obligatoires:\n";
echo "========================================\n";

$missingVars = [];
$defaultVars = [];

foreach ($requiredVars as $var => $description) {
    $value = $_ENV[$var] ?? null;

    if (empty($value) || strpos($value, 'your_') === 0 || strpos($value, 'password_here') !== false) {
        $missingVars[] = $var;
        echo "❌ $var: $description - VALEUR PAR DÉFAUT DÉTECTÉE\n";
    } else {
        echo "✅ $var: $description - CONFIGURÉ\n";
    }
}

echo "\n";

if (!empty($missingVars)) {
    echo "⚠️  ATTENTION: Les variables suivantes doivent être configurées:\n";
    foreach ($missingVars as $var) {
        echo "   - $var\n";
    }
    echo "\nÉditez le fichier .env et remplacez les valeurs par défaut.\n";
} else {
    echo "✅ Toutes les variables obligatoires sont configurées.\n";
}

echo "\n=== Instructions ===\n";
echo "1. Éditez le fichier .env avec vos vraies valeurs\n";
echo "2. Testez l'application\n";
echo "3. Ne commitez JAMAIS le fichier .env\n";
echo "4. Consultez config/SECURITE_CONFIGURATION.md pour plus d'informations\n\n";

echo "Configuration terminée!\n";
