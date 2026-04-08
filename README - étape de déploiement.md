# étape 1: Requête SQL

- Executer tous les requêtes dans `/sql/profilUser/profilUser.sql`
- Lors de l'execution vérifier un à un que les requêtes sont bien executées.

---

# étape 2: Fichiers de cache

## Vérifier l'existences des fichiers de cache:

Si certains des fichiers cités ci-dessous sont inexistants situés dans le dossier `/var/cache/`, éxecuter la commande suivante:

```Bash
php config/bootstrap_build.php
```

### Container.php

Ce fichier sert de conteneur pour tous l'app.

### url_matcher.php

Ce fichier sert de mappeur pour tous les routes dans les controleurs, surtout utilisée en PROD pour la performance (utilisé aussi en DEV).

### url_generator.php

Ce fichier sert de mappeur pour générer les url, surtout utilisée en PROD pour la performance (utilisé aussi en DEV).

### routes_dev.php

Ce fichier est comme url_matcher.php et url_generator.php à la fois mais qui n'est utilisé qu'en DEV.

### twig/

Ce dossier sert à contenir les fichiers de cache des templates twig utilisés dans tous l'APP.
Au premier démarrage en "PROD", tous les templates twig seront compilés et mis en cache dans ce dossier. (donc normale que ça prends du temps ~3s à 5s)

---

# étape 3: Proxy

Si le dossier `/var/cache/proxies` est vide, exécuter:

```Bash
vendor/bin/doctrine orm:generate-proxies
```

Il va générer les proxy pour chaque entité. Donc en cas de modification d'entités, veuillez éxecuter cette commande.

---

# étape 4: Données de BDD

Préremplir la base de donnée avec les données de profil enregistré, et les pages

## Vérification des données

Il faut comparer les données en PROD avant le déploiement et les fichiers de migration pour s'assurer que tout est à jour.
Fichiers de migration:

- `/config/migration/profils.json`
- `/config/migration/affectations_profils.json`

### profils.json

Ce fichier sert de configuration pour la migration des données. Il contient les profils, les applications, les pages et les agences/services.

Exemple:

```json
[
  {
    "ref_profil": "SUP-ADMIN",
    "designation_profil": "SUPER ADMINISTRATEUR",
    "societe_id": 1,
    "applications": [
      {
        "code_app": "INTRANET",
        "pages": [
          {
            "nom_route": "dashboard_index",
            "peut_voir": true,
            "peut_voir_liste_avec_debiteur": false,
            "peut_multi_succursale": false,
            "peut_supprimer": true,
            "peut_exporter": true
          }
        ],
        "agences_services": [
          { "code_agence": "HFF-TANA", "code_service": "DSI" }
        ]
      }
    ]
  }
]
```

### affectations_profils.json

Ce fichier sert de configuration pour la migration des données. Il contient les affectations des profils aux utilisateurs.

Exemple:

```json
[
  {
    "ref_profil": "HFF_01-ASS_DIT+REP+DDC",
    "societe_id": 1,
    "username": "thierry.aro"
  }
]
```

---

# étape 5: Cache pour profil

### menu

Le dossier `/var/cache/pools/menu` sert pour les caches de menus (principal et admin) attribués aux profils.
Si ce dossier est vide, éxecuter la commande:

```Bash
php bin/console app:cache-warmup-menu
```

### securité

Le dossier `/var/cache/pools/security` sert pour les caches de sécurités (droit ou permission sur des pages, agences service autorisés) attribués aux profils.
Si ce dossier est vide, éxecuter la commande:

```Bash
php bin/console app:cache-warmup-menu
php bin/console app:cache-warmup-ag-serv
```

### Tous

Pour remplir tous les dossiers cités ci-dessus, on peut éxecuter l'unique commande:

```Bash
php bin/console app:cache-warmup-all
```
