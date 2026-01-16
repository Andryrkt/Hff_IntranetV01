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

Ref nikitika JS na CSS de ampiakarina ny version ny CSS sy JS
**_Exemple actuel:_**

```html
<link
  href="{{ App.base_path }}/Views/css/new.css?v=2025.09.15.08.00"
  rel="stylesheet"
/>
<script
  src="{{ App.base_path }}/Views/js/scripts.js?v=2025.09.15.08.00"
  type="module"
></script>
```

Mila ovaina daholo ny version.
===> Ctrl + Shift + H (raccourci pour remplacer tout) - mot à chercher = 2025.08.29.16.20 - remplacer par = <YYYY>.<MM>.<dd>.<HH>.<mm>
