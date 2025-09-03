# Tests du Module DOM

Ce dossier contient tous les tests pour le module DOM (Dossiers de Mission) de l'application HFF Intranet.

## Structure des tests

```
tests/
├── dom/                          # Tests spécifiques au module DOM
│   ├── unit/                     # Tests unitaires
│   │   ├── DomValidationServiceTest.php
│   │   └── DomBusinessLogicServiceTest.php
│   ├── functional/               # Tests fonctionnels
│   │   ├── DomControllerTest.php
│   │   └── DomApiControllerTest.php
│   └── integration/              # Tests d'intégration
│       └── DomIntegrationTest.php
├── phpunit.xml                   # Configuration PHPUnit
├── run_tests.php                 # Script de lancement PHP
├── run_dom_tests.sh              # Script de lancement Bash (Linux/Mac)
├── run_dom_tests.ps1             # Script de lancement PowerShell (Windows)
└── README.md                     # Ce fichier
```

## Types de tests

### Tests unitaires (`unit/`)
- **DomValidationServiceTest.php** : Tests du service de validation
  - Validation des matricules
  - Vérification des chevauchements de dates
  - Validation des règles métier
  - Validation de cohérence

- **DomBusinessLogicServiceTest.php** : Tests de la logique métier
  - Création de DOM
  - Calcul des indemnités
  - Duplication de DOM
  - Gestion des trop-perçus

### Tests fonctionnels (`functional/`)
- **DomControllerTest.php** : Tests des contrôleurs principaux
  - Création DOM étape 1 et 2
  - Validation de matricule via API
  - Vérification des chevauchements
  - Calcul des indemnités
  - Duplication et trop-perçus

- **DomApiControllerTest.php** : Tests des APIs REST
  - Récupération des catégories
  - Récupération des sites
  - Récupération des services
  - Validation de matricule
  - Calcul des indemnités

### Tests d'intégration (`integration/`)
- **DomIntegrationTest.php** : Tests d'intégration complète
  - Flux complet de création DOM
  - Intégration des services
  - Intégration des formulaires
  - Intégration des APIs
  - Gestion des autorisations
  - Gestion des erreurs
  - Gestion des sessions

## Comment lancer les tests

### Option 1: Script PowerShell (Windows)
```powershell
cd tests
.\run_dom_tests.ps1
```

### Option 2: Script Bash (Linux/Mac/Git Bash)
```bash
cd tests
./run_dom_tests.sh
```

### Option 3: PHPUnit directement
```bash
# Tous les tests DOM
phpunit --testsuite "DOM All Tests"

# Tests unitaires uniquement
phpunit --testsuite "DOM Unit Tests"

# Tests fonctionnels uniquement
phpunit --testsuite "DOM Functional Tests"

# Tests d'intégration uniquement
phpunit --testsuite "DOM Integration Tests"

# Avec couverture de code
phpunit --testsuite "DOM All Tests" --coverage-html coverage
```

### Option 4: Script PHP
```bash
cd tests
php run_tests.php
```

## Configuration

Le fichier `phpunit.xml` contient la configuration des tests :
- **Bootstrap** : `vendor/autoload.php`
- **Testsuites** : Organisation par type de tests
- **Couverture** : Inclut les contrôleurs, services, formulaires et entités DOM
- **Environnement** : Mode test avec base de données en mémoire

## Prérequis

1. **PHPUnit** : Installé via Composer
   ```bash
   composer require --dev phpunit/phpunit
   ```

2. **Dépendances** : Toutes les dépendances installées
   ```bash
   composer install
   ```

3. **Base de données** : Configuration pour les tests (SQLite en mémoire)

## Couverture de code

Les tests couvrent :
- ✅ **Contrôleurs DOM** : `src/Controller/dom/`
- ✅ **Services DOM** : `src/Service/dom/`
- ✅ **Formulaires DOM** : `src/Form/dom/`
- ✅ **Entités DOM** : `src/Entity/dom/`
- ✅ **Repositories DOM** : `src/Repository/dom/`

## Exemples de tests

### Test unitaire
```php
public function testValidateMatriculeValid(): void
{
    $matricule = 'EMP001';
    $result = $this->validationService->validateMatricule($matricule);
    $this->assertTrue($result['valid']);
}
```

### Test fonctionnel
```php
public function testCreateStep1(): void
{
    $request = new Request();
    $response = $this->controller->createStep1($request);
    $this->assertEquals(200, $response->getStatusCode());
}
```

### Test d'intégration
```php
public function testCompleteDomCreationFlow(): void
{
    // Test du flux complet de création DOM
    // Étape 1 → Étape 2 → Validation → Calcul
}
```

## Ajout de nouveaux tests

Pour ajouter de nouveaux tests :

1. **Tests unitaires** : Ajoutez dans `dom/unit/`
2. **Tests fonctionnels** : Ajoutez dans `dom/functional/`
3. **Tests d'intégration** : Ajoutez dans `dom/integration/`

Suivez la convention de nommage : `[Classe]Test.php`

## Rapports

- **Rapport de couverture** : Généré dans `coverage/` (option 5)
- **Rapport détaillé** : Mode verbose (option 6)
- **Résultats** : Affichés dans le terminal avec couleurs

## Dépannage

### Erreur "PHPUnit not found"
```bash
composer require --dev phpunit/phpunit
```

### Erreur "Dependencies not installed"
```bash
composer install
```

### Erreur de base de données
Vérifiez la configuration dans `phpunit.xml` et assurez-vous que la base de données de test est accessible.

## Contribution

Lors de l'ajout de nouvelles fonctionnalités au module DOM :
1. Ajoutez les tests correspondants
2. Vérifiez que tous les tests passent
3. Maintenez une couverture de code élevée
4. Documentez les nouveaux tests
