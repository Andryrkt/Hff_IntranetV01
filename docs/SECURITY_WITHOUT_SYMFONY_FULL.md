# Guide de sécurité sans Symfony complet

## Vue d'ensemble

Puisque vous utilisez des composants Symfony individuels et non le framework complet, l'annotation `@IsGranted` n'est pas disponible. Voici les alternatives pratiques pour gérer les autorisations.

## 🎯 **Solution recommandée : SecurityTrait**

### 1. Utilisation dans les contrôleurs

```php
<?php

namespace App\Controller\dom;

use App\Controller\Controller;
use App\Controller\Traits\SecurityTrait;
use App\Service\security\SecurityService;

class DomController extends Controller
{
    use SecurityTrait;

    private $securityService;

    public function __construct(SecurityService $securityService)
    {
        parent::__construct();
        $this->securityService = $securityService;
    }

    public function index()
    {
        // Vérification simple d'accès
        $this->requireAccess('DOM');
        
        // Votre logique métier ici...
    }

    public function create()
    {
        // Vérification d'accès avec permission de création
        $this->requireCreate('DOM');
        
        // Logique de création...
    }

    public function edit($id)
    {
        // Vérification d'accès avec permission de modification
        $this->requireEdit('DOM');
        
        // Logique de modification...
    }

    public function delete($id)
    {
        // Vérification d'accès avec permission de suppression
        $this->requireDelete('DOM');
        
        // Logique de suppression...
    }
}
```

### 2. Vérifications conditionnelles

```php
public function dashboard()
{
    // Vérification simple d'accès
    $this->requireAccess('DOM');

    // Vérifications conditionnelles
    if ($this->canCreate('DOM')) {
        // Afficher le bouton de création
        $showCreateButton = true;
    }

    if ($this->canEdit('DOM')) {
        // Afficher les boutons de modification
        $showEditButtons = true;
    }

    if ($this->canDelete('DOM')) {
        // Afficher les boutons de suppression
        $showDeleteButtons = true;
    }

    return $this->render('dashboard.html.twig', [
        'showCreateButton' => $showCreateButton ?? false,
        'showEditButtons' => $showEditButtons ?? false,
        'showDeleteButtons' => $showDeleteButtons ?? false,
    ]);
}
```

### 3. Gestion des erreurs

```php
public function sensitiveAction()
{
    try {
        $this->requireAccess('DOM');
        // Logique sensible...
    } catch (AccessDeniedException $e) {
        $this->addFlash('error', 'Accès refusé : ' . $e->getMessage());
        return $this->redirectToRoute('home');
    }
}
```

## 🔧 **Méthodes disponibles dans SecurityTrait**

### **Vérifications avec exceptions (recommandé)**
- `requireAccess($application)` - Vérifie l'accès de base
- `requireCreate($application)` - Vérifie la permission de création
- `requireEdit($application)` - Vérifie la permission de modification
- `requireDelete($application)` - Vérifie la permission de suppression

### **Vérifications booléennes**
- `canAccess($application)` - Retourne true/false pour l'accès
- `canCreate($application)` - Retourne true/false pour la création
- `canEdit($application)` - Retourne true/false pour la modification
- `canDelete($application)` - Retourne true/false pour la suppression

## 📝 **Exemples d'utilisation**

### **Dans un contrôleur DOM**

```php
class DomController extends Controller
{
    use SecurityTrait;

    public function list()
    {
        $this->requireAccess('DOM');
        
        // Récupérer la liste des DOM
        $doms = $this->getEntityManager()->getRepository(Dom::class)->findAll();
        
        return $this->render('dom/list.html.twig', ['doms' => $doms]);
    }

    public function create(Request $request)
    {
        $this->requireCreate('DOM');
        
        $form = $this->createForm(DomType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Logique de création...
        }
        
        return $this->render('dom/create.html.twig', ['form' => $form->createView()]);
    }

    public function edit($id, Request $request)
    {
        $this->requireEdit('DOM');
        
        $dom = $this->getEntityManager()->getRepository(Dom::class)->find($id);
        if (!$dom) {
            throw $this->createNotFoundException('DOM non trouvé');
        }
        
        // Logique de modification...
    }

    public function delete($id)
    {
        $this->requireDelete('DOM');
        
        $dom = $this->getEntityManager()->getRepository(Dom::class)->find($id);
        if (!$dom) {
            throw $this->createNotFoundException('DOM non trouvé');
        }
        
        // Logique de suppression...
    }
}
```

### **Dans un contrôleur TIK**

```php
class TikController extends Controller
{
    use SecurityTrait;

    public function index()
    {
        $this->requireAccess('TIK');
        
        // Logique TIK...
    }

    public function create()
    {
        $this->requireCreate('TIK');
        
        // Logique de création TIK...
    }
}
```

## 🎨 **Utilisation dans les templates Twig**

### **Avec des variables passées depuis le contrôleur**

```twig
{# templates/dom/list.html.twig #}

{% if showCreateButton %}
    <a href="{{ path('dom_create') }}" class="btn btn-primary">Créer un DOM</a>
{% endif %}

{% for dom in doms %}
    <div class="dom-item">
        <h3>{{ dom.title }}</h3>
        
        {% if showEditButtons %}
            <a href="{{ path('dom_edit', {id: dom.id}) }}" class="btn btn-sm btn-warning">Modifier</a>
        {% endif %}
        
        {% if showDeleteButtons %}
            <a href="{{ path('dom_delete', {id: dom.id}) }}" class="btn btn-sm btn-danger">Supprimer</a>
        {% endif %}
    </div>
{% endfor %}
```

### **Avec des fonctions Twig personnalisées (optionnel)**

Si vous voulez utiliser `is_granted()` dans Twig, vous pouvez créer une fonction Twig personnalisée :

```php
// Dans votre service Twig
class SecurityTwigExtension extends AbstractExtension
{
    private $securityService;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('is_granted', [$this, 'isGranted']),
        ];
    }

    public function isGranted(string $application, string $attribute = 'ACCESS'): bool
    {
        return $this->securityService->isGranted($application, $attribute);
    }
}
```

## 🚀 **Avantages de cette approche**

### ✅ **Compatible avec votre environnement**
- Fonctionne avec des composants Symfony individuels
- Pas besoin du framework complet

### ✅ **Code propre et lisible**
- `$this->requireAccess('DOM')` est très expressif
- Plus clair que les anciennes méthodes

### ✅ **Flexibilité**
- Support de différents niveaux d'autorisation
- Vérifications conditionnelles faciles

### ✅ **Maintenabilité**
- Logique centralisée dans le Voter
- Facile à modifier et étendre

### ✅ **Performance**
- Cache automatique des décisions d'autorisation
- Pas de requêtes répétitives

## 📋 **Migration depuis l'ancien système**

### **Avant**
```php
public function maMethode()
{
    $this->verifierSessionUtilisateur();
    $this->autorisationAcces($this->getUser(), Application::ID_DOM);
    // Logique métier...
}
```

### **Après**
```php
public function maMethode()
{
    $this->requireAccess('DOM');
    // Logique métier...
}
```

## 🎯 **Conclusion**

Cette approche vous donne tous les avantages d'un système de sécurité moderne sans nécessiter le framework Symfony complet. Le code est plus propre, plus maintenable et plus performant que l'ancien système.
