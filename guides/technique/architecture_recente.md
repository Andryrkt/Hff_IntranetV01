# Documentation des Composants Récents

## Introduction

Ce document décrit l'architecture et l'utilisation des composants réutilisables qui ont été récemment ajoutés au projet. Il couvre le système de validation avancé et les nouveaux types de formulaires.

---

## 1. Architecture de Validation Avancée

Pour centraliser et réutiliser la logique de validation, une architecture à plusieurs niveaux a été mise en place. Elle permet de séparer clairement les outils génériques, les règles métier spécifiques et leur utilisation dans les contrôleurs.

### Composants Clés

#### a. `ValidationServiceBase` (Classe de Base Abstraite)

- **Fichier**: `src/Service/validation/ValidationServiceBase.php`
- **Rôle**: C'est une "boîte à outils" qui contient des méthodes de validation très génériques, utilisables par n'importe quel service qui en hérite. Elle n'est pas destinée à être utilisée directement.

**Méthodes disponibles**:
- `isFileSubmitted(form, fieldName)`: Vérifie si un fichier a été soumis dans un champ de formulaire.
- `matchPattern(subject, pattern)`: Vérifie si une chaîne correspond à une expression régulière.
- `matchNumberAfterUnderscore(subject, expectedNumber)`: Extrait le premier nombre trouvé après un `_` dans une chaîne et le compare à un nombre attendu.
- `isIdentifierMissing(identifier)`: Vérifie si un identifiant est `null`.
- `isStatusBlocking(repository, identifier, blockingStatuses)`: Logique générique pour vérifier un statut bloquant (voir interface ci-dessous).

#### b. `StatusRepositoryInterface` (Interface)

- **Fichier**: `src/Repository/Interfaces/StatusRepositoryInterface.php`
- **Rôle**: Définit un "contrat" pour les Repositories. Tout Repository qui `implement` cette interface s'engage à fournir une méthode `findLatestStatusByIdentifier()`. Cela permet à `ValidationServiceBase` de fonctionner avec n'importe quel type de repository sans le connaître à l'avance (découplage).

#### c. `DevisMagasinValidationService` (Service Spécifique)

- **Fichier**: `src/Service/magasin/devis/DevisMagasinValidationService.php`
- **Rôle**: Hérite de `ValidationServiceBase` et orchestre les appels aux méthodes génériques pour appliquer des règles métier complexes et spécifiques au contexte des "Devis Magasin". C'est ici que les messages d'erreur sont définis et que les notifications sont envoyées.

**Exemple d'utilisation dans un Contrôleur**:

```php
// Dans DevisMagasinController.php

// Instanciation du service
$validationService = new DevisMagasinValidationService(
    $this->historiqueOperationDeviMagasinService, 
    $numeroDevis
);

// Appel d'une règle de validation
if (!$validationService->checkMissingIdentifier($numeroDevis)) {
    return; // Arrête le traitement
}

// Appel d'une autre règle
$devisRepo = self::$em->getRepository(DevisMagasin::class);
if ($validationService->checkBlockingStatusOnSubmission($devisRepo, $numeroDevis)) {
    return; 
}
```

---

## 2. Composants de Formulaire Réutilisables

Deux nouveaux types de formulaire ont été créés dans `src/Form/common/` pour être facilement intégrés dans n'importe quel formulaire de l'application.

### a. `AgenceServiceType`

- **Rôle**: Affiche une paire de listes déroulantes dépendantes. La sélection d'une `Agence` met à jour dynamiquement la liste des `Service`s disponibles.
- **Utilisation**: Intégrez-le dans un autre formulaire avec `$builder->add()`.

**Exemple d'intégration**:

```php
use App\Form\common\AgenceServiceType;

// ... dans la méthode buildForm d'un autre Type

$builder
    ->add('emetteur', AgenceServiceType::class, [
        'label' => false, // Masque le label du groupe
        'required' => false,
        'agence_label' => 'Agence Emetteur', // Label du champ Agence
        'service_label' => 'Service Emetteur', // Label du champ Service
    ])
    ->add('debitteur', AgenceServiceType::class, [
        'label' => false,
        'required' => false,
        'agence_label' => 'Agence Debiteur',
        'service_label' => 'Service Debiteur',
    ]);
```

### b. `DateRangeType`

- **Rôle**: Affiche une paire de champs de date pour sélectionner une plage (début et fin).
- **Utilisation**: S'intègre de la même manière. Il est configuré en `mapped => false` par défaut, ce qui signifie que le contrôleur doit récupérer les valeurs `debut` et `fin` manuellement depuis les données du formulaire.

**Exemple d'intégration**:

```php
use App\Form\common\DateRangeType;

// ... dans la méthode buildForm d'un autre Type

$builder->add('dateCreation', DateRangeType::class, [
    'label' => false, // Masque le label du groupe
    'debut_label' => 'Créé après le',
    'fin_label' => 'Créé avant le',
]);
```
