# Organisation des services.yaml - Séparation des Fichiers

## 🎯 **Problème Identifié**

Un `services.yaml` trop volumineux devient difficile à maintenir et à naviguer.

## 🚀 **Solution : Séparation des Fichiers de Configuration**

### **Structure Recommandée**

```
config/
├── services.yaml                    # Configuration principale
├── services/
│   ├── services_pdf.yaml           # Services PDF
│   ├── services_fichier.yaml       # Services de fichiers
│   ├── services_controller.yaml    # Contrôleurs
│   ├── services_form.yaml          # Formulaires
│   └── services_custom.yaml        # Services personnalisés
└── packages/
    ├── dev/
    └── prod/
```

## 📁 **Fichier 1 : services.yaml (Principal)**

```yaml
# config/services.yaml
imports:
    - { resource: 'services/services_pdf.yaml' }
    - { resource: 'services/services_fichier.yaml' }
    - { resource: 'services/services_controller.yaml' }
    - { resource: 'services/services_form.yaml' }
    - { resource: 'services/services_custom.yaml' }

# Configuration par défaut pour tous les services
services:
    _defaults:
        autowire: true
        autoconfigure: true
        public: false

    # Services de base (déjà existants)
    App\Service\:
        resource: '../src/Service/*'
        exclude:
            - '../src/Service/dit/or/'
        tags: ['app.service']
        public: true

    App\Model\:
        resource: '../src/Model/*'
        tags: ['app.model']
        public: true

    App\Repository\:
        resource: '../src/Repository/*'
        tags: ['app.repository']
        public: true
```

## 📁 **Fichier 2 : services_pdf.yaml**

```yaml
# config/services/services_pdf.yaml
services:
    # Configuration de la classe parente PDF
    App\Service\genererPdf\GeneratePdf:
        arguments:
            $baseCheminDuFichier: '%env(BASE_PATH_FICHIER)%/'
            $baseCheminDocuware: '%env(BASE_PATH_DOCUWARE)%/'
        public: true

    # Services PDF spécialisés
    App\Service\genererPdf\GeneratePdfDevisMagasin:
        parent: App\Service\genererPdf\GeneratePdf
        public: true

    # Ajoutez d'autres services PDF ici
    # App\Service\genererPdf\GeneratePdfDevisDit:
    #     parent: App\Service\genererPdf\GeneratePdf
    #     public: true

    # App\Service\genererPdf\GeneratePdfFacture:
    #     parent: App\Service\genererPdf\GeneratePdf
    #     public: true
```

## 📁 **Fichier 3 : services_fichier.yaml**

```yaml
# config/services/services_fichier.yaml
services:
    # Services de gestion des fichiers
    App\Service\fichier\UploderFileService:
        arguments:
            $cheminDeBase: '%env(BASE_PATH_FICHIER)%/magasin/devis/'
        public: true

    # Ajoutez d'autres services de fichiers ici
    # App\Service\fichier\FileManagerService:
    #     arguments:
    #         $basePath: '%env(BASE_PATH_FICHIER)%/'
    #     public: true
```

## 📁 **Fichier 4 : services_controller.yaml**

```yaml
# config/services/services_controller.yaml
services:
    # Contrôleurs avec injection de dépendances
    App\Controller\magasin\devis\DevisMagasinVerificationPrixControllerAutoWired:
        arguments:
            $cheminBaseUpload: '%env(BASE_PATH_FICHIER)%/magasin/devis/'
        public: true

    # Ajoutez d'autres contrôleurs ici
    # App\Controller\magasin\devis\DevisMagasinController:
    #     public: true
```

## 📁 **Fichier 5 : services_form.yaml**

```yaml
# config/services/services_form.yaml
services:
    # Formulaires - injection manuelle
    App\Form\:
        resource: '../src/Form/*'
        autowire: false
        public: true

    # Formulaires spécifiques avec configuration
    # App\Form\magasin\devis\DevisMagasinType:
    #     arguments:
    #         $options: ['validation_groups' => ['Default']]
    #     public: true
```

## 📁 **Fichier 6 : services_custom.yaml**

```yaml
# config/services/services_custom.yaml
services:
    # Services personnalisés avec configuration spécifique
    App\Service\historiqueOperation\HistoriqueOperationDevisMagasinService:
        arguments:
            $config: ['notification_enabled' => true]
        public: true

    # Services de validation
    App\Service\magasin\devis\DevisMagasinValidationVpService:
        public: true

    # Services de version
    App\Service\autres\VersionService:
        public: true

    # Ajoutez d'autres services personnalisés ici
```

## 🎯 **Alternative : Organisation par Module**

### **Structure par Module**

```
config/
├── services.yaml
├── services/
│   ├── services_magasin.yaml       # Module magasin
│   ├── services_dit.yaml           # Module DIT
│   ├── services_dom.yaml           # Module DOM
│   ├── services_admin.yaml         # Module admin
│   └── services_common.yaml        # Services communs
```

### **services_magasin.yaml**

```yaml
# config/services/services_magasin.yaml
services:
    # Services PDF pour magasin
    App\Service\genererPdf\GeneratePdf:
        arguments:
            $baseCheminDuFichier: '%env(BASE_PATH_FICHIER)%/'
            $baseCheminDocuware: '%env(BASE_PATH_DOCUWARE)%/'
        public: true

    App\Service\genererPdf\GeneratePdfDevisMagasin:
        parent: App\Service\genererPdf\GeneratePdf
        public: true

    # Services de fichiers pour magasin
    App\Service\fichier\UploderFileService:
        arguments:
            $cheminDeBase: '%env(BASE_PATH_FICHIER)%/magasin/devis/'
        public: true

    # Contrôleurs magasin
    App\Controller\magasin\devis\DevisMagasinVerificationPrixControllerAutoWired:
        arguments:
            $cheminBaseUpload: '%env(BASE_PATH_FICHIER)%/magasin/devis/'
        public: true

    # Repositories magasin
    App\Repository\magasin\devis\DevisMagasinRepository:
        public: true
```

## 🚀 **Implémentation Étape par Étape**

### **Étape 1 : Créer le Dossier services**

```bash
mkdir config/services
```

### **Étape 2 : Créer les Fichiers de Configuration**

```bash
# Créer les fichiers
touch config/services/services_pdf.yaml
touch config/services/services_fichier.yaml
touch config/services/services_controller.yaml
touch config/services/services_form.yaml
touch config/services/services_custom.yaml
```

### **Étape 3 : Modifier services.yaml Principal**

```yaml
# config/services.yaml
imports:
    - { resource: 'services/services_pdf.yaml' }
    - { resource: 'services/services_fichier.yaml' }
    - { resource: 'services/services_controller.yaml' }
    - { resource: 'services/services_form.yaml' }
    - { resource: 'services/services_custom.yaml' }

# Configuration par défaut
services:
    _defaults:
        autowire: true
        autoconfigure: true
        public: false

    # Services de base
    App\Service\:
        resource: '../src/Service/*'
        exclude:
            - '../src/Service/dit/or/'
        tags: ['app.service']
        public: true

    App\Model\:
        resource: '../src/Model/*'
        tags: ['app.model']
        public: true

    App\Repository\:
        resource: '../src/Repository/*'
        tags: ['app.repository']
        public: true
```

### **Étape 4 : Déplacer les Configurations**

Déplacer les configurations spécifiques dans leurs fichiers respectifs.

## 🎉 **Avantages de Cette Organisation**

### **1. Maintenabilité**
- ✅ **Fichiers plus petits** et faciles à naviguer
- ✅ **Séparation des responsabilités** par type de service
- ✅ **Modifications ciblées** sans affecter le reste

### **2. Lisibilité**
- ✅ **Structure claire** et organisée
- ✅ **Facile à comprendre** pour les nouveaux développeurs
- ✅ **Documentation implicite** de l'architecture

### **3. Évolutivité**
- ✅ **Ajout facile** de nouveaux modules
- ✅ **Réorganisation** sans casser l'existant
- ✅ **Tests** plus faciles par module

### **4. Performance**
- ✅ **Chargement sélectif** des configurations
- ✅ **Cache optimisé** par fichier
- ✅ **Débogage** plus facile

## 🎯 **Recommandation Finale**

**Organisation par Type de Service** (première option) car :
- ✅ Plus simple à implémenter
- ✅ Séparation logique claire
- ✅ Facile à maintenir
- ✅ Respect des bonnes pratiques Symfony

**Votre idée de séparer les services.yaml est excellente !** 🚀
