# Tests pour DevisMagasinVerificationPrixController

Ce dossier contient les tests pour le contrôleur `DevisMagasinVerificationPrixController` qui gère la vérification de prix des devis magasin.

## Fichiers de test

### 1. DevisMagasinVerificationPrixControllerTest.php
Test unitaire complet utilisant PHPUnit avec des mocks pour toutes les dépendances.

**Fonctionnalités testées :**
- Instanciation du contrôleur
- Validation des constantes
- Tests des méthodes publiques et privées
- Gestion des erreurs et exceptions
- Tests de sécurité et performance

**Limitations :**
- Le contrôleur actuel utilise des dépendances globales qui rendent les tests unitaires difficiles
- Nécessite une refactorisation pour permettre l'injection de dépendances

### 2. test_devis_magasin_verification_prix_controller.php
Script de test d'intégration pratique qui peut être exécuté directement.

**Fonctionnalités testées :**
- Instanciation du contrôleur
- Vérification des constantes
- Test des dépendances (services, repositories)
- Validation des méthodes du contrôleur
- Configuration de l'environnement
- Simulation de requêtes

## Comment exécuter les tests

### Test d'intégration (recommandé)
```bash
cd test
php test_devis_magasin_verification_prix_controller.php
```

### Test unitaire PHPUnit (si PHPUnit est installé)
```bash
cd test
phpunit DevisMagasinVerificationPrixControllerTest.php
```

## Prérequis

1. **Configuration de l'environnement :**
   - Variable `BASE_PATH_FICHIER` définie dans `$_ENV`
   - Répertoire d'upload `/magasin/devis/` accessible

2. **Services requis :**
   - `HistoriqueOperationDevisMagasinService`
   - `DevisMagasinRepository`
   - `ListeDevisMagasinModel`
   - `GeneratePdfDevisMagasin`
   - `DevisMagasinValidationVpService`

3. **Bootstrap :**
   - Fichier `config/bootstrap_di.php` fonctionnel
   - Container de services opérationnel

## Structure des tests

### Tests unitaires (DevisMagasinVerificationPrixControllerTest.php)

```php
// Test d'instanciation
public function testControllerInstantiation()

// Test des constantes
public function testControllerConstants()

// Test de la méthode soumission
public function testSoumissionWithValidNumeroDevis()
public function testSoumissionWithNullNumeroDevis()

// Test de validation du formulaire
public function testFormulaireValidation()

// Test de gestion des erreurs
public function testValidationErrors()

// Test d'historisation
public function testHistorisationOperation()

// Test de génération PDF
public function testGenerationPdf()

// Test d'intégration IPS
public function testIntegrationIPS()

// Test des autorisations
public function testAutorisationAcces()

// Test de gestion des exceptions
public function testExceptionHandling()

// Test de performance
public function testPerformance()

// Test de sécurité
public function testSecurity()
```

### Tests d'intégration (test_devis_magasin_verification_prix_controller.php)

1. **Chargement du bootstrap** - Vérification que les services sont disponibles
2. **Instanciation du contrôleur** - Test de création et des constantes
3. **Test des dépendances** - Vérification des services requis
4. **Test des méthodes** - Validation de la structure du contrôleur
5. **Test de configuration** - Vérification de l'environnement
6. **Test de simulation** - Test avec des données simulées

## Améliorations suggérées

### Pour des tests unitaires complets :

1. **Refactorisation du contrôleur :**
   ```php
   // Au lieu d'utiliser des dépendances globales
   public function __construct(
       EntityManagerInterface $em,
       FormFactoryInterface $formFactory,
       // ... autres dépendances
   ) {
       // Injection de dépendances
   }
   ```

2. **Utilisation de mocks :**
   ```php
   // Mock des services
   $mockValidationService = $this->createMock(DevisMagasinValidationVpService::class);
   $mockValidationService->method('checkMissingIdentifier')->willReturn(true);
   ```

3. **Tests d'intégration avec base de données :**
   ```php
   // Utilisation d'une base de données de test
   // Configuration de fixtures
   // Tests avec des données réelles
   ```

## Résolution des problèmes courants

### Erreur "Impossible de charger les services"
- Vérifier que `config/bootstrap_di.php` existe et est fonctionnel
- Vérifier les chemins d'autoload dans `composer.json`

### Erreur "Variable BASE_PATH_FICHIER non définie"
- Ajouter la variable dans le fichier `.env` ou `parameters.yaml`
- Ou définir directement : `$_ENV['BASE_PATH_FICHIER'] = '/chemin/vers/uploads'`

### Erreur "Service non disponible"
- Vérifier que le service est bien enregistré dans le container
- Vérifier les namespaces et les chemins d'autoload

### Erreur "Répertoire d'upload n'existe pas"
- Créer le répertoire manuellement
- Vérifier les permissions d'écriture

## Notes importantes

- Les tests actuels sont principalement des tests d'intégration
- Pour des tests unitaires complets, une refactorisation du contrôleur est nécessaire
- Les tests utilisent des mocks pour simuler les dépendances
- Les tests de sécurité et performance sont des placeholders pour des tests futurs

## Contribution

Pour ajouter de nouveaux tests :
1. Suivre la convention de nommage des méthodes de test
2. Ajouter des commentaires explicatifs
3. Tester les cas d'erreur et les cas limites
4. Mettre à jour ce README si nécessaire
