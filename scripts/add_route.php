<?php

/**
 * Script pour ajouter facilement une nouvelle route essentielle
 */

if ($argc < 4) {
    echo "Usage: php scripts/add_route.php <nom_route> <chemin> <controller>\n";
    echo "Exemple: php scripts/add_route.php 'nouvelle_route' '/nouveau-chemin' 'App\\Controller\\NouveauController::action'\n";
    exit(1);
}

$routeName = $argv[1];
$routePath = $argv[2];
$routeController = $argv[3];

$yamlFile = __DIR__ . '/../config/routes/essential_routes.yaml';

if (!file_exists($yamlFile)) {
    echo "❌ Fichier de configuration non trouvé : $yamlFile\n";
    exit(1);
}

// Lire le fichier YAML
$content = file_get_contents($yamlFile);

// Ajouter la nouvelle route dans la section appropriée
$newRoute = "  $routeName:\n    path: $routePath\n    controller: $routeController\n";

// Déterminer dans quelle section ajouter la route
$section = 'functional_routes'; // Par défaut
if (strpos($routePath, '/admin/') === 0) {
    $section = 'admin_routes';
} elseif (in_array($routeName, ['login', 'logout', 'profil_acceuil', 'auth_deconnexion'])) {
    $section = 'essential_routes';
}

// Ajouter la route
$pattern = "/($section:.*?)(\n  [a-zA-Z_]+:|\n\n|\n$)/s";
$replacement = "$1$newRoute$2";

$newContent = preg_replace($pattern, $replacement, $content);

if ($newContent === $content) {
    echo "❌ Impossible d'ajouter la route. Vérifiez le format du fichier YAML.\n";
    exit(1);
}

// Sauvegarder
file_put_contents($yamlFile, $newContent);

echo "✅ Route ajoutée avec succès :\n";
echo "   Nom: $routeName\n";
echo "   Chemin: $routePath\n";
echo "   Controller: $routeController\n";
echo "   Section: $section\n\n";
echo "Testez avec : php scripts/manage_routes.php\n";
