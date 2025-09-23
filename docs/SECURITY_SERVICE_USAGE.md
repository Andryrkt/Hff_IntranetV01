# Guide d'utilisation du SecurityService

## Vue d'ensemble

Le `SecurityService` centralise la logique de vérification des sessions utilisateur et des autorisations d'accès. Il remplace les appels répétitifs à `verifierSessionUtilisateur()` et `autorisationAcces()` dans les contrôleurs.

## Utilisation

### 1. Injection de dépendance dans le contrôleur

```php
<?php

namespace App\Controller\dom;

use App\Controller\Controller;
use App\Service\security\SecurityService;
use App\Entity\admin\Application;

class MonController extends Controller
{
    private $securityService;

    public function __construct(SecurityService $securityService)
    {
        parent::__construct();
        $this->securityService = $securityService;
    }

    public function maMethode()
    {
        // Vérification complète : session + autorisation
        $this->securityService->verifyUserAccess($this->getUser(), Application::ID_DOM);
        
        // Votre logique métier ici...
    }
}
```

### 2. Méthodes disponibles

#### `verifyUserAccess($user, $applicationId)`
Vérifie à la fois la session utilisateur et les autorisations d'accès.

```php
// Exemple d'utilisation
$this->securityService->verifyUserAccess($this->getUser(), Application::ID_DOM);
```

#### `verifyUserSession()`
Vérifie uniquement que l'utilisateur est connecté.

```php
// Exemple d'utilisation
$this->securityService->verifyUserSession();
```

#### `verifyUserAuthorization($user, $applicationId)`
Vérifie uniquement les autorisations d'accès.

```php
// Exemple d'utilisation
$this->securityService->verifyUserAuthorization($this->getUser(), Application::ID_DOM);
```

### 3. Gestion des erreurs

Le service lève des exceptions `RuntimeException` avec des messages explicites :

```php
try {
    $this->securityService->verifyUserAccess($this->getUser(), Application::ID_DOM);
} catch (\RuntimeException $e) {
    // Gérer l'erreur (redirection, message d'erreur, etc.)
    $this->addFlash('error', $e->getMessage());
    return $this->redirectToRoute('login');
}
```

## Avantages

- ✅ **Code plus propre** : Une seule ligne au lieu de deux
- ✅ **Réutilisabilité** : Peut être utilisé dans tous les contrôleurs
- ✅ **Maintenabilité** : Logique centralisée
- ✅ **Testabilité** : Plus facile à tester unitairement
- ✅ **Flexibilité** : Méthodes séparées pour différents besoins

## Migration

### Avant
```php
public function maMethode()
{
    //verification si user connecter
    $this->verifierSessionUtilisateur();

    /** Autorisation accées */
    $this->autorisationAcces($this->getUser(), Application::ID_DOM);
    /** FIN AUtorisation acées */
    
    // Logique métier...
}
```

### Après
```php
public function maMethode()
{
    // Vérification de la session utilisateur et des autorisations d'accès
    $this->securityService->verifyUserAccess($this->getUser(), Application::ID_DOM);
    
    // Logique métier...
}
```
