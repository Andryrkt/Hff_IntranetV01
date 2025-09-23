# Scripts et Outils

Ce dossier contient tous les scripts utilitaires et de maintenance du projet.

## Structure

### 📁 maintenance/
Scripts de maintenance du système :
- `check_proxies.php` - Vérification des proxies Doctrine

### Scripts de migration et correction
- `ajouter_import_basecontroller.php` - Ajout d'imports BaseController
- `convertir_display_en_response.php` - Conversion display en response
- `corriger_controllers_specifiques.php` - Correction des contrôleurs
- `corriger_render_controllers.php` - Correction des rendus
- `corriger_render_simple.php` - Correction des rendus simples
- `corriger_session_service.php` - Correction du service de session
- `ensure_proxies.php` - Assurance des proxies
- `migrate_controller.php` - Migration des contrôleurs
- `nettoyer_fichiers_dupliques.php` - Nettoyage des doublons
- `remplacer_references_static.php` - Remplacement des références statiques
- `remplacer_validator_global.php` - Remplacement du validateur global
- `simplifier_imports_controller.php` - Simplification des imports
- `verifier_services_historique_simple.php` - Vérification des services
- `verifier_services_historique.php` - Vérification des services historiques

### Scripts de configuration
- `setup_ldap_test.php` - Configuration LDAP de test
- `update_schema.php` - Mise à jour du schéma de base de données

## Utilisation

Ces scripts sont généralement exécutés via la ligne de commande PHP :

```bash
php scripts/nom_du_script.php
```

## Attention

⚠️ **Important** : Ces scripts modifient souvent la structure du code ou de la base de données. 
Toujours faire une sauvegarde avant d'exécuter un script de migration ou de correction.

## Maintenance

Les scripts doivent être documentés et testés avant utilisation en production.
