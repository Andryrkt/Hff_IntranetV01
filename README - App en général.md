# DOCUMENTATION HFF INTRANET

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

fichier config.js à crée dans Views > js > utils > config.js

```Bash
export const baseUrl = "/Hffintranet";
```

## Déploiement

Branche ts maints andalovana aloha: "dev", "pre_prod"

Ny version an'ny CSS sy JS dia calculée automatiquement (`App.browserHash`, dans `AppExtension::getGlobals()`), tsy mila ovaina intsony manuellement isaky ny fichier na deploiement.
**_Exemple actuel:_**

```html
<link
  href="{{ App.base_path }}/Views/css/new.css?v={{ App.browserHash }}"
  rel="stylesheet"
/>
<script
  src="{{ App.base_path }}/Views/js/scripts.js?v={{ App.browserHash }}"
  type="module"
></script>
```

`App.browserHash` dia `hash('crc32b', date('Y-m-d'))` : mitovy mandritra ny andro iray, ary miova ho azy isaky ny andro vaovao (invalide ny cache navigateur ho an'ny CSS/JS rehetra).

.......
