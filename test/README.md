# Tests

Ce dossier contient tous les tests du projet HFF Intranet.

## Structure

Le dossier `test/` est actuellement vide mais prêt à accueillir :

### Tests unitaires
- Tests des entités Doctrine
- Tests des services
- Tests des contrôleurs
- Tests des repositories

### Tests d'intégration
- Tests des API
- Tests des formulaires
- Tests des workflows métier

### Tests fonctionnels
- Tests end-to-end
- Tests d'interface utilisateur

## Configuration

Le projet utilise PHPUnit pour les tests (déjà installé via Composer).

## Exécution des tests

```bash
# Tous les tests
vendor/bin/phpunit

# Tests spécifiques
vendor/bin/phpunit test/Unit/
vendor/bin/phpunit test/Integration/
```

## Bonnes pratiques

1. **Nommage** : Les fichiers de test doivent se terminer par `Test.php`
2. **Structure** : Organiser les tests par module/domaine
3. **Couverture** : Viser une couverture de code élevée
4. **Documentation** : Documenter les cas de test complexes

## Exemple de test

```php
<?php

namespace App\Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function testSomething(): void
    {
        $this->assertTrue(true);
    }
}
```
