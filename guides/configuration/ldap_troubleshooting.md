# Guide de résolution des problèmes LDAP

## Problème : "Impossible de se connecter au serveur LDAP"

### 1. Vérifications de base

#### Extension PHP LDAP
```bash
php -m | grep ldap
```
Doit afficher `ldap`

#### Test de connectivité réseau
```bash
telnet localhost 389
```
ou
```bash
nc -zv localhost 389
```

### 2. Solutions selon l'environnement

#### Environnement de développement local
1. **Installer un serveur LDAP local :**
   - Windows : Active Directory Domain Services (AD DS)
   - Linux : OpenLDAP ou 389 Directory Server
   - Docker : `docker run -p 389:389 osixia/openldap`

2. **Configuration WAMP/XAMPP :**
   - Vérifier que l'extension `php_ldap` est activée dans `php.ini`
   - Redémarrer Apache

#### Environnement de production
1. **Vérifier la configuration réseau :**
   - Firewall
   - Routage réseau
   - DNS

2. **Vérifier les paramètres LDAP :**
   - Host et port corrects
   - DN de base valide
   - Credentials valides

### 3. Configuration recommandée pour le développement

#### Fichier .env
```env
# Configuration LDAP pour développement
LDAP_HOST=localhost
LDAP_PORT=389
LDAP_DOMAIN=@test.local
LDAP_DN=OU=Users,DC=test,DC=local
```

#### Alternative : Mode dégradé
Si LDAP n'est pas disponible, l'application fonctionne en mode dégradé :
- Les erreurs sont loggées mais n'interrompent pas l'application
- Les méthodes retournent des valeurs par défaut
- L'authentification peut être gérée par d'autres moyens

### 4. Test de la configuration

Exécuter le script de test :
```bash
php test_ldap_connection.php
```

### 5. Logs et débogage

Les erreurs LDAP sont loggées dans :
- Fichiers de logs PHP
- Logs Apache/Nginx
- Logs de l'application

### 6. Solutions temporaires

En attendant la résolution du problème LDAP :
1. L'application continue de fonctionner
2. Les fonctionnalités LDAP sont désactivées
3. Utiliser l'authentification locale si disponible
