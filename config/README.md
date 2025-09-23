# Configuration Hffintranet

## Configuration Sécurisée

Cette application utilise maintenant des variables d'environnement pour sécuriser les informations sensibles comme les mots de passe et les chaînes de connexion.

## Démarrage rapide

### 1. Configuration initiale

```bash
# Exécuter le script de configuration
php config/setup-env.php
```

### 2. Configuration manuelle

```bash
# Copier le fichier d'exemple
cp config/env.example .env

# Éditer le fichier .env avec vos vraies valeurs
nano .env
```

### 3. Vérification

```bash
# Vérifier que l'application fonctionne
php -S localhost:8000 -t public
```

## Fichiers importants

- `.env` - Configuration locale (NE PAS COMMITER)
- `config/env.example` - Modèle de configuration
- `config/SECURITE_CONFIGURATION.md` - Documentation complète
- `config/setup-env.php` - Script d'aide à la configuration

## Sécurité

⚠️ **IMPORTANT** : Ne jamais commiter le fichier `.env` qui contient les vraies valeurs sensibles.

## Support

Consultez `config/SECURITE_CONFIGURATION.md` pour la documentation complète.
