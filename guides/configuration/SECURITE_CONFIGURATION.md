# Configuration Sécurisée - Hffintranet

## Vue d'ensemble

Ce document explique comment configurer l'application Hffintranet de manière sécurisée en utilisant des variables d'environnement au lieu de valeurs codées en dur.

## Problème de sécurité résolu

**AVANT** : Les mots de passe et informations sensibles étaient codés en dur dans le fichier `config/services/services_custom.yaml` :
```yaml
App\Model\Connexion:
    arguments:
        $dbPassword: 'Hff@sql2024'  # ❌ Mot de passe visible dans le code
```

**APRÈS** : Utilisation de variables d'environnement :
```yaml
App\Model\Connexion:
    arguments:
        $dbPassword: '%env(DB_PASSWORD_SQLSERV)%'  # ✅ Mot de passe sécurisé
```

## Configuration requise

### 1. Créer le fichier .env

Créez un fichier `.env` à la racine du projet en copiant le modèle :

```bash
cp config/env.example .env
```

### 2. Configurer les variables d'environnement

Éditez le fichier `.env` et remplacez les valeurs d'exemple par vos vraies valeurs :

```env
# Base de données principale
DB_DNS_SQLSERV=HFF_INTRANET_V01_TEST_TEST
DB_USERNAME_SQLSERV=sa
DB_PASSWORD_SQLSERV=votre_mot_de_passe_ici

# Base de données Dote4
DB_DNS_SQLSERV_4=HFF_INTRANET_V04_TEST
DB_USERNAME_SQLSERV_4=sa
DB_PASSWORD_SQLSERV_4=votre_mot_de_passe_dote4

# Base de données Informix
DB_DNS_INFORMIX=IPS_HFFPROD_TEST
DB_USERNAME_INFORMIX=informix
DB_PASSWORD_INFORMIX=votre_mot_de_passe_informix

# Configuration LDAP
LDAP_HOST=192.168.0.1
LDAP_PORT=389
LDAP_DOMAIN=@@fraise.hff.mg
LDAP_DN=OU=HFF Users,DC=fraise,DC=hff,DC=mg

# Configuration Email
MAILER_HOST=smtp.gmail.com
MAILER_PORT=587
MAILER_USER=noreply@hff.mg
MAILER_PASSWORD=votre_mot_de_passe_email
```

### 3. Variables d'environnement disponibles

| Variable | Description | Exemple |
|----------|-------------|---------|
| `DB_DNS_SQLSERV` | DSN de la base de données principale | `HFF_INTRANET_V01_TEST_TEST` |
| `DB_USERNAME_SQLSERV` | Utilisateur de la base principale | `sa` |
| `DB_PASSWORD_SQLSERV` | Mot de passe de la base principale | `votre_mot_de_passe` |
| `DB_DNS_SQLSERV_4` | DSN de la base Dote4 | `HFF_INTRANET_V04_TEST` |
| `DB_USERNAME_SQLSERV_4` | Utilisateur Dote4 | `sa` |
| `DB_PASSWORD_SQLSERV_4` | Mot de passe Dote4 | `votre_mot_de_passe` |
| `DB_DNS_SQLSERV_4_GCOT` | DSN de la base Dote4 GCOT | `HFF_GCOT64` |
| `DB_DNS_INFORMIX` | DSN de la base Informix | `IPS_HFFPROD_TEST` |
| `DB_USERNAME_INFORMIX` | Utilisateur Informix | `informix` |
| `DB_PASSWORD_INFORMIX` | Mot de passe Informix | `votre_mot_de_passe` |
| `LDAP_HOST` | Serveur LDAP | `192.168.0.1` |
| `LDAP_PORT` | Port LDAP | `389` |
| `LDAP_DOMAIN` | Domaine LDAP | `@@fraise.hff.mg` |
| `LDAP_DN` | DN de base LDAP | `OU=HFF Users,DC=fraise,DC=hff,DC=mg` |
| `MAILER_HOST` | Serveur SMTP | `smtp.gmail.com` |
| `MAILER_PORT` | Port SMTP | `587` |
| `MAILER_USER` | Utilisateur email | `noreply@hff.mg` |
| `MAILER_PASSWORD` | Mot de passe email | `votre_mot_de_passe` |
| `BASE_PATH_LONG` | Chemin complet du projet | `C:/wamp64/www/Hffintranet` |
| `BASE_PATH_FICHIER` | Chemin des fichiers uploadés | `C:/wamp64/www/Upload/` |
| `BASE_PATH_LOG` | Chemin des logs | `C:/wamp64/www/Hffintranet/var/app_errors.log` |

## Sécurité

### Fichiers à ne jamais commiter

- `.env` - Contient les vraies valeurs sensibles
- `config/env.local.yaml` - Configuration locale spécifique

### Fichiers de référence (peuvent être commités)

- `config/env.example` - Modèle de configuration
- `config/parameters.yaml` - Paramètres généraux

### Bonnes pratiques

1. **Ne jamais** commiter le fichier `.env`
2. **Toujours** utiliser des mots de passe forts
3. **Changer** les mots de passe par défaut
4. **Limiter** l'accès au fichier `.env` (permissions 600)
5. **Sauvegarder** le fichier `.env` de manière sécurisée

## Déploiement

### Environnement de développement

1. Copier `config/env.example` vers `.env`
2. Configurer les valeurs de développement
3. Tester l'application

### Environnement de production

1. Créer un fichier `.env` avec les valeurs de production
2. S'assurer que le fichier n'est pas accessible via le web
3. Configurer les permissions appropriées

## Dépannage

### L'application ne se connecte pas à la base de données

1. Vérifier que le fichier `.env` existe
2. Vérifier les valeurs des variables `DB_*`
3. Tester la connexion manuellement

### Erreur "Environment variable not found"

1. Vérifier que la variable existe dans `.env`
2. Vérifier l'orthographe de la variable
3. Redémarrer le serveur web

## Migration depuis l'ancienne configuration

Si vous migrez depuis une configuration avec des valeurs codées en dur :

1. Sauvegarder l'ancien fichier `services_custom.yaml`
2. Créer le fichier `.env` avec les anciennes valeurs
3. Tester l'application
4. Supprimer l'ancien fichier de sauvegarde

## Support

Pour toute question concernant la configuration sécurisée, contactez l'équipe de développement.
