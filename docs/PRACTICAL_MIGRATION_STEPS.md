# Guide pratique de migration vers Symfony 5

## 🎯 **Migration progressive (sans conflits)**

### **Étape 1 : Préparation de l'environnement**

1. **Créer un nouveau répertoire pour Symfony 5 :**
```bash
# Dans le répertoire parent de Hffintranet
mkdir Hffintranet_Symfony5
cd Hffintranet_Symfony5
```

2. **Installer Symfony 5 :**
```bash
composer create-project symfony/skeleton:"5.4.*" .
```

3. **Installer les bundles nécessaires :**
```bash
composer require symfony/orm-pack
composer require symfony/form
composer require symfony/validator
composer require symfony/security-bundle
composer require sensio/framework-extra-bundle
composer require twig/extra-bundle
composer require knplabs/knp-paginator-bundle
composer require friendsofsymfony/ckeditor-bundle
composer require symfony/webpack-encore-bundle
```

### **Étape 2 : Copie des fichiers existants**

1. **Copier vos entités :**
```bash
cp -r ../Hffintranet/src/Entity/* src/Entity/
```

2. **Copier vos contrôleurs (un par un) :**
```bash
# Commencer par un contrôleur simple
cp ../Hffintranet/src/Controller/dom/DomFirstController.php src/Controller/dom/
```

3. **Copier vos services :**
```bash
cp -r ../Hffintranet/src/Service/* src/Service/
cp -r ../Hffintranet/src/Security/* src/Security/
```

4. **Copier vos templates :**
```bash
cp -r ../Hffintranet/Views/* templates/
```

### **Étape 3 : Configuration Symfony 5**

#### **config/bundles.php**
```php
<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
    Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class => ['all' => true],
    Twig\Extra\TwigExtraBundle\TwigExtraBundle::class => ['all' => true],
    Knp\Bundle\PaginatorBundle\KnpPaginatorBundle::class => ['all' => true],
    FOS\CKEditorBundle\FOSCKEditorBundle::class => ['all' => true],
    Symfony\WebpackEncoreBundle\WebpackEncoreBundle::class => ['all' => true],
];
```

#### **config/packages/framework.yaml**
```yaml
framework:
    secret: '%env(APP_SECRET)%'
    session:
        handler_id: null
        cookie_secure: auto
        cookie_samesite: lax
        storage_factory_id: session.storage.factory.native
    php_errors:
        log: true
```

#### **config/packages/security.yaml**
```yaml
security:
    providers:
        app_user_provider:
            entity:
                class: App\Entity\admin\utilisateur\User
                property: nomUtilisateur

    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        main:
            lazy: true
            provider: app_user_provider

    access_control:
        - { path: ^/admin, roles: ROLE_ADMIN }
        - { path: ^/rh, roles: ROLE_USER }
```

#### **config/packages/twig.yaml**
```yaml
twig:
    default_path: '%kernel.project_dir%/templates'
    form_themes: ['bootstrap_4_layout.html.twig']
```

### **Étape 4 : Migration des contrôleurs**

#### **Exemple : DomFirstController**

**Avant (votre code actuel) :**
```php
<?php

namespace App\Controller\dom;

use App\Controller\Controller;
use App\Controller\Traits\SecurityTrait;
use App\Service\security\SecurityService;

class DomFirstController extends Controller
{
    use SecurityTrait;

    public function firstForm(Request $request)
    {
        $this->requireAccess('DOM');
        // ...
    }
}
```

**Après (Symfony 5) :**
```php
<?php

namespace App\Controller\dom;

use App\Service\security\SecurityService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DomFirstController extends AbstractController
{
    private $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * @Route("/dom-first-form", name="dom_first_form")
     * @IsGranted("ACCESS", subject="DOM")
     */
    public function firstForm(Request $request): Response
    {
        // L'autorisation est vérifiée automatiquement
        // ...
    }
}
```

### **Étape 5 : Mise à jour des services**

#### **config/services.yaml**
```yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true

    App\:
        resource: '../src/'
        exclude:
            - '../src/DependencyInjection/'
            - '../src/Entity/'
            - '../src/Kernel.php'

    # Vos services personnalisés
    App\Service\security\SecurityService:
        arguments:
            $sessionService: '@App\Service\SessionManagerService'
            $authorizationChecker: '@security.authorization_checker'
        public: true

    App\Security\Voter\ApplicationVoter:
        tags: ['security.voter']
        public: false
```

### **Étape 6 : Mise à jour des templates Twig**

#### **Exemple de template :**

**Avant :**
```twig
{% if showCreateButton %}
    <a href="{{ path('dom_create') }}">Créer</a>
{% endif %}
```

**Après :**
```twig
{% if is_granted('CREATE', 'DOM') %}
    <a href="{{ path('dom_create') }}">Créer</a>
{% endif %}
```

### **Étape 7 : Tests et validation**

1. **Tester l'application :**
```bash
php bin/console server:run
```

2. **Vérifier les logs :**
```bash
tail -f var/log/dev.log
```

3. **Tester les autorisations :**
- Se connecter avec différents utilisateurs
- Vérifier que les autorisations fonctionnent
- Tester les annotations @IsGranted

## 🎯 **Avantages après migration**

### ✅ **Code plus propre**
```php
/**
 * @IsGranted("ACCESS", subject="DOM")
 */
public function firstForm(Request $request): Response
{
    // Code plus expressif
}
```

### ✅ **Templates plus simples**
```twig
{% if is_granted('CREATE', 'DOM') %}
    <button>Créer</button>
{% endif %}
```

### ✅ **Performance optimisée**
- Cache automatique des autorisations
- Symfony Profiler pour le débogage

### ✅ **Tests simplifiés**
```php
public function testDomAccess()
{
    $client = static::createClient();
    $client->request('GET', '/rh/ordre-de-mission/dom-first-form');
    
    $this->assertResponseIsSuccessful();
}
```

## 📋 **Checklist de migration**

- [ ] Nouveau projet Symfony 5 créé
- [ ] Bundles installés
- [ ] Entités copiées
- [ ] Contrôleurs migrés
- [ ] Services configurés
- [ ] Templates mis à jour
- [ ] Tests effectués
- [ ] Application fonctionnelle

## 🚀 **Prochaines étapes**

1. **Commencer par un contrôleur simple**
2. **Tester chaque étape**
3. **Migrer progressivement**
4. **Valider à chaque étape**

Voulez-vous que je vous aide avec une étape spécifique ?
