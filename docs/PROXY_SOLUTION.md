# Solution pour les erreurs de proxies Doctrine

## Problème
L'erreur suivante se produit régulièrement :
```
Warning: require(C:\wamp64\www\Hffintranet\var\cache\proxies\__CG__AppEntityadminSociette.php): failed to open stream: No such file or directory
```

## Cause
Les proxies Doctrine sont supprimés lors du nettoyage du cache mais ne sont pas régénérés automatiquement.

## Solution implémentée

### 1. Vérification automatique dans index.php
Le fichier `index.php` vérifie maintenant automatiquement si les proxies existent au démarrage de l'application et les régénère si nécessaire.

### 2. Scripts de maintenance
- `scripts/ensure_proxies.php` : Script pour vérifier et régénérer les proxies
- `maintenance/check_proxies.php` : Script de maintenance pouvant être exécuté manuellement ou via cron

### 3. Amélioration de doctrineBootstrap.php
Le fichier `doctrineBootstrap.php` inclut maintenant une vérification des proxies.

## Utilisation

### Vérification manuelle
```bash
php maintenance/check_proxies.php
```

### Régénération forcée
```bash
# Supprimer le cache
rm -rf var/cache

# Les proxies seront automatiquement régénérés au prochain accès
```

## Avantages
- ✅ Plus d'erreurs de proxies manquants
- ✅ Régénération automatique
- ✅ Solution transparente pour l'utilisateur
- ✅ Scripts de maintenance disponibles
- ✅ Vérification au démarrage de l'application

## Notes
- La vérification se fait à chaque démarrage de l'application
- Les proxies sont régénérés uniquement s'ils sont manquants
- Aucun impact sur les performances en fonctionnement normal
