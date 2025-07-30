# DOCUMENTATION HFF INTERANET

## configuration du php.ini pour la production

- display_errors = Off
- display_startup_errors = Off
- log_errors = On
- error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT

## configuration du php.ini pour la taille de ficher à uploder

- upload_max_filesize = 5M
- post_max_size =5M

## configuration du php.ini pour la durée de session par defaut

session.gc_maxlifetime = 3600

## à chaque deployement executé ceci

```Bash
vendor/bin/doctrine orm:generate-proxies
```

## ajouter ceci si on vient de le deploier

fichier config.js

```Bash
export const baseUrl = "/Hffintranet";
```
