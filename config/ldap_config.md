# Configuration LDAP - OBLIGATOIRE

## ⚠️ IMPORTANT : LDAP est maintenant obligatoire

L'application nécessite un serveur LDAP fonctionnel. Sans LDAP, l'application ne peut pas fonctionner.

## Variables d'environnement requises

Créez un fichier `.env` à la racine du projet avec les variables suivantes :

### Configuration de test (serveur LDAP public)
```env
# Configuration LDAP pour tests
LDAP_HOST=ldap.forumsys.com
LDAP_PORT=389
LDAP_DOMAIN=
LDAP_DN=dc=example,dc=com
```

### Configuration locale
```env
# Configuration LDAP locale
LDAP_HOST=localhost
LDAP_PORT=389
LDAP_DOMAIN=@domain.local
LDAP_DN=OU=Users,DC=domain,DC=local
```

## Installation d'un serveur LDAP local

### Windows (WAMP)
1. Installer Active Directory Domain Services (AD DS)
2. Configurer un domaine local
3. Créer des utilisateurs de test

### Docker (recommandé pour les tests)
```bash
docker run -p 389:389 --name openldap -d osixia/openldap
```

### Linux
```bash
sudo apt-get install slapd ldap-utils
sudo dpkg-reconfigure slapd
```

## Test de la configuration

Exécutez le script de test :
```bash
php setup_ldap_test.php
```

## Notes importantes

- LDAP est maintenant OBLIGATOIRE pour le fonctionnement de l'application
- Sans LDAP, l'application lèvera une exception
- Utilisez le serveur LDAP public pour les tests rapides
- Configurez un serveur local pour la production
