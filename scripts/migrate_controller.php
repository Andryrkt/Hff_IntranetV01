<?php

/**
 * Script de migration automatisé pour refactoriser les contrôleurs
 * Ce script aide à créer des versions refactorisées des contrôleurs existants
 */

require_once __DIR__ . '/../vendor/autoload.php';

class ControllerMigrator
{
    private string $sourceDir;
    private string $outputDir;
    private array $templateParams;

    public function __construct(string $sourceDir, string $outputDir)
    {
        $this->sourceDir = $sourceDir;
        $this->outputDir = $outputDir;
        $this->templateParams = $this->getTemplateParams();
    }

    /**
     * Paramètres du template de constructeur
     */
    private function getTemplateParams(): array
    {
        return [
            'entityManager' => '\Doctrine\ORM\EntityManagerInterface',
            'urlGenerator' => '\Symfony\Component\Routing\Generator\UrlGeneratorInterface',
            'twig' => '\Twig\Environment',
            'formFactory' => '\Symfony\Component\Form\FormFactoryInterface',
            'session' => '\Symfony\Component\HttpFoundation\Session\SessionInterface',
            'tokenStorage' => '\Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface',
            'authorizationChecker' => '\Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface',
            'fusionPdf' => '\App\Service\FusionPdf',
            'ldapModel' => '\App\Model\LdapModel',
            'profilModel' => '\App\Model\ProfilModel',
            'badmModel' => '\App\Model\badm\BadmModel',
            'personnelModel' => '\App\Model\admin\personnel\PersonnelModel',
            'domModel' => '\App\Model\dom\DomModel',
            'daModel' => '\App\Model\da\DaModel',
            'domDetailModel' => '\App\Model\dom\DomDetailModel',
            'domDuplicationModel' => '\App\Model\dom\DomDuplicationModel',
            'domListModel' => '\App\Model\dom\DomListModel',
            'ditModel' => '\App\Model\dit\DitModel',
            'transferDonnerModel' => '\App\Model\TransferDonnerModel',
            'sessionManagerService' => '\App\Service\SessionManagerService',
            'excelService' => '\App\Service\ExcelService'
        ];
    }

    /**
     * Générer le constructeur avec injection de dépendances
     */
    private function generateConstructor(): string
    {
        $params = [];
        $assignments = [];

        foreach ($this->templateParams as $paramName => $type) {
            $params[] = "        {$type} \${$paramName}";
            $assignments[] = "            \${$paramName}";
        }

        $paramsStr = implode(",\n", $params);
        $assignmentsStr = implode(",\n", $assignments);

        return <<<PHP
    public function __construct(
{$paramsStr}
    ) {
        parent::__construct(
{$assignmentsStr}
        );
    }

PHP;
    }

    /**
     * Migrer un contrôleur spécifique
     */
    public function migrateController(string $controllerPath): bool
    {
        if (!file_exists($controllerPath)) {
            echo "❌ Fichier non trouvé: {$controllerPath}\n";
            return false;
        }

        $content = file_get_contents($controllerPath);
        $className = $this->extractClassName($content);

        if (!$className) {
            echo "❌ Impossible d'extraire le nom de la classe de: {$controllerPath}\n";
            return false;
        }

        echo "🔄 Migration de {$className}...\n";

        // Remplacer l'héritage
        $content = str_replace(
            "extends Controller",
            "extends BaseController",
            $content
        );

        // Remplacer le constructeur
        $oldConstructor = $this->extractConstructor($content);
        if ($oldConstructor) {
            $newConstructor = $this->generateConstructor();
            $content = str_replace($oldConstructor, $newConstructor, $content);
        }

        // Remplacer les appels statiques
        $content = $this->replaceStaticCalls($content);

        // Ajouter les imports nécessaires
        $content = $this->addRequiredImports($content);

        // Créer le nom du fichier refactorisé
        $refactoredName = str_replace('.php', 'Refactored.php', basename($controllerPath));
        $outputPath = $this->outputDir . '/' . $refactoredName;

        // Écrire le fichier refactorisé
        if (file_put_contents($outputPath, $content)) {
            echo "✅ {$className} migré vers {$outputPath}\n";
            return true;
        } else {
            echo "❌ Erreur lors de l'écriture de {$outputPath}\n";
            return false;
        }
    }

    /**
     * Extraire le nom de la classe
     */
    private function extractClassName(string $content): ?string
    {
        if (preg_match('/class\s+(\w+)/', $content, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Extraire le constructeur existant
     */
    private function extractConstructor(string $content): ?string
    {
        if (preg_match('/public function __construct\([^)]*\)\s*\{[^}]*\}/s', $content, $matches)) {
            return $matches[0];
        }
        return null;
    }

    /**
     * Remplacer les appels statiques
     */
    private function replaceStaticCalls(string $content): string
    {
        $replacements = [
            'self::$em' => '$this->getEntityManager()',
            'self::$twig' => '$this->getTwig()',
            'self::$validator' => '$this->getFormFactory()',
            'self::$generator' => '$this->getUrlGenerator()',
            'self::$paginator' => '$this->getPaginator()',
        ];

        foreach ($replacements as $old => $new) {
            $content = str_replace($old, $new, $content);
        }

        // Remplacer self::$twig->display par $this->render
        $content = preg_replace(
            '/self::\$twig->display\s*\(\s*([^,]+)(?:,\s*([^)]+))?\s*\)/',
            '$this->render($1$2)',
            $content
        );

        return $content;
    }

    /**
     * Ajouter les imports nécessaires
     */
    private function addRequiredImports(string $content): string
    {
        $requiredImports = [
            'use Symfony\Component\HttpFoundation\Response;',
            'use Symfony\Component\HttpFoundation\RedirectResponse;',
        ];

        $existingImports = [];
        foreach ($requiredImports as $import) {
            if (strpos($content, $import) === false) {
                $existingImports[] = $import;
            }
        }

        if (!empty($existingImports)) {
            // Trouver la fin des imports existants
            $lastUsePos = strrpos($content, 'use ');
            if ($lastUsePos !== false) {
                $lineEndPos = strpos($content, "\n", $lastUsePos);
                if ($lineEndPos !== false) {
                    $importsToAdd = "\n" . implode("\n", $existingImports);
                    $content = substr($content, 0, $lineEndPos + 1) . $importsToAdd . substr($content, $lineEndPos + 1);
                }
            }
        }

        return $content;
    }

    /**
     * Migrer tous les contrôleurs d'un dossier
     */
    public function migrateDirectory(string $directory): array
    {
        $results = [];
        $files = glob($directory . '/*.php');

        foreach ($files as $file) {
            $results[] = $this->migrateController($file);
        }

        return $results;
    }

    /**
     * Afficher les statistiques de migration
     */
    public function showMigrationStats(array $results): void
    {
        $total = count($results);
        $success = count(array_filter($results));
        $failed = $total - $success;

        echo "\n=== Statistiques de Migration ===\n";
        echo "Total: {$total}\n";
        echo "✅ Réussis: {$success}\n";
        echo "❌ Échoués: {$failed}\n";
        echo "Taux de réussite: " . round(($success / $total) * 100, 2) . "%\n";
    }
}

// Utilisation du script
if (php_sapi_name() === 'cli') {
    $sourceDir = __DIR__ . '/../src/Controller';
    $outputDir = __DIR__ . '/../src/Controller/refactored';

    // Créer le dossier de sortie s'il n'existe pas
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }

    $migrator = new ControllerMigrator($sourceDir, $outputDir);

    echo "=== Migration des Contrôleurs ===\n\n";

    // Migrer un contrôleur spécifique
    if (isset($argv[1])) {
        $controllerPath = $sourceDir . '/' . $argv[1];
        $migrator->migrateController($controllerPath);
    } else {
        // Migrer tous les contrôleurs principaux
        $mainControllers = [
            'HomeController.php',
            'Authentification.php',
            'Transfer04Controller.php',
            'MigrationDaController.php',
            'LdapControl.php'
        ];

        $results = [];
        foreach ($mainControllers as $controller) {
            $controllerPath = $sourceDir . '/' . $controller;
            if (file_exists($controllerPath)) {
                $results[] = $migrator->migrateController($controllerPath);
            }
        }

        $migrator->showMigrationStats($results);
    }
} else {
    echo "Ce script doit être exécuté en ligne de commande.\n";
    echo "Usage: php migrate_controller.php [nom_du_controleur]\n";
}
