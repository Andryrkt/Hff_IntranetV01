# Plan : refonte des services `App\Service\da` (services spécifiques + orchestration)

## Contexte

La migration des traits est terminée (`PLAN_MIGRATION_TRAITS_DA_VERS_SERVICES.md`, étape 4 : 4.1 à 4.10 FAIT, `src/Controller/Traits/da` supprimé). Restent :
- des services trop gros : `DaFilterService` (491 lignes), `DaService` (314), `DaTimelineService` (313), `EmailDaService` (294), `DaCreationService` (252) ;
- 14 fichiers qui instancient `EmailDaService` et/ou `FileUploaderForDAService` par `new` (13 contrôleurs DA + `Api/da/ActionSurNonDispoApi`), plus `new ExcelService()` dans `DaValidationService:138` ;
- de la logique métier dans les deux contrôleurs de proposition (~300 lignes chacun) ;
- 18 bugs signalés, jamais corrigés.

Ce plan applique les règles « service / trait / orchestration » aux services DA existants (audit puis refonte).

## Décisions validées (2026-10-09)

- **Périmètre** : uniquement `App\Service\da` et les contrôleurs/API qui les consomment. **Intouchables** : entités, routes, templates Twig. Les services déjà migrés ne sont retouchés que si l'audit le justifie.
- **Contraintes** : PHP 7.4, pas de `readonly` ; injection **par constructeur uniquement** (pas par argument d'action) ; yaml géré par l'utilisateur (Claude liste les entrées à ajouter dans `config/services/services_da.yaml`) ; **aucun commit par Claude** : un message de commit est proposé à chaque étape.
- **Nommage** : services d'orchestration nommés **par cas d'usage**, style `Da<CasUsage>Service` (comme `DaValidationService`) ; la doc de la classe indique « orchestre ».
- **Comportement** : strictement préservé pendant la refonte ; bugs signalés d'abord. La correction des bugs est une **phase dédiée après la refonte** (phase D), un commit par bug.
- **Ordre** : gains rapides d'abord (A1 → A4), puis propositions (B), audit (C), bugs (D), contrôle final (E).
- **Validation** : parcours manuel des pages (pas de tests automatisés pour l'instant) + `php -l` ; `lint:container` n'existe pas dans ce projet. Statut FAIT / À FAIRE par étape.

## Règles

### Service spécifique ou orchestrateur
- **Service spécifique** : porte un seul agrégat ou une seule responsabilité (observations, documents, lignes de proposition…). Dépendances minimales.
- **Service d'orchestration** : enchaîne un cas d'usage qui traverse plusieurs domaines (ex. valider une DA : statut + `DaAfficher` + PDF + mail). Il injecte des services spécifiques et ne fait presque pas de logique propre.
- Pas de relais pur (`return $this->x->y()`) : le contrôleur appelle directement le service cible.
- **Plafond** : environ 5 dépendances par constructeur. Au-delà : découper par sous-domaine d'abord ; créer un orchestrateur seulement si le service enchaîne vraiment des étapes.
- Une dépendance utilisée par une seule méthode va dans un service dédié (principe 1 du plan de migration).
- Fusionner deux services seulement s'ils ont les mêmes dépendances, le même sujet métier et ne sont jamais utilisés séparément.

### Règle de départage (méthode candidate à deux services)
1. Une méthode va dans le service de **l'agrégat qu'elle écrit** : observation → `DaObservationService` ; DAL/DALR → service de lignes ; `DaAfficher` → `DaAfficherService`.
2. Elle écrit dans **plusieurs agrégats** → c'est un orchestrateur.
3. Elle est appelée par **tous** les autres services → service de base (`DaService`).

### Sens des dépendances (ne pas inverser)
Base : `DaService`, `DaObservationService` (futur), `DaDocumentService` (futur), `FileUploaderForDAService`, `EmailDaService` → intermédiaire : `DaAfficherService` (→ `DaService`), `DaSoumissionValidationService` (→ `DaService`) → sommet : `DaValidationService`, `DaAffectationService`, orchestrateur de proposition.

### Dépendances circulaires
Exemple : si `DaService::insertionObservation` devait rafraîchir `DaAfficher`, on injecterait `DaAfficherService` dans `DaService`, alors que `DaAfficherService` dépend déjà de `DaService` → cycle, le conteneur ne se construit pas.

**Décision validée (2026-10-09) : service tiers ou orchestrateur.** `DaObservationService` insère l'observation ; l'orchestrateur du cas d'usage appelle ensuite `DaAfficherService`. Les services de base ne se connaissent pas. Si deux services spécifiques ont besoin l'un de l'autre, on extrait la partie commune dans un 3e service dont dépendent les deux.

Alternatives, **non retenues par défaut** (à ne proposer que si la règle ci-dessus ne s'applique vraiment pas, avec accord de l'utilisateur) :
- passer la donnée en paramètre, quand le service appelé n'a besoin que de valeurs déjà calculées ;
- événement Symfony, seulement si les deux côtés doivent rester découplés (le flux devient invisible) ;
- injection paresseuse / setter : proscrite, elle masque le cycle.

## Cartographie cible (`src/Service/da/`)

| Service | Type | Reprend | Dépendances (indicatif) | Statut |
|---|---|---|---|---|
| `DaObservationService` | spécifique | `DaService::insertionObservation`, `getObservations` | `em`, `FileUploaderForDAService` | À FAIRE (A2) |
| `DaDocumentService` | spécifique | `DaService::getBaIntranetPath`, `getDevisPjPathDaLine`, `getDevisPjPathObservation`, `getOrPath`, `getAllDdpPath` | `em`, `FileCheckerService` | À FAIRE (A3) |
| `DaService` (allégé) | base | `getJoursRestants`, `getLignesRectifiees`, `appliquerChangementStatut`, `appliquerStatutDemandeDevisEnCours`, `normalizeTypographicChars`, `getDemandeAppro` | `em` | À FAIRE (A4) |
| `DaPropositionLigneService` | spécifique | logique DAL/DALR et uploads des 2 contrôleurs de proposition | `em`, `FileUploaderForDAService` | À FAIRE (B) |
| `DaPropositionService` | orchestration | valider/proposer/refuser une proposition : lignes + statut + PDF + mail + `DaAfficher` | `DaPropositionLigneService`, `DaService`, `DaObservationService`, `DaAfficherService`, `DaValidationService`, `EmailDaService` | À FAIRE (B) |
| `EmailDaService`, `FileUploaderForDAService` | existants | injectés par le conteneur au lieu de `new` | voir A1 | À FAIRE (A1) |
| `DaFilterService`, `DaTimelineService`, `EmailDaService`, `DaCreationService` | à auditer | découpe par sous-domaine | — | À FAIRE (C) |

## Étapes

Chaque étape : `php -l` sur les fichiers modifiés, liste des entrées yaml pour l'utilisateur, parcours manuel de la liste de l'étape, message de commit proposé, mise à jour du statut ici.

### A1. Injection des `new` restants — À FAIRE
- **Fichiers** : `DaNewAchatController`, `DaNewAvecDitController`, `DaNewReApproMensuelController`, `DaDetailAvecDitController`, `DaDetailDirectController`, `DaDetailReapproController`, `DaEditAvecDitController`, `DaEditDirectController`, `DaPropositionArticleDirectController`, `DaPropositionRefAvecDitController`, `DaValidationAvecDitController`, `DaValidationDirectController`, `DaValidationReapproMensuelController`, `Api/da/ActionSurNonDispoApi` ; `DaValidationService:138` (`new ExcelService()`).
- **Travail** : `EmailDaService` reçoit `Twig\Environment` et `UrlGeneratorInterface` du conteneur (service à déclarer public) ; `FileUploaderForDAService` est déjà déclaré dans le yaml ; `ExcelService` injecté dans `DaValidationService`. Chaque contrôleur reçoit ce dont il se sert par son constructeur ; les repositories restent tirés de `$em`.
- **yaml** : déclarer `EmailDaService` ; ajouter les arguments `$emailDaService` / `$daFileUploader` des contrôleurs concernés ; `$excelService` de `DaValidationService`.
- **Parcours** : créer une DA et la soumettre (mail création), ajouter une observation (mail observation), valider (mail validation + Excel/PDF), modifier (mail modification), proposition avec fiche technique et devis, action non dispo.
- **Commit proposé** : `refactor: injection de EmailDaService, FileUploaderForDAService et ExcelService`

### A2. `DaObservationService` — À FAIRE
- Déplacer `insertionObservation` et `getObservations` de `DaService` ; `DaService` perd `FileUploaderForDAService`.
- Appelants à basculer (recherche par `grep insertionObservation\|getObservations src`) : contrôleurs de création, détail, édition, validation, proposition ; `DaAffectationService`, `DaValidationService`.
- Garder la signature actuelle (`$username` explicite) ; pas d'appel de `DaAfficherService` depuis ce service (règle des cycles).
- **Parcours** : ajouter une observation avec et sans pièce jointe sur chaque type de DA ; vérifier la liste d'observations au détail.
- **Commit proposé** : `feat: DaObservationService extrait de DaService`

### A3. `DaDocumentService` — À FAIRE
- Déplacer les 5 méthodes de chemins de documents ; `FileCheckerService` y est injecté (il n'est plus dans `DaService`).
- **Adapter `DocRattacheService`** : il appelle ces méthodes **dynamiquement** (`$service->$method(...)`) ; sans adaptation, tout plante sans erreur de syntaxe. Mettre à jour la configuration de mapping pour viser `DaDocumentService`.
- **Parcours** : ouvrir le détail d'une DA ayant des BA, devis PJ, OR, DDP : les documents rattachés doivent s'afficher comme avant.
- **Commit proposé** : `feat: DaDocumentService extrait de DaService`

### A4. Alléger `DaService` — À FAIRE
- Après A2 et A3, `DaService` ne garde que le noyau DA. Décider si `appliquerChangementStatut` et `appliquerStatutDemandeDevisEnCours` forment un `DaStatutService` (à trancher avec l'utilisateur à ce moment).
- **Commit proposé** : `refactor: DaService réduit au noyau DA`

### B. Propositions — À FAIRE
- Extraire la logique métier des deux contrôleurs (`DaPropositionRefAvecDitController`, `DaPropositionArticleDirectController`) : `DaPropositionLigneService` (lignes DAL/DALR, uploads) puis `DaPropositionService` (orchestration). Les contrôleurs ne gardent que `Request`, `Form`, `render`, redirections.
- Prérequis : A1 à A3 faits (sinon les dépendances du nouveau service se multiplient).
- Dédupliquer les méthodes communes des deux contrôleurs (la logique « refs / dalrs / dals trié » existe en double).
- **Parcours** : proposer, choisir, valider des lignes, observation, retour au statut précédent, validation avec DW, sur AvecDit et Direct.
- **Commit proposé** : `feat: DaPropositionService et DaPropositionLigneService`

### C. Audit des constructeurs et des gros services — À FAIRE
- Lister tous les constructeurs de `src/Service/da` et des contrôleurs DA : tout ce qui dépasse 5 dépendances est analysé (découpe ou orchestrateur).
- Noter, sans le faire d'emblée, la découpe de : `DaFilterService` (491), `DaTimelineService` (313), `EmailDaService` (294, un service par type de mail ?), `DaCreationService` (252, `agenceServiceIpsObjet` et initialisations). Chaque découpe devient une sous-étape C1, C2… avec ses fichiers, son yaml et son parcours, ajoutée ici après l'audit.
- **Commit proposé** : `docs: audit des constructeurs DA` (le plan seul).

### D. Correction des bugs — À FAIRE (après A à C)
Un commit par bug : `fix: <bug> (DaXxx)`. Numéros de l'ancien plan (« Bugs évidents »).

| Priorité | Bugs | Nature |
|---|---|---|
| **P1** (erreur fatale / perte de données) | 1 (`DaEdit*` DALR sur DAL supprimée → index indéfini), 2 (export Excel reporting IPS cassé : `getData` renvoie `results`/`totals`, le contrôleur lit `reportingIps`/`qteTotale`/`montantTotal`), 7 (`DaNewAvecDitController` : `$dit` null déréférencé, `getPrixUnitaire(...)[0]` non gardé), 14 (`DaAffectationAchatController` : `find($id)` sans garde) | plantage |
| **P2** (sécurité / robustesse) | 3 (`getUser()` non gardé, `agenceServiceIpsObjet()` renvoie des null), 4 (Ghostscript : chemin Windows en dur, `echo`, pas de `escapeshellarg`, fichier source écrasé), 6 et 18 (`DaAfficherService::ajouterDansTableAffichageParNumDa` : `getDit()` sans garde, `$oldDaAffichers[0]`) | sécurité, null |
| **P3** (cohérence des données) | 5 et 17 (`getLignesRectifiees` : `->first()` peut renvoyer `false`, pas de filtre `deleted`, DALR indexés par numéro de ligne), 8 (numéros de ligne en collision, réappro mensuel), 9 (DALR sans filtre `deleted`), 10 (déjà résolu : cache `oldObservations` supprimé ; reste la clé de préfixe non gardée), 11 (obsolète : `daObservationRepository` du trait supprimé), 12 (N+1 SQL dans `DaListeDitService::ajoutNumSerieNumParc`, conditions testées deux fois, `categorie` traité différemment), 15 (`DaAfficherService` normalise `artDesi` : à vérifier que c'est fait), 16 (`getUser()->getNomUtilisateur()` vs `getUserName()` session) | données |
| **P4** | 13 (commentaires faux « 3 jours » alors que le code ajoute 5) | texte |

Avant de commencer D, relire chaque bug dans le code actuel : certains (10, 11, 15) sont déjà résolus ou obsolètes depuis la migration ; les marquer FAIT/OBSOLÈTE au lieu de les corriger.

### E. Contrôle final — À FAIRE
- `grep -rn "new EmailDaService\|new FileUploaderForDAService\|new ExcelService" src` ne renvoie plus rien (hors services eux-mêmes).
- Aucun constructeur de service ou contrôleur DA au-dessus de ~5 dépendances sans justification écrite ici.
- Parcours complet des pages DA (liste ci-dessous).

## Points de la liste de décisions précédente, classés

| Point | Où dans ce plan |
|---|---|
| Vérification manuelle des pages touchées par 4.8 à 4.10 | transversal : fait avant d'ouvrir A1 (parcours de la liste ci-dessous) |
| Commits 4.8, 4.9, 4.10 non faits | à faire par l'utilisateur avant A1 ; messages proposés : `feat: DaListeDitService remplace DaListeDitTrait`, `feat: ReportingIpsService remplace ReportingIpsTrait`, `feat: suppression de DaTrait, champs explicites dans les contrôleurs DA` |
| Injection des `new` restants | A1 |
| Bugs signalés | D |
| `DaPropositionService`, `DaDocumentService` (hors périmètre de l'ancien plan) | B, A3 |

## Parcours manuel de référence

- **Création** : DA Achat, DA avec DIT (avec/sans observation), DA Reappro mensuel (création et re-création).
- **Édition** : modifier une DA Direct et AvecDit (lignes ajoutées/supprimées/modifiées), observation, nouvelle version `DaAfficher`.
- **Détail** : afficher, ajouter une observation avec/sans PJ, documents rattachés, autoriser l'émetteur.
- **Validation** : valider (statut, Excel/PDF, mail), refuser, observation ; Reappro mensuel : valider/refuser.
- **Proposition** : proposer, choisir, valider des lignes, retour au statut précédent.
- **Affectation** : passer la DA au demandeur avec motif, affecter une DA parent.
- **Liste DIT** : `/demande-appro/list-dit`, recherche, pagination, n° série/parc.
- **Reporting IPS** : `/demande-appro/reporting-ips`, recherche ; export Excel (cassé tant que le bug 2 n'est pas corrigé).
- **Génération PDF** : `GenerationPDFController`, `GenerationPDFReapproController`.
