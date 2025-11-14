# Tests pour DevisMagasinValidationVpOrchestrator

Ce répertoire contient une suite complète de tests pour la classe `DevisMagasinValidationVpOrchestrator`.

## 📁 Structure des fichiers

```
test/Service/magasin/devis/Validator/
├── DevisMagasinValidationVpOrchestratorTest.php           # Tests unitaires de base
├── DevisMagasinValidationVpOrchestratorIntegrationTest.php # Tests d'intégration
├── DevisMagasinValidationVpOrchestratorEdgeCasesTest.php  # Tests des cas limites
├── phpunit.xml                                            # Configuration PHPUnit
├── run_tests.php                                          # Script d'exécution
└── README.md                                              # Ce fichier
```

## 🧪 Types de tests

### 1. Tests unitaires (`DevisMagasinValidationVpOrchestratorTest.php`)
- **Constructeur** : Vérification de l'initialisation des validateurs
- **Méthodes individuelles** : Test de chaque méthode publique
- **Validation complète** : Test de la méthode `validateBeforeVpSubmission`
- **Cas d'erreur** : Gestion des échecs de validation

### 2. Tests d'intégration (`DevisMagasinValidationVpOrchestratorIntegrationTest.php`)
- **Scénarios complets** : Validation end-to-end
- **Interactions** : Entre l'orchestrateur et ses validateurs
- **Performance** : Mesure des temps d'exécution
- **Gestion d'exceptions** : Comportement en cas d'erreur

### 3. Tests des cas limites (`DevisMagasinValidationVpOrchestratorEdgeCasesTest.php`)
- **Valeurs extrêmes** : Numéros de devis, montants, lignes
- **Caractères spéciaux** : Unicode, caractères internationaux
- **Formulaires corrompus** : Données invalides
- **Repositories défaillants** : Erreurs de base de données
- **Concurrence** : Simulation d'accès simultanés

## 🚀 Exécution des tests

### Méthode 1 : Script automatisé (recommandé)
```bash
cd test/Service/magasin/devis/Validator/
php run_tests.php
```

### Méthode 2 : PHPUnit directement
```bash
# Tous les tests
vendor/bin/phpunit --configuration=test/Service/magasin/devis/Validator/phpunit.xml

# Un test spécifique
vendor/bin/phpunit --configuration=test/Service/magasin/devis/Validator/phpunit.xml DevisMagasinValidationVpOrchestratorTest.php

# Avec couverture de code
vendor/bin/phpunit --configuration=test/Service/magasin/devis/Validator/phpunit.xml --coverage-html=coverage

# Un seul test
vendor/bin/phpunit --configuration=test/Service/magasin/devis/Validator/phpunit.xml --filter testConstructor
```

### Méthode 3 : Depuis la racine du projet
```bash
# Depuis C:\wamp64\www\Hffintranet\
vendor/bin/phpunit test/Service/magasin/devis/Validator/
```

## 📊 Rapports générés

Après exécution, les rapports suivants sont générés :

- **`coverage/index.html`** : Rapport de couverture HTML interactif
- **`coverage.xml`** : Rapport de couverture au format Clover
- **`junit.xml`** : Rapport JUnit pour l'intégration continue
- **`testdox.html`** : Documentation des tests au format HTML
- **`testdox.txt`** : Documentation des tests au format texte

## 🔧 Configuration

### Variables d'environnement
Les tests utilisent les variables suivantes (configurées dans `phpunit.xml`) :
- `APP_ENV=test`
- `APP_DEBUG=true`
- `BASE_PATH_FICHIER=/tmp/test_uploads`
- `DATABASE_URL=sqlite:///:memory:`

### Prérequis
- PHP 7.4+ ou 8.0+
- PHPUnit 9.5+
- Extensions PHP : `mbstring`, `xml`, `json`
- Composer (pour l'autoloader)

## 📝 Exemples d'utilisation

### Exécuter un test spécifique
```bash
vendor/bin/phpunit --configuration=phpunit.xml --filter testConstructor
```

### Exécuter avec arrêt au premier échec
```bash
vendor/bin/phpunit --configuration=phpunit.xml --stop-on-failure
```

### Exécuter en mode verbose
```bash
vendor/bin/phpunit --configuration=phpunit.xml --verbose
```

### Exécuter avec couverture et arrêt sur erreur
```bash
vendor/bin/phpunit --configuration=phpunit.xml --coverage-html=coverage --stop-on-failure
```

## 🐛 Debugging

### Activer les logs détaillés
```bash
vendor/bin/phpunit --configuration=phpunit.xml --verbose --debug
```

### Exécuter un seul test avec debug
```bash
vendor/bin/phpunit --configuration=phpunit.xml --filter testConstructor --verbose --debug
```

### Utiliser Xdebug pour le debugging
1. Installez Xdebug
2. Configurez votre IDE pour le debugging PHP
3. Placez des breakpoints dans les tests
4. Exécutez en mode debug

## 📈 Métriques de qualité

### Couverture de code
- **Objectif** : > 90% de couverture
- **Méthodes** : Toutes les méthodes publiques testées
- **Branches** : Tous les chemins conditionnels testés
- **Lignes** : Toutes les lignes exécutables testées

### Performance
- **Temps d'exécution** : < 1 seconde pour 1000 validations
- **Mémoire** : < 512MB par test
- **Concurrence** : Support des accès simultanés

## 🔄 Intégration continue

### GitHub Actions
```yaml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.0'
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: vendor/bin/phpunit --configuration=test/Service/magasin/devis/Validator/phpunit.xml
```

### Jenkins
```groovy
pipeline {
    agent any
    stages {
        stage('Test') {
            steps {
                sh 'composer install'
                sh 'vendor/bin/phpunit --configuration=test/Service/magasin/devis/Validator/phpunit.xml'
            }
        }
    }
    post {
        always {
            publishHTML([
                allowMissing: false,
                alwaysLinkToLastBuild: true,
                keepAll: true,
                reportDir: 'test/Service/magasin/devis/Validator/coverage',
                reportFiles: 'index.html',
                reportName: 'Coverage Report'
            ])
        }
    }
}
```

## 🚨 Résolution de problèmes

### Erreur "Class not found"
```bash
composer dump-autoload
```

### Erreur de permissions
```bash
chmod +x run_tests.php
chmod -R 755 test/Service/magasin/devis/Validator/
```

### Erreur de mémoire
```bash
php -d memory_limit=1G run_tests.php
```

### Tests qui échouent
1. Vérifiez les logs d'erreur
2. Exécutez en mode verbose
3. Vérifiez la configuration PHPUnit
4. Vérifiez les dépendances

## 📚 Ressources supplémentaires

- [Documentation PHPUnit](https://phpunit.readthedocs.io/)
- [Guide de test Symfony](https://symfony.com/doc/current/testing.html)
- [Bonnes pratiques de test PHP](https://phpunit.readthedocs.io/en/9.5/writing-tests-for-phpunit.html)

## 🤝 Contribution

Pour ajouter de nouveaux tests :

1. Créez un nouveau fichier `*Test.php`
2. Étendez `TestCase` ou utilisez les classes existantes
3. Ajoutez le fichier à `phpunit.xml`
4. Documentez les nouveaux tests
5. Exécutez la suite complète pour vérifier

## 📞 Support

En cas de problème :
1. Consultez les logs d'erreur
2. Vérifiez la configuration
3. Exécutez les tests en mode debug
4. Contactez l'équipe de développement
