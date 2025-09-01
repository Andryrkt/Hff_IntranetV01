<?php

// Test simple du contrôleur sans Twig complexe
echo "=== Test Simple du Contrôleur ===\n";

// Charger le bootstrap DI simplifié
$services = require 'config/bootstrap_di_simple.php';

echo "1. Vérification des services...\n";
if (isset($services['container'])) {
    echo "✅ Conteneur disponible\n";
} else {
    echo "❌ Conteneur manquant\n";
    exit(1);
}

// Créer une instance du HomeController
try {
    $controller = new \App\Controller\HomeController();
    echo "✅ HomeController instancié\n";

    // Tester la méthode showPageAcceuil
    $result = $controller->showPageAcceuil();
    echo "✅ Méthode showPageAcceuil exécutée\n";

    if ($result instanceof \Symfony\Component\HttpFoundation\Response) {
        echo "✅ Retourne une Response valide\n";
        echo "📄 Contenu de la réponse :\n";
        echo substr($result->getContent(), 0, 200) . "...\n";
    } else {
        echo "❌ Ne retourne pas une Response\n";
        var_dump($result);
    }
} catch (\Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    echo "Stack trace :\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Fin du test ===\n";
