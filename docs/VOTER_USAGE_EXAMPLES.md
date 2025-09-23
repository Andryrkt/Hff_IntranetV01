# Guide d'utilisation du Voter et des annotations de sécurité

## Vue d'ensemble

Le système de Voters de Symfony offre une approche plus moderne et intégrée pour gérer les autorisations. Voici comment l'utiliser dans vos contrôleurs.

## 1. Utilisation avec le SecurityService (approche actuelle)

```php
<?php

namespace App\Controller\dom;

use App\Controller\Controller;
use App\Service\security\SecurityService;
use App\Security\Voter\ApplicationVoter;

class DomController extends Controller
{
    private $securityService;

    public function __construct(SecurityService $securityService)
    {
        parent::__construct();
        $this->securityService = $securityService;
    }

    public function index()
    {
        // Vérification simple d'accès
        $this->securityService->verifyUserAccess($this->getUser(), ApplicationVoter::DOM);
        
        // Vérifications spécifiques
        if ($this->securityService->canCreate(ApplicationVoter::DOM)) {
            // Logique de création
        }
        
        if ($this->securityService->canEdit(ApplicationVoter::DOM)) {
            // Logique de modification
        }
    }
}
```

## 2. Utilisation avec les annotations @IsGranted (recommandé)

### Installation du bundle (si pas déjà fait)
```bash
composer require sensio/framework-extra-bundle
```

### Configuration dans config/packages/framework.yaml
```yaml
sensio_framework_extra:
    security:
        annotations: true
```

### Utilisation dans les contrôleurs

```php
<?php

namespace App\Controller\dom;

use App\Controller\Controller;
use App\Security\Voter\ApplicationVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/rh/ordre-de-mission")
 */
class DomController extends Controller
{
    /**
     * @Route("/dom-first-form", name="dom_first_form")
     * @IsGranted("ACCESS", subject="DOM")
     */
    public function firstForm()
    {
        // L'autorisation est vérifiée automatiquement
        // Votre logique métier ici...
    }

    /**
     * @Route("/dom-create", name="dom_create")
     * @IsGranted("CREATE", subject="DOM")
     */
    public function create()
    {
        // Seuls les utilisateurs avec permission CREATE peuvent accéder
    }

    /**
     * @Route("/dom-edit/{id}", name="dom_edit")
     * @IsGranted("EDIT", subject="DOM")
     */
    public function edit($id)
    {
        // Seuls les utilisateurs avec permission EDIT peuvent accéder
    }

    /**
     * @Route("/dom-delete/{id}", name="dom_delete")
     * @IsGranted("DELETE", subject="DOM")
     */
    public function delete($id)
    {
        // Seuls les utilisateurs avec permission DELETE peuvent accéder
    }
}
```

## 3. Utilisation dans les templates Twig

```twig
{# Vérification d'autorisation dans un template #}
{% if is_granted('ACCESS', 'DOM') %}
    <a href="{{ path('dom_first_form') }}">Créer un DOM</a>
{% endif %}

{% if is_granted('CREATE', 'DOM') %}
    <button>Nouveau DOM</button>
{% endif %}

{% if is_granted('EDIT', 'DOM') %}
    <button>Modifier</button>
{% endif %}

{% if is_granted('DELETE', 'DOM') %}
    <button>Supprimer</button>
{% endif %}
```

## 4. Utilisation programmatique

```php
// Dans un contrôleur ou un service
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MonService
{
    private $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function maMethode()
    {
        // Vérification simple
        if ($this->authorizationChecker->isGranted('ACCESS', 'DOM')) {
            // L'utilisateur peut accéder au DOM
        }

        // Vérification avec attribut spécifique
        if ($this->authorizationChecker->isGranted('CREATE', 'DOM')) {
            // L'utilisateur peut créer des DOM
        }
    }
}
```

## 5. Gestion des erreurs

### Avec annotations
```php
/**
 * @Route("/dom-secret", name="dom_secret")
 * @IsGranted("ACCESS", subject="DOM", message="Accès refusé à cette section")
 */
public function secret()
{
    // Si l'utilisateur n'a pas accès, une AccessDeniedException est levée
    // avec le message personnalisé
}
```

### Avec SecurityService
```php
try {
    $this->securityService->verifyUserAccess($this->getUser(), 'DOM');
} catch (AccessDeniedException $e) {
    $this->addFlash('error', 'Accès refusé : ' . $e->getMessage());
    return $this->redirectToRoute('home');
}
```

## 6. Avantages du Voter

### ✅ **Intégration native Symfony**
- Utilise le système de sécurité standard de Symfony
- Compatible avec tous les composants de sécurité

### ✅ **Cache automatique**
- Les décisions d'autorisation sont mises en cache
- Améliore les performances

### ✅ **Flexibilité**
- Support de différents attributs (ACCESS, VIEW, CREATE, EDIT, DELETE)
- Logique complexe centralisée dans le Voter

### ✅ **Testabilité**
- Facile à tester unitairement
- Mocking simple des autorisations

### ✅ **Annotations**
- Code plus propre avec `@IsGranted`
- Séparation claire entre sécurité et logique métier

## 7. Migration depuis l'ancien système

### Avant
```php
public function maMethode()
{
    $this->verifierSessionUtilisateur();
    $this->autorisationAcces($this->getUser(), Application::ID_DOM);
    // Logique métier...
}
```

### Après (avec SecurityService)
```php
public function maMethode()
{
    $this->securityService->verifyUserAccess($this->getUser(), 'DOM');
    // Logique métier...
}
```

### Après (avec annotations)
```php
/**
 * @IsGranted("ACCESS", subject="DOM")
 */
public function maMethode()
{
    // Logique métier...
}
```

## Conclusion

Le Voter est effectivement la meilleure approche pour gérer les autorisations dans Symfony. Il offre plus de flexibilité, de performance et d'intégration que les services personnalisés.
