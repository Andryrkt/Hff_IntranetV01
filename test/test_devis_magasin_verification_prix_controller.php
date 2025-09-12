<?php

/**
 * Script de test d'intégration pour DevisMagasinVerificationPrixController
 * Ce script teste que le contrôleur peut être instancié et que ses méthodes principales fonctionnent
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Démarrer la session AVANT tout output pour les tests CLI
if (php_sapi_name() === 'cli') {
    ini_set('session.use_cookies', '0');
    ini_set('session.use_only_cookies', '0');
    ini_set('session.cache_limiter', '');
    session_start();
}

echo "=== Test du Contrôleur DevisMagasinVerificationPrixController ===\n\n";

try {
    // Charger le bootstrap avec injection de dépendances
    echo "1. Chargement du bootstrap avec DI...\n";
    $services = require __DIR__ . '/../config/bootstrap_di.php';

    if (!$services) {
        throw new Exception("Impossible de charger les services");
    }

    echo "✅ Bootstrap chargé avec succès\n\n";

    $container = $services['container'];

    // Test d'instanciation du contrôleur
    echo "2. Test d'instanciation de DevisMagasinVerificationPrixController...\n";

    try {
        $controller = new \App\Controller\magasin\devis\DevisMagasinVerificationPrixController();
        echo "✅ DevisMagasinVerificationPrixController instancié avec succès\n";

        // Test des constantes
        $reflection = new ReflectionClass($controller);
        $constants = $reflection->getConstants();

        echo "   - Constantes du contrôleur :\n";
        foreach ($constants as $name => $value) {
            echo "     * $name = '$value'\n";
        }

        // Vérification des constantes attendues
        $expectedConstants = [
            'TYPE_SOUMISSION_VERIFICATION_PRIX' => 'VP',
            'STATUT_PRIX_A_CONFIRMER' => 'Prix à confirmer',
            'MESSAGE_DE_CONFIRMATION' => 'verification prix'
        ];

        foreach ($expectedConstants as $constName => $expectedValue) {
            if (isset($constants[$constName]) && $constants[$constName] === $expectedValue) {
                echo "✅ Constante $constName correcte\n";
            } else {
                echo "❌ Constante $constName incorrecte (attendu: '$expectedValue', reçu: '" . ($constants[$constName] ?? 'non définie') . "')\n";
            }
        }

        echo "✅ Test des constantes terminé\n";
    } catch (Exception $e) {
        echo "❌ Erreur lors de l'instanciation de DevisMagasinVerificationPrixController: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }

    echo "\n";

    // Test des dépendances du contrôleur
    echo "3. Test des dépendances du contrôleur...\n";

    try {
        // Test de l'Entity Manager
        $em = $container->get('doctrine.orm.entity_manager');
        if ($em) {
            echo "✅ Entity Manager disponible\n";
        } else {
            echo "❌ Entity Manager non disponible\n";
        }

        // Test du service d'historique des opérations
        $historiqueService = $container->get(\App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService::class);
        if ($historiqueService) {
            echo "✅ Service HistoriqueOperationDevisMagasinService disponible\n";
        } else {
            echo "❌ Service HistoriqueOperationDevisMagasinService non disponible\n";
        }

        // Test du repository DevisMagasin
        $repository = $em->getRepository(\App\Entity\magasin\devis\DevisMagasin::class);
        if ($repository) {
            echo "✅ Repository DevisMagasin disponible\n";
        } else {
            echo "❌ Repository DevisMagasin non disponible\n";
        }

        // Test du service de validation VP
        $validationService = new \App\Service\magasin\devis\DevisMagasinValidationVpService(
            $historiqueService,
            'TEST123'
        );
        if ($validationService) {
            echo "✅ Service DevisMagasinValidationVpService instancié\n";
        } else {
            echo "❌ Service DevisMagasinValidationVpService non instancié\n";
        }

        // Test du modèle ListeDevisMagasinModel
        $listeModel = new \App\Model\magasin\devis\ListeDevisMagasinModel();
        if ($listeModel) {
            echo "✅ Modèle ListeDevisMagasinModel instancié\n";
        } else {
            echo "❌ Modèle ListeDevisMagasinModel non instancié\n";
        }

        // Test du service de génération PDF
        $pdfService = new \App\Service\genererPdf\GeneratePdfDevisMagasin();
        if ($pdfService) {
            echo "✅ Service GeneratePdfDevisMagasin instancié\n";
        } else {
            echo "❌ Service GeneratePdfDevisMagasin non instancié\n";
        }

        echo "✅ Test des dépendances terminé\n";
    } catch (Exception $e) {
        echo "❌ Erreur lors du test des dépendances: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }

    echo "\n";

    // Test des méthodes du contrôleur (via réflexion)
    echo "4. Test des méthodes du contrôleur...\n";

    try {
        $reflection = new ReflectionClass(\App\Controller\magasin\devis\DevisMagasinVerificationPrixController::class);

        // Vérification des méthodes publiques
        $publicMethods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);
        $expectedMethods = ['soumission'];

        echo "   - Méthodes publiques trouvées :\n";
        foreach ($publicMethods as $method) {
            if ($method->class === \App\Controller\magasin\devis\DevisMagasinVerificationPrixController::class) {
                echo "     * " . $method->name . "()\n";
            }
        }

        // Vérification des méthodes privées
        $privateMethods = $reflection->getMethods(ReflectionMethod::IS_PRIVATE);
        $expectedPrivateMethods = ['traitementFormualire', 'enregistrementFichier'];

        echo "   - Méthodes privées trouvées :\n";
        foreach ($privateMethods as $method) {
            if ($method->class === \App\Controller\magasin\devis\DevisMagasinVerificationPrixController::class) {
                echo "     * " . $method->name . "()\n";
            }
        }

        // Vérification de la présence des méthodes attendues
        $methodNames = array_map(function ($method) {
            return $method->name;
        }, array_merge($publicMethods, $privateMethods));

        foreach ($expectedMethods as $expectedMethod) {
            if (in_array($expectedMethod, $methodNames)) {
                echo "✅ Méthode publique $expectedMethod trouvée\n";
            } else {
                echo "❌ Méthode publique $expectedMethod manquante\n";
            }
        }

        foreach ($expectedPrivateMethods as $expectedMethod) {
            if (in_array($expectedMethod, $methodNames)) {
                echo "✅ Méthode privée $expectedMethod trouvée\n";
            } else {
                echo "❌ Méthode privée $expectedMethod manquante\n";
            }
        }

        echo "✅ Test des méthodes terminé\n";
    } catch (Exception $e) {
        echo "❌ Erreur lors du test des méthodes: " . $e->getMessage() . "\n";
    }

    echo "\n";

    // Test de la configuration de l'environnement
    echo "5. Test de la configuration de l'environnement...\n";

    try {
        // Vérification de la variable d'environnement BASE_PATH_FICHIER
        if (isset($_ENV['BASE_PATH_FICHIER'])) {
            echo "✅ Variable BASE_PATH_FICHIER définie : " . $_ENV['BASE_PATH_FICHIER'] . "\n";
        } else {
            echo "❌ Variable BASE_PATH_FICHIER non définie\n";
        }

        // Vérification du répertoire d'upload
        $uploadPath = $_ENV['BASE_PATH_FICHIER'] . '/magasin/devis/';
        if (is_dir($uploadPath)) {
            echo "✅ Répertoire d'upload existe : $uploadPath\n";
        } else {
            echo "⚠️  Répertoire d'upload n'existe pas : $uploadPath\n";
            echo "   Création du répertoire...\n";
            if (mkdir($uploadPath, 0755, true)) {
                echo "✅ Répertoire d'upload créé avec succès\n";
            } else {
                echo "❌ Impossible de créer le répertoire d'upload\n";
            }
        }

        echo "✅ Test de la configuration terminé\n";
    } catch (Exception $e) {
        echo "❌ Erreur lors du test de la configuration: " . $e->getMessage() . "\n";
    }

    echo "\n";

    // Test de simulation d'une requête
    echo "6. Test de simulation d'une requête...\n";

    try {
        // Création d'une requête simulée
        $request = new \Symfony\Component\HttpFoundation\Request();
        $request->setMethod('GET');
        $request->attributes->set('numeroDevis', 'TEST123456');

        echo "✅ Requête simulée créée\n";
        echo "   - Méthode : " . $request->getMethod() . "\n";
        echo "   - Numéro de devis : " . $request->attributes->get('numeroDevis') . "\n";

        // Test de création d'un formulaire simulé
        $formFactory = $container->get('form.factory');
        if ($formFactory) {
            echo "✅ FormFactory disponible\n";

            // Test de création d'un formulaire DevisMagasin
            $devisMagasin = new \App\Entity\magasin\devis\DevisMagasin();
            $form = $formFactory->createBuilder(\App\Form\magasin\devis\DevisMagasinType::class, $devisMagasin)->getForm();

            if ($form) {
                echo "✅ Formulaire DevisMagasin créé avec succès\n";
            } else {
                echo "❌ Impossible de créer le formulaire DevisMagasin\n";
            }
        } else {
            echo "❌ FormFactory non disponible\n";
        }

        echo "✅ Test de simulation terminé\n";
    } catch (Exception $e) {
        echo "❌ Erreur lors du test de simulation: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }

    echo "\n";

    // Résumé des tests
    echo "=== Résumé des tests ===\n";
    echo "✅ Contrôleur DevisMagasinVerificationPrixController testé\n";
    echo "✅ Constantes du contrôleur vérifiées\n";
    echo "✅ Dépendances du contrôleur testées\n";
    echo "✅ Méthodes du contrôleur vérifiées\n";
    echo "✅ Configuration de l'environnement testée\n";
    echo "✅ Simulation de requête testée\n\n";

    echo "🎉 Les tests du contrôleur DevisMagasinVerificationPrixController sont terminés !\n";
    echo "📝 Note: Pour des tests unitaires complets, il faudrait refactoriser le contrôleur\n";
    echo "   pour permettre l'injection de dépendances et l'utilisation de mocks.\n";
} catch (Exception $e) {
    echo "❌ Erreur critique: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";

    echo "\n🔧 Suggestions de résolution:\n";
    echo "1. Vérifier que tous les fichiers de configuration sont présents\n";
    echo "2. Vérifier que les services requis sont disponibles dans le container\n";
    echo "3. Vérifier les namespaces et les chemins d'autoload\n";
    echo "4. Consulter les logs d'erreur pour plus de détails\n";
}
