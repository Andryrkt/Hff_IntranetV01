# Architecture Refactorisée - Services de Validation des Devis Magasin

## Vue d'ensemble

La refactorisation du service `DevisMagasinValidationVdService` a été effectuée pour améliorer la maintenabilité, la testabilité et la séparation des responsabilités. L'architecture précédente monolithique a été remplacée par une architecture modulaire avec des validateurs spécialisés.

## Structure de l'architecture

```
src/Service/magasin/devis/
├── Config/
│   └── DevisMagasinValidationConfig.php          # Configuration centralisée
├── Validator/
│   ├── DevisMagasinFileValidator.php             # Validation des fichiers
│   ├── DevisMagasinStatusValidator.php           # Validation des statuts
│   ├── DevisMagasinContentValidator.php          # Validation du contenu
│   └── DevisMagasinValidationOrchestrator.php    # Orchestrateur principal
└── DevisMagasinValidationVdService.php           # Service principal (compatibilité)
```

## Composants

### 1. DevisMagasinValidationConfig
**Responsabilité :** Configuration centralisée
- Constantes pour les noms de champs de fichiers
- Patterns de validation des noms de fichiers
- Statuts bloquants par type de validation
- Messages d'erreur standardisés
- Routes de redirection

### 2. DevisMagasinFileValidator
**Responsabilité :** Validation des fichiers
- Vérification de la présence d'un fichier
- Validation du format du nom de fichier
- Vérification de la cohérence du numéro de devis dans le nom de fichier
- Gestion des notifications d'erreur spécifiques aux fichiers

### 3. DevisMagasinStatusValidator
**Responsabilité :** Validation des statuts
- Vérification des statuts bloquants pour la soumission générale
- Validation des statuts spécifiques à la validation de prix (VP)
- Vérification des statuts pour la validation de devis (VD)
- Gestion des cas complexes de changement de statut avec modifications de contenu

### 4. DevisMagasinContentValidator
**Responsabilité :** Validation du contenu
- Vérification de l'existence du devis
- Validation des modifications de lignes et montants
- Comparaison des données actuelles avec les données précédentes
- Gestion des notifications d'erreur spécifiques au contenu

### 5. DevisMagasinValidationOrchestrator
**Responsabilité :** Orchestration des validations
- Coordination de tous les validateurs spécialisés
- Exécution séquentielle des validations
- Délégation des responsabilités aux validateurs appropriés
- Interface unifiée pour les validations complexes

### 6. DevisMagasinValidationVdService (Service Principal)
**Responsabilité :** Compatibilité ascendante
- Maintien de l'interface existante
- Délégation à l'orchestrateur
- Marquage comme déprécié pour encourager la migration

## Avantages de la nouvelle architecture

### 1. Séparation des responsabilités
- Chaque validateur a une responsabilité unique et bien définie
- Réduction de la complexité cognitive
- Facilité de maintenance et de débogage

### 2. Testabilité améliorée
- Chaque validateur peut être testé indépendamment
- Injection de dépendances facilitée
- Tests unitaires plus ciblés et efficaces

### 3. Réutilisabilité
- Les validateurs peuvent être réutilisés dans d'autres contextes
- Configuration centralisée partagée
- Logique métier isolée et réutilisable

### 4. Extensibilité
- Ajout de nouveaux validateurs sans modification du code existant
- Configuration flexible via la classe de configuration
- Facilite l'ajout de nouvelles règles de validation

### 5. Maintenabilité
- Code plus lisible et organisé
- Réduction de la duplication de code
- Messages d'erreur centralisés et cohérents

## Migration et compatibilité

### Utilisation actuelle (compatible)
```php
$validationService = new DevisMagasinValidationVdService($historiqueService, $numeroDevis);
$validationService->validationAvantSoumission($repository, $numeroDevis, $newSumOfLines, $newSumOfMontant);
```

### Utilisation recommandée (nouvelle architecture)
```php
$orchestrator = new DevisMagasinValidationOrchestrator($historiqueService, $numeroDevis);
$isValid = $orchestrator->validateBeforeSubmission($repository, $numeroDevis, $newSumOfLines, $newSumOfMontant);
```

## Configuration

Toutes les constantes et configurations sont centralisées dans `DevisMagasinValidationConfig` :

```php
// Exemples d'utilisation
$filename = DevisMagasinValidationConfig::FILE_FIELD_NAME;
$pattern = DevisMagasinValidationConfig::FILENAME_PATTERN;
$blockingStatuses = DevisMagasinValidationConfig::BLOCKING_STATUSES;
$errorMessage = DevisMagasinValidationConfig::ERROR_MESSAGES['missing_identifier'];
```

## Prochaines étapes recommandées

1. **Migration progressive** : Remplacer progressivement les appels au service principal par l'orchestrateur
2. **Tests unitaires** : Créer des tests pour chaque validateur spécialisé
3. **Documentation** : Compléter la documentation des méthodes publiques
4. **Monitoring** : Ajouter des métriques de performance pour chaque type de validation
5. **Optimisation** : Implémenter le cache pour les requêtes répétitives

## Notes importantes

- Le service principal `DevisMagasinValidationVdService` est marqué comme déprécié mais reste fonctionnel
- Toutes les méthodes existantes sont maintenues pour la compatibilité ascendante
- La nouvelle architecture est rétrocompatible avec le code existant
- Les `exit` dans l'ancienne méthode `validationAvantSoumission` sont maintenus pour la compatibilité mais déconseillés
