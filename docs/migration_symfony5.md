# Migration vers Symfony 5 - Guide Complet

## Vue d'ensemble

Ce document décrit le processus de migration de l'application Hffintranet vers Symfony 5 avec l'implémentation de l'injection de dépendances.

## État actuel

L'application utilise actuellement :
- Symfony 5.4 (composants individuels)
- Doctrine ORM 2.19
- Twig 3.11
- PHP 7.4+

## Objectifs de la migration

1. **Implémenter l'injection de dépendances Symfony**
2. **Refactoriser l'architecture pour utiliser le conteneur de services**
3. **Préparer la structure pour une migration complète vers Symfony 5**
4. **Améliorer la maintenabilité et la testabilité du code**

## Fichiers créés/modifiés

### 1. Configuration des services (`config/services.yaml`)
- Définition de tous les services de l'application
- Configuration de l'autowiring et de l'autoconfiguration
- Définition des services publics et privés

### 2. Configuration principale (`config/config.yaml`)
- Import de tous les fichiers de configuration
- Configuration globale du framework
- Paramètres de l'application

### 3. Configuration Doctrine (`config/doctrine.yaml`)
- Configuration de la base de données
- Configuration de l'ORM
- Configuration des migrations

### 4. Configuration Twig (`config/twig.yaml`)
- Configuration des templates
- Configuration des extensions
- Configuration des thèmes de formulaires

### 5. Configuration des formulaires (`config/form.yaml`)
- Configuration des composants de formulaire
- Configuration de la validation
- Configuration de la sécurité CSRF

### 6. Nouveau bootstrap (`config/bootstrap_di.php`)
- Utilisation du conteneur de services Symfony
- Configuration des services
- Initialisation de l'application

### 7. Nouvelle classe Controller (`src/Controller/ControllerDI.php`)
- Injection de dépendances via le constructeur
- Suppression des méthodes statiques
- Accès aux services via les propriétés injectées

## Avantages de l'injection de dépendances

### 1. Testabilité
- Les services peuvent être facilement mockés
- Tests unitaires plus simples à écrire
- Isolation des composants

### 2. Maintenabilité
- Code plus lisible et organisé
- Dépendances explicites
- Réduction du couplage

### 3. Flexibilité
- Configuration centralisée
- Services configurables
- Gestion des environnements

### 4. Performance
- Services lazy-loaded
- Cache des services
- Optimisations automatiques

## Étapes de migration

### Phase 1 : Implémentation de l'ID (Terminée)
- ✅ Création des fichiers de configuration
- ✅ Nouveau bootstrap avec conteneur de services
- ✅ Nouvelle classe Controller avec ID

### Phase 2 : Refactorisation des contrôleurs
- [ ] Migrer les contrôleurs existants vers la nouvelle architecture
- [ ] Remplacer les appels statiques par l'injection de dépendances
- [ ] Tester chaque contrôleur

### Phase 3 : Migration des services
- [ ] Refactoriser les services existants
- [ ] Implémenter les interfaces appropriées
- [ ] Configurer les services dans le conteneur

### Phase 4 : Migration des modèles
- [ ] Refactoriser les modèles existants
- [ ] Implémenter les repositories appropriés
- [ ] Configurer les entités Doctrine

### Phase 5 : Tests et validation
- [ ] Tests unitaires
- [ ] Tests d'intégration
- [ ] Tests de régression

### Phase 6 : Migration complète vers Symfony 5
- [ ] Création du Kernel Symfony
- [ ] Configuration des bundles
- [ ] Migration des routes
- [ ] Migration des formulaires

## Utilisation de la nouvelle architecture

### 1. Dans un contrôleur existant

```php
// Avant (ancienne méthode)
class MonController extends Controller
{
    public function index()
    {
        $em = self::getEntity();
        $twig = self::getTwig();
        // ...
    }
}

// Après (nouvelle méthode)
class MonController extends ControllerDI
{
    public function index()
    {
        $em = $this->getEntityManager();
        $twig = $this->getTwig();
        // ...
    }
}
```

### 2. Création d'un nouveau service

```php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class MonService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function maMethode()
    {
        // Utilisation de l'EntityManager injecté
    }
}
```

### 3. Configuration du service

```yaml
# config/services.yaml
services:
    App\Service\MonService:
        arguments:
            $entityManager: '@doctrine.orm.entity_manager'
```

## Configuration des variables d'environnement

Créer un fichier `.env` à la racine du projet :

```env
# Configuration de l'application
APP_ENV=dev
APP_DEBUG=true
APP_SECRET=votre_secret_ici

# Configuration de la base de données
DB_HOST=localhost
DB_PORT=3306
DB_NAME=hffintranet
DB_USER=root
DB_PASSWORD=

# Configuration des chemins
BASE_PATH_COURT=/Hffintranet
```

## Tests de la nouvelle architecture

### 1. Test du conteneur de services

```php
// Dans un script de test
$services = require 'config/bootstrap_di.php';
$container = $services['container'];

// Vérifier que les services sont disponibles
$entityManager = $container->get('doctrine.orm.entity_manager');
$twig = $container->get('twig');
```

### 2. Test d'un contrôleur

```php
// Créer une instance du contrôleur
$controller = new MonController(
    $entityManager,
    $urlGenerator,
    $twig,
    $formFactory,
    $session,
    $tokenStorage,
    $authorizationChecker,
    // ... autres services
);

// Tester les méthodes
$result = $controller->index();
```

## Problèmes connus et solutions

### 1. Services non trouvés
- Vérifier la configuration dans `services.yaml`
- S'assurer que l'autowiring est activé
- Vérifier les namespaces des classes

### 2. Erreurs de configuration
- Vérifier la syntaxe YAML
- S'assurer que tous les fichiers sont importés
- Vérifier les paramètres

### 3. Problèmes de performance
- Activer le cache des services en production
- Optimiser la configuration Doctrine
- Utiliser le profiler Symfony

## Prochaines étapes

1. **Tester la nouvelle architecture** avec quelques contrôleurs
2. **Migrer progressivement** les contrôleurs existants
3. **Implémenter les tests** pour valider la migration
4. **Préparer la migration complète** vers Symfony 5

## Support et assistance

Pour toute question ou problème lors de la migration :
1. Consulter la documentation Symfony 5
2. Vérifier les logs d'erreur
3. Utiliser le profiler Symfony pour le débogage
4. Consulter la communauté Symfony

## Conclusion

L'implémentation de l'injection de dépendances est la première étape vers une architecture moderne et maintenable. Cette migration permettra d'améliorer significativement la qualité du code et facilitera la transition vers Symfony 5.
