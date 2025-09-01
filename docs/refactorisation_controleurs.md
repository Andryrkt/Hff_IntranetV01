# Guide de Refactorisation des Contrôleurs

## 🎯 **Objectif**

Refactoriser tous les contrôleurs existants pour utiliser l'injection de dépendances au lieu de l'ancienne approche statique.

## 📋 **Contrôleurs déjà refactorisés**

- ✅ `HomeControllerRefactored` → `BaseController`
- ✅ `AuthentificationRefactored` → `BaseController`

## 🔧 **Processus de refactorisation**

### 1. **Créer une nouvelle classe refactorisée**

```php
<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;

class MonControllerRefactored extends BaseController
{
    public function __construct(
        \Doctrine\ORM\EntityManagerInterface $entityManager,
        \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator,
        \Twig\Environment $twig,
        \Symfony\Component\Form\FormFactoryInterface $formFactory,
        \Symfony\Component\HttpFoundation\Session\SessionInterface $session,
        \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage,
        \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker,
        \App\Service\FusionPdf $fusionPdf,
        \App\Model\LdapModel $ldapModel,
        \App\Model\ProfilModel $profilModel,
        \App\Model\badm\BadmModel $badmModel,
        \App\Model\admin\personnel\PersonnelModel $personnelModel,
        \App\Model\dom\DomModel $domModel,
        \App\Model\da\DaModel $daModel,
        \App\Model\dom\DomDetailModel $domDetailModel,
        \App\Model\dom\DomDuplicationModel $domDuplicationModel,
        \App\Model\dom\DomListModel $domListModel,
        \App\Model\dit\DitModel $ditModel,
        \App\Model\TransferDonnerModel $transferDonnerModel,
        \App\Service\SessionManagerService $sessionManagerService,
        \App\Service\ExcelService $excelService
    ) {
        parent::__construct(
            $entityManager,
            $urlGenerator,
            $twig,
            $formFactory,
            $session,
            $tokenStorage,
            $authorizationChecker,
            $fusionPdf,
            $ldapModel,
            $profilModel,
            $badmModel,
            $personnelModel,
            $domModel,
            $daModel,
            $domDetailModel,
            $domDuplicationModel,
            $domListModel,
            $ditModel,
            $transferDonnerModel,
            $sessionManagerService,
            $excelService
        );
    }

    /**
     * @Route("/ma-route", name="ma_route")
     */
    public function maMethode()
    {
        // Utiliser les services injectés
        $em = $this->getEntityManager();
        $twig = $this->getTwig();
        
        // Remplacer self::$twig->display par $this->render
        return $this->render('mon_template.html.twig', [
            'data' => 'value'
        ]);
    }
}
```

### 2. **Changements principaux à effectuer**

#### **Héritage**
```php
// AVANT
class MonController extends Controller

// APRÈS
class MonControllerRefactored extends BaseController
```

#### **Constructeur**
```php
// AVANT
public function __construct()
{
    parent::__construct();
    // Pas d'injection de dépendances
}

// APRÈS
public function __construct(
    EntityManagerInterface $entityManager,
    UrlGeneratorInterface $urlGenerator,
    // ... tous les services
) {
    parent::__construct(
        $entityManager,
        $urlGenerator,
        // ... tous les services
    );
}
```

#### **Accès aux services**
```php
// AVANT
self::$em->getRepository(Entity::class)
self::$twig->display('template.html.twig', $context)

// APRÈS
$this->getEntityManager()->getRepository(Entity::class)
$this->render('template.html.twig', $context)
```

#### **Redirections**
```php
// AVANT
$this->redirectToRoute('route_name')

// APRÈS
$this->redirectToRoute('route_name')  // Même méthode, mais plus de return
```

### 3. **Méthodes helper disponibles dans BaseController**

- `$this->render($template, $context)` → Retourne une Response
- `$this->redirectToRouteResponse($routeName, $params)` → Retourne une RedirectResponse
- `$this->redirectToResponse($url)` → Retourne une RedirectResponse
- `$this->jsonResponse($data, $status)` → Retourne une Response JSON
- `$this->isUserConnected()` → Vérifie si l'utilisateur est connecté
- `$this->getCurrentUserId()` → Obtient l'ID de l'utilisateur connecté
- `$this->getCurrentUsername()` → Obtient le nom de l'utilisateur connecté

### 4. **Services disponibles via getters**

- `$this->getEntityManager()` → EntityManager
- `$this->getTwig()` → Twig Environment
- `$this->getFormFactory()` → Form Factory
- `$this->getUrlGenerator()` → URL Generator
- `$this->getSession()` → Session
- `$this->getTokenStorage()` → Token Storage
- `$this->getAuthorizationChecker()` → Authorization Checker

### 5. **Services disponibles directement**

- `$this->fusionPdf` → Service FusionPdf
- `$this->ldap` → LdapModel
- `$this->profilModel` → ProfilModel
- `$this->badm` → BadmModel
- `$this->Person` → PersonnelModel
- `$this->DomModel` → DomModel
- `$this->DaModel` → DaModel
- `$this->sessionService` → SessionManagerService
- `$this->excelService` → ExcelService

## 📝 **Liste des contrôleurs à refactoriser**

### **Contrôleurs principaux**
- [ ] `Transfer04Controller.php`
- [ ] `MigrationDaController.php`
- [ ] `LdapControl.php`

### **Contrôleurs par module**
- [ ] `admin/` (14 contrôleurs)
- [ ] `badm/` (8 contrôleurs)
- [ ] `bordereau/` (1 contrôleur)
- [ ] `cde/` (1 contrôleur)
- [ ] `da/` (2 contrôleurs)
- [ ] `ddp/` (4 contrôleurs)
- [ ] `dit/` (6 contrôleurs)
- [ ] `dom/` (6 contrôleurs)
- [ ] `dw/` (2 contrôleurs)
- [ ] `magasin/` (7 dossiers)
- [ ] `mutation/` (1 contrôleur)
- [ ] `pdf/` (dossier)
- [ ] `planning/` (2 contrôleurs)
- [ ] `planningAtelier/` (1 contrôleur)
- [ ] `tik/` (7 contrôleurs)

## 🧪 **Test de la refactorisation**

Pour chaque contrôleur refactorisé, créer un test :

```php
// Dans test_refactored_controllers.php
try {
    $controller = new \App\Controller\MonControllerRefactored(
        $container->get('doctrine.orm.entity_manager'),
        $container->get('router'),
        // ... tous les services
    );
    
    echo "✅ MonControllerRefactored instancié avec succès\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}
```

## 🚀 **Avantages de la refactorisation**

1. **Testabilité** : Les contrôleurs peuvent être testés unitairement
2. **Maintenabilité** : Code plus clair et structuré
3. **Flexibilité** : Facile de changer les implémentations
4. **Compatibilité Symfony 5** : Architecture prête pour la migration
5. **Injection de dépendances** : Gestion automatique des dépendances

## ⚠️ **Points d'attention**

1. **Toujours tester** après chaque refactorisation
2. **Vérifier les imports** et namespaces
3. **Maintenir la compatibilité** avec l'ancien code pendant la transition
4. **Documenter** les changements effectués

## 📚 **Ressources**

- `src/Controller/ControllerDI.php` : Classe de base avec injection de dépendances
- `src/Controller/BaseController.php` : Classe avec méthodes helper
- `src/Controller/HomeControllerRefactored.php` : Exemple de refactorisation
- `src/Controller/AuthentificationRefactored.php` : Exemple de refactorisation
- `test_refactored_controllers.php` : Script de test
