# Plan : refonte de `App\Model\Model` et des classes de connexion

> **Statut : plan non rédigé.** Ce fichier ne contient que le contexte et le prompt à redonner à Claude pour produire le plan détaillé.

## Origine

Étape C5 de `PLAN_REFONTE_SERVICES_DA.md` : remplacer les `new XxxModel()` des services DA par de l'injection. Bloqué parce que le constructeur de `Model` ouvre les connexions DB immédiatement : injecter `DitModel`, `DemandePaiementModel`, `DossierInterventionAtelierModel` ou `MagasinListeOrLivrerModel` ferait ouvrir ces connexions à chaque requête qui construit le service, même sans usage (et une panne Informix/DW casserait des pages qui n'en ont pas besoin). Seul `DaPrixFournisseurService` a été injecté dans `EmailDaService`.

## Constats (état au 2026-10-13)

`src/Model/Model.php`, constructeur :
- `global $container` pour récupérer le logger ;
- `new Connexion()`, `new DatabaseInformix($logger)` + `$this->connect->connect()` **forcé** (erreur avalée par un `try/catch`), `new ConnexionDote4()`, `new connexionDote4Gcot()` ;
- `dbIrium` / `dbIps` lus dans `$_ENV`.

Utilisation :
- 58 classes `extends Model` ; usages directs des propriétés protégées : `$this->connect` 580 fois, `$this->connexion` 109, `$this->dbIps` 35, `$this->dbIrium` 31, `$this->connexion04` 7, `$this->connexion04Gcot` 7 ;
- tous les `App\Model\*` sont enregistrés comme services (`services_app.yaml`), donc autowirables ; `App\Model\da` et `App\Model\ddp` ont `autowire: true` explicite.

Défauts de `Model` :
- méthodes utilitaires sans rapport avec une base de modèles : `getmailUserConnect`, `getAgence_SageofCours`, `getAgenceServiceIriumofcours`, `getDatesystem`, `has_permission`, `modificationDernierIdApp`, `recuperationDerniereIdApp` (corps incomplet), `retournerResult28`, `retournerResultGcot04`, `retournerResult04` ;
- SQL concaténé (`getmailUserConnect`, `getAgence_SageofCours`, `getAgenceServiceIriumofcours`, `modificationDernierIdApp`, `recuperationDerniereIdApp`) ;
- `echo` des erreurs et `odbc_close` dans `has_permission` et `modificationDernierIdApp` (ferme la connexion partagée) ;
- `odbc_fetch_array(...)['Mail']` non gardé.

Pistes déjà étudiées pour les connexions paresseuses :
- **3a** : `unset()` des 4 propriétés de connexion dans le constructeur + `__get` qui crée l'objet au premier accès. Les 58 enfants ne changent pas. Risques : un enfant dépendant d'une connexion déjà ouverte sans passer par `$this->connect` ; l'erreur de connexion survient au premier accès, plus à l'instanciation ; touche tout le projet.
- **3b** : injecter les modèles et les déclarer `lazy: true` dans le yaml (proxy Symfony). À vérifier : version de Symfony et présence de `symfony/proxy-manager-bridge` (ou lazy ghost natif). `Model` reste inchangé.

## Décisions déjà prises

- **Objectifs** : performance et testabilité.
- **Axes** (tous) : connexions paresseuses ; sortir les méthodes utilitaires de `Model` ; injection de dépendances à la place de `global $container` et des `new Connexion()` ; composition à la place de l'héritage ; sécurité (SQL concaténé, `echo` d'erreurs, `odbc_close` dans les méthodes) ; principes SOLID.
- **Périmètre** : `Model`, `Connexion`, `ConnexionDote4`, `connexionDote4Gcot`, `DatabaseInformix`. Les 58 modèles enfants ne sont migrés que si c'est vraiment nécessaire. Aucun module exclu.
- **Contraintes** : PHP 7.4, pas de `readonly`, injection par constructeur uniquement, yaml géré par l'utilisateur (Claude liste les entrées à ajouter), **aucun commit par Claude** (message de commit proposé à chaque étape), comportement **strictement préservé**.
- **Validation** : `php -l` + parcours manuel des pages concernées, statut FAIT / À FAIRE par étape. Pas de section « critères de réussite ».

## Prompt à redonner à Claude

```text
Lis docs/plan/PLAN_REFONTE_MODEL.md, src/Model/Model.php, Connexion, ConnexionDote4,
connexionDote4Gcot et DatabaseInformix, puis rédige le plan détaillé de la refonte
dans ce même fichier (remplace la ligne « Statut : plan non rédigé »).

Avant d'écrire :
1. Vérifie dans le code : qui appelle chaque méthode utilitaire de Model (grep sur les
   58 enfants et hors Model), quels enfants lisent $this->connect / $this->connexion
   avant tout usage, quels enfants redéfinissent __construct, et quelles autres classes
   instancient Connexion* / DatabaseInformix par `new`.
2. Vérifie la faisabilité de l'option 3b (version de Symfony, proxy-manager-bridge ou
   lazy ghost objects, lazy: true) et dis si elle suffit seule.

Le plan doit contenir :
- l'ordre des étapes, du gain le plus sûr au plus risqué (gains rapides d'abord) ;
- pour chaque étape : fichiers touchés, travail, entrées yaml à ajouter par moi,
  parcours manuel, message de commit proposé, statut ;
- la décision sur la migration des 58 enfants : dis si elle est nécessaire ou non et
  pourquoi, avec un découpage par lots si oui ;
- les écarts de comportement possibles, listés (comportement strictement préservé) ;
- une cartographie cible (nouveaux services/classes, dépendances, plafond ~5 par
  constructeur, sens des dépendances sans cycle) respectant SOLID ;
- la correction des défauts de sécurité dans une phase dédiée après la refonte,
  un commit par défaut.

Contraintes : PHP 7.4, pas de readonly, injection par constructeur uniquement, je gère
le yaml, tu ne commits jamais. Ne modifie aucun code : plan seul. Utilise /clarify
si une décision m'appartient.
```
