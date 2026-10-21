# Plan : refonte des services `App\Service\da` (services spécifiques + orchestration)

## Contexte

La migration des traits est terminée (`PLAN_MIGRATION_TRAITS_DA_VERS_SERVICES.md`, étape 4 : 4.1 à 4.10 FAIT, `src/Controller/Traits/da` supprimé). Restent :
- des services trop gros : `DaFilterService` (491 lignes), `EmailDaService` (323), `DaService` (314), `DaTimelineService` (313), `DaCreationService` (252) ;
- 15 fichiers qui instancient `EmailDaService` et/ou `FileUploaderForDAService` par `new` (13 contrôleurs DA + `Api/da/ActionSurNonDispoApi` + `Service/da/CdeFrn/FacBl/TraitementSoumissionfacBlService:298`), plus `new ExcelService()` dans `DaValidationService:138` ;
- de la logique métier dans les deux contrôleurs de proposition (~300 lignes chacun) ;
- 18 bugs signalés, jamais corrigés.

Ce plan applique les règles « service / trait / orchestration » aux services DA existants (audit puis refonte).

## État au 2026-10-12

Depuis la rédaction du plan (2026-10-09), les commits ont porté sur des fonctionnalités et corrections hors refonte (export Excel liste DA, livraison partielle + notification APPRO, indicateurs pièces jointes/observations, récupération DIT, UI). **Aucune étape A1 à E n'est commencée**, tous les statuts restent À FAIRE. Effets de bord à prendre en compte :
- `EmailDaService` a gagné `notifierApproLivraisonPartielle` (appelé par `TraitementSoumissionfacBlService`, qui fait `new EmailDaService($twig, $urlGenerator)` en ligne 298 et reçoit `$twig`/`$urlGenerator` en arguments de méthode depuis `DaSoumissionFacBlController`) : à traiter en A1.
- `ActionSurNonDispoApi`, `DaDetailAvecDitController`, `DaSoumissionFacBlController`, `DaSoumissionCalculService`, `DaIconService` ont été modifiés : relire leur état avant de les toucher.
- Le bug 2 (export Excel) : le commit `815e731b0` a corrigé l'export de la **liste des DA** (`ListeDa/ExportExcelController`) ; l'export **reporting IPS** reste à vérifier avant D.
- Les numéros de ligne cités ci-dessous sont ceux du 2026-10-09 : les retrouver par `grep` avant de modifier.

## Décisions validées (2026-10-09)

- **Périmètre** : uniquement `App\Service\da` et les contrôleurs/API qui les consomment. **Intouchables** : entités, routes, templates Twig. Les services déjà migrés ne sont retouchés que si l'audit le justifie.
- **Contraintes** : PHP 7.4, pas de `readonly` ; injection **par constructeur uniquement** (pas par argument d'action) ; yaml géré par l'utilisateur (Claude liste les entrées à ajouter dans `config/services/services_da.yaml`) ; **aucun commit par Claude** : un message de commit est proposé à chaque étape.
- **Nommage** : services d'orchestration nommés **par cas d'usage**, style `Da<CasUsage>Service` (comme `DaValidationService`) ; la doc de la classe indique « orchestre ».
- **Comportement** : strictement préservé pendant la refonte ; bugs signalés d'abord. La correction des bugs est une **phase dédiée après la refonte** (phase E), un commit par bug.
- **Ordre** : gains rapides d'abord (A1 → A4), puis propositions (B), audit (C), utilisateur et repositories (D), bugs (E), contrôle final (F).
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
| `DaObservationService` | spécifique | `DaService::insertionObservation`, `getObservations` | `em`, `FileUploaderForDAService` | FAIT (A2) |
| `DaDocumentService` | spécifique | `DaService::getBaIntranetPath`, `getDevisPjPathDaLine`, `getDevisPjPathObservation`, `getOrPath`, `getAllDdpPath` | `em`, `FileCheckerService` | FAIT (A3) |
| `DaService` (allégé) | base | `getJoursRestants`, `getLignesRectifiees`, `appliquerChangementStatut`, `appliquerStatutDemandeDevisEnCours`, `normalizeTypographicChars`, `getDemandeAppro` | `em` | FAIT (A4) |
| `DaPropositionLigneService` | spécifique | logique DAL/DALR et uploads des 2 contrôleurs de proposition | `em`, `FileUploaderForDAService` | FAIT (B) |
| `DaPropositionService` | orchestration | valider/proposer/refuser une proposition : lignes + statut + PDF + mail + `DaAfficher` | `DaPropositionLigneService`, `DaService`, `DaObservationService`, `DaAfficherService`, `DaValidationService`, `EmailDaService` | FAIT (B) |
| `EmailDaService`, `FileUploaderForDAService` | existants | injectés par le conteneur au lieu de `new` | voir A1 | FAIT (A1) |
| `DaFilterService`, `DaTimelineService`, `EmailDaService`, `DaCreationService` | à auditer | découpe par sous-domaine | — | Audit FAIT (C), C1 à C3 FAIT, C4 hors périmètre, C5 partiel |

## Étapes

Chaque étape : `php -l` sur les fichiers modifiés, liste des entrées yaml pour l'utilisateur, parcours manuel de la liste de l'étape, message de commit proposé, mise à jour du statut ici.

### A1. Injection des `new` restants — FAIT (2026-10-12, yaml à ajouter par l'utilisateur)
- **Fichiers** : `DaNewAchatController`, `DaNewAvecDitController`, `DaNewReApproMensuelController`, `DaDetailAvecDitController`, `DaDetailDirectController`, `DaDetailReapproController`, `DaEditAvecDitController`, `DaEditDirectController`, `DaPropositionArticleDirectController`, `DaPropositionRefAvecDitController`, `DaValidationAvecDitController`, `DaValidationDirectController`, `DaValidationReapproMensuelController`, `Api/da/ActionSurNonDispoApi` ; `TraitementSoumissionfacBlService:298` (`new EmailDaService`, `$twig`/`$urlGenerator` passés en argument depuis `DaSoumissionFacBlController` : injecter `EmailDaService` dans le constructeur du service et retirer ces arguments) ; `DaValidationService:138` (`new ExcelService()`).
- **Travail** : `EmailDaService` reçoit `Twig\Environment` et `UrlGeneratorInterface` du conteneur (service à déclarer public) ; `FileUploaderForDAService` est déjà déclaré dans le yaml ; `ExcelService` injecté dans `DaValidationService`. Chaque contrôleur reçoit ce dont il se sert par son constructeur ; les repositories restent tirés de `$em`.
- **yaml** : déclarer `EmailDaService` ; ajouter les arguments `$emailDaService` / `$daFileUploader` des contrôleurs concernés ; `$excelService` de `DaValidationService`.
- **Parcours** : soumettre un FacBl en livraison partielle (mail APPRO), créer une DA et la soumettre (mail création), ajouter une observation (mail observation), valider (mail validation + Excel/PDF), modifier (mail modification), proposition avec fiche technique et devis, action non dispo.
- **Commit proposé** : `refactor: injection de EmailDaService, FileUploaderForDAService et ExcelService`

### A2. `DaObservationService` — FAIT (2026-10-12)
- Déplacer `insertionObservation` et `getObservations` de `DaService` ; `DaService` perd `FileUploaderForDAService`.
- Appelants à basculer (recherche par `grep insertionObservation\|getObservations src`) : contrôleurs de création, détail, édition, validation, proposition ; `DaAffectationService`, `DaValidationService`.
- Fait : 15 appelants basculés ; `DaService` n'est plus injecté dans les 9 qui ne s'en servaient que pour les observations (GenerationPDFReappro, DaAffectationAchat, DaEditAvecDit/Direct, DaPropositionRefAvecDit/ArticleDirect, DaValidationReapproMensuel, DaAffectationService, DaSoumissionValidationService). `DaService` garde `daObservationRepository` jusqu'à A3 (`getDevisPjPathObservation`).
- Garder la signature actuelle (`$username` explicite) ; pas d'appel de `DaAfficherService` depuis ce service (règle des cycles).
- **Parcours** : ajouter une observation avec et sans pièce jointe sur chaque type de DA ; vérifier la liste d'observations au détail.
- **Commit proposé** : `feat: DaObservationService extrait de DaService`

### A3. `DaDocumentService` — FAIT (2026-10-12)
- Déplacer les 5 méthodes de chemins de documents ; `FileCheckerService` y est injecté (il n'est plus dans `DaService`).
- **Adapter `DocRattacheService`** : il appelle ces méthodes **dynamiquement** (`$service->$method(...)`) ; sans adaptation, tout plante sans erreur de syntaxe. Mettre à jour la configuration de mapping pour viser `DaDocumentService`.
- Fait : seul `DocRattacheService` appelait ces 5 méthodes ; son champ `daService` devient `daDocumentService` (les 5 clés `service` de la config passent à `daDocumentService`). `DaService` perd `FileCheckerService`, `daObservationRepository` et 2 modèles ; il ne garde que le noyau (statuts, lignes rectifiées, jours restants, normalisation, `getDemandeAppro`).
- **Parcours** : ouvrir le détail d'une DA ayant des BA, devis PJ, OR, DDP : les documents rattachés doivent s'afficher comme avant.
- **Commit proposé** : `feat: DaDocumentService extrait de DaService`

### A4. Alléger `DaService` — FAIT (2026-10-12)
- Après A2 et A3, `DaService` ne garde que le noyau DA. Décider si `appliquerChangementStatut` et `appliquerStatutDemandeDevisEnCours` forment un `DaStatutService` (à trancher avec l'utilisateur à ce moment).
- **Décision (2026-10-12)** : pas de `DaStatutService`. `appliquerChangementStatut` n'a que 3 appelants (`DemandeDevisApi`, `DaDetailAvecDitController`, `DaDetailDirectController`) et `appliquerStatutDemandeDevisEnCours` 1 ; un service de 2 méthodes ajouterait un fichier et 3 injections pour peu de gain. À reconsidérer si une 3e méthode de statut apparaît. `DaService` (156 lignes) garde : `getJoursRestants`, `getLignesRectifiees`, `appliquerChangementStatut`, `appliquerStatutDemandeDevisEnCours`, `normalizeTypographicChars`, `getDemandeAppro`. Aucun changement de code.
- **Commit proposé** : `plan: A2 à A4 FAIT, DaService réduit au noyau DA`

### B. Propositions — FAIT (2026-10-12)
- Extraire la logique métier des deux contrôleurs (`DaPropositionRefAvecDitController`, `DaPropositionArticleDirectController`) : `DaPropositionLigneService` (lignes DAL/DALR, uploads) puis `DaPropositionService` (orchestration). Les contrôleurs ne gardent que `Request`, `Form`, `render`, redirections.
- Prérequis : A1 à A3 faits (sinon les dépendances du nouveau service se multiplient).
- Dédupliquer les méthodes communes des deux contrôleurs (la logique « refs / dalrs / dals trié » existe en double).
- **Fait** : `DaPropositionLigneService` (4 dépendances : `em`, `FileUploaderForDAService`, `DaModel`, `DaFournisseurService`) et `DaPropositionService` (6 : `em`, `DaPropositionLigneService`, `DaObservationService`, `DaAfficherService`, `DaValidationService`, `EmailDaService`). Un seul orchestrateur pour les deux types : les écarts AvecDit / Direct se décident sur `$da->getDaTypeId() === TYPE_DA_DIRECT` (export Excel, étapes DocuWare, message, libellés famille et n° fournisseur). Les contrôleurs passent de ~670 à 156 lignes et ne gardent que formulaires, rendu, session et redirection (le `exit` de `redirectToRoute` impose de laisser la redirection au contrôleur ; les méthodes du service renvoient la notification). `DaValidationService::soumettreDaDirecteADocuware` ajouté pour que l'orchestrateur reste à 6 dépendances (`DaValidationDirectController` refait les mêmes 2 appels : à unifier plus tard).
- **Écarts assumés** : `modificationIdDALDansDALR` supprimée (public, aucun appelant) ; `recupEntitePageCourante` ne plante plus si aucune DALR trouvée (variable non initialisée) ; une DA d'un autre type ouverte sur l'URL « direct » serait désormais traitée selon son propre type.
- **Parcours** : proposer, choisir, valider des lignes, observation, retour au statut précédent, validation avec DW, sur AvecDit et Direct.
- **Commit proposé** : `feat: DaPropositionService et DaPropositionLigneService`

### C. Audit des constructeurs et des gros services — FAIT (2026-10-12, audit seul ; découpes C1 à C5 à décider)

Audit fait sur `Service/da`, `Controller/da`, `Api/da`, `Factory/da` après A1 à B. Aucun code modifié.

**Constructeurs au-dessus de 5 dépendances**

| Classe | Deps | Lignes | Constat | Décision |
|---|---|---|---|---|
| `TraitementSoumissionBAPService` | 11 | 373 | mélange PDF (4 générateurs/modèles), fichiers, persistance | C4 |
| `TraitementSoumissionDDPLService` | 10 | 328 | idem | C4 |
| `TraitementSoumissionfacBlService` | 8 | 303 | idem + mail livraison partielle | C4 |
| `DaNewAvecDitController` | 9 | 203 | flux création + observation + upload + mail dans le contrôleur | C2 |
| `DaDetailAvecDitController`, `DaDetailDirectController` | 8 | 150 / 141 | flux « envoyer une observation » dupliqué dans les 3 contrôleurs de détail | C1 |
| `DaSoumissionFacBlFactory` | 7 | 286 | factory, 2 `new` de modèles | laissé (une factory assemble par nature) |
| `DaNewAchatController`, `DaNewReApproMensuelController` | 6 | 197 / 153 | même flux de création | C2 |
| `DaPropositionService`, `DaValidationService`, `DaSoumissionValidationService`, `DaValidationReapproMensuelController` | 6 | 264 / 209 / 170 / 155 | orchestrateurs, chaque dépendance sert un maillon du flux | **justifié**, on ne découpe pas |

**Gros services**

| Service | Constat | Décision |
|---|---|---|
| `DaFilterService` (491) | 1 seule méthode de 209 lignes (`applyStatutsFilters`) ; agence/service (~95 lignes, `conditionAgenceService`, `supprimerQuatriemeLettrePD3`) indépendant du reste ; appelé uniquement par `DaAfficherRepository` via le conteneur global | C3 |
| `EmailDaService` (323) | 9 méthodes de mail + 5 helpers, 2 dépendances : cohérent, une seule responsabilité (les mails DA). `new DaPrixFournisseurService()` utilisé par 3 méthodes. Un service par type de mail ferait 9 classes de 30 lignes | **pas de découpe** ; C5 (petit) : injecter `DaPrixFournisseurService` |
| `DaTimelineService` (313) | 1 dépendance, 1 entrée publique, builders DA / OR / BC privés : cohérent | **pas de découpe** |
| `DaCreationService` (252) | 2 dépendances ; `agenceServiceIpsObjet` et `handleAgenceEtServiceDebiteur` sont de l'initialisation propre à la création ; 1 `new MagasinListeOrLivrerModel()` | **pas de découpe** ; C5 : injecter le modèle |

**`new` restants hors A1** (`new XxxModel()` / `new ApplicationService` / `new DitModel` / `new HistoriqueOperationDaBcService` dans contrôleurs et services) : listés pour C5, faible priorité, aucun n'empêche la refonte.

#### Sous-étapes proposées (à valider une par une)

- **C1. Flux d'observation des 3 contrôleurs de détail — FAIT (2026-10-12, aucun yaml).** Déplacer dans `DaDetailService` (aujourd'hui 1 dépendance, le routeur) une méthode `envoyerObservation(DemandeAppro, DaObservation, username, User, estAppro): array` (insertion + statut « autoriser émetteur » + `DaAfficher` + mail). Les contrôleurs `DaDetailAvecDit`, `DaDetailDirect`, `DaDetailReappro` retombent de 8 à ~5 dépendances. `DaDetailService` monte à 5. Même schéma que l'étape B (le service renvoie la notification, le contrôleur redirige). yaml : aucun (autowire). Parcours : observation avec/sans PJ, avec/sans « autoriser l'émetteur », sur les 3 types de détail.
  - **Fait** : `DaDetailService::envoyerObservation` (5 dépendances : routeur, `DaObservationService`, `DaService`, `DaAfficherService`, `EmailDaService`). AvecDit/Direct passent à 6 dépendances (`DaService`, `DaAfficherService`, `EmailDaService` remplacés par `DaDetailService`), Reappro reste à 5. Reappro passe par la même méthode : son formulaire n'a pas la case `statutChange` (toujours false), donc aucun changement de statut, comportement inchangé.
  - **Commit proposé** : `refactor: DaDetailService::envoyerObservation remplace le flux dupliqué des 3 détails`
- **C2. Flux de création — FAIT (2026-10-12, aucun yaml).** Mêmes étapes (persist DA + lignes, uploads, observation, `DaAfficher`, mail) dans `DaNewAchat`, `DaNewAvecDit`, `DaNewReApproMensuel`. Cible probable : un orchestrateur par cas d'usage, nom à trancher avec l'utilisateur (règle `Da<CasUsage>Service`). À ne lancer qu'avec l'inventaire des écarts entre les 3, comme pour B.
  - **Fait** : `DaEnregistrementService` (orchestrateur, 5 dépendances : `em`, `DaCreationLigneService`, `DaObservationService`, `DaAfficherService`, `EmailDaService`) avec 3 méthodes, une par flux (`enregistrerDaAvecDit`, `enregistrerDaReapproMensuel`, `enregistrerDaParent`) car les écarts sont réels : entité parent vs DA, boucle de lignes, `DaAfficher`, mail. `DaCreationLigneService` (5 : `em`, `FileUploaderForDAService`, `DaService`, `DaModel`, `DaFournisseurService`) porte les 3 traitements de lignes et la gestion des fichiers. Restent dans les contrôleurs : formulaire, contrôle de date (3 règles différentes), n° de DA (`autoDecrement` protégé) et redirection. Contrôleurs : 9/6/6 → 3/3/3 dépendances. Comportement inchangé.
  - **Parcours** : créer et re-créer (brouillon puis soumission) une DA Achat avec PJ, AvecDit avec PJ et observation, Reappro mensuel ; date passée refusée ; mail à la soumission.
  - **Commit proposé** : `feat: DaEnregistrementService et DaCreationLigneService pour la création des DA`
- **C3. `DaFilterService` — FAIT (2026-10-12, aucun yaml).** Extraire `applyAgencyServiceFilters` + helpers dans un `DaAgenceServiceFilterService`, et découper `applyStatutsFilters` (209 lignes) en méthodes privées par critère (statut DA, OR, BC, clôturées). Le dépôt récupère le service via le conteneur global : un 2e service impose une 2e ligne `getFilterService()`-like dans `DaAfficherRepository`. Parcours : liste DA et liste cde fournisseurs avec chaque filtre (statuts, dates, agence/service, tri), plus la pagination.
  - **Fait** : `DaAgenceServiceFilterService` (agence/service + restriction selon les droits, 1 dépendance `SecurityService`) extrait ; `DaAfficherRepository` l'appelle via `getAgenceServiceFilterService()` (3 sites). `supprimerQuatriemeLettrePD3` n'est pas lié à l'agence (filtre `numDa`) : il reste dans `DaFilterService`. `applyStatutsFilters` (209 lignes, blocs DA et BC dupliqués) passe à ~45 lignes + `exclureDaFermees`, `filtrerStatutDa`, `filtrerStatutBc`, `filtrerStatutOr` et `andWhereOuCloturee` (condition étendue aux DA clôturées). `DaFilterService` : 491 → ~320 lignes. Comportement inchangé (mêmes conditions, mêmes paramètres).
  - **Commit proposé** : `refactor: DaAgenceServiceFilterService extrait, applyStatutsFilters découpé`
- **C4. Les 3 `TraitementSoumission*` — HORS PÉRIMÈTRE de ce plan**, à traiter dans un plan dédié (module cde fournisseurs / FacBl, pas DA propositions). Constat noté ci-dessus.
- **C5. `new` de modèles et services — PARTIELLEMENT FAIT (2026-10-13).** Fait : `DaPrixFournisseurService` injecté dans `EmailDaService` (aucun yaml ; commit proposé `refactor: DaPrixFournisseurService injecté dans EmailDaService`). **En attente** : les 4 `new` de modèles, car `Model::__construct` ouvre les connexions DB immédiatement : injecter un modèle les ouvrirait à chaque requête construisant le service. À reprendre après `PLAN_REFONTE_MODEL.md` (connexions paresseuses). Cible initiale : `DaPrixFournisseurService` dans `EmailDaService`, `MagasinListeOrLivrerModel` dans `DaCreationService`, `DemandePaiementModel` et `DossierInterventionAtelierModel` dans `DaDocumentService`, `DitModel` dans `DaListeDitService`. Un commit par service.

- **Commit proposé** : `plan: audit des constructeurs DA (étape C)` (le plan seul).

### D. Utilisateur et repositories — À FAIRE (après A à C)
Objectif : performance et cohérence. Périmètre : `Controller/da`, `Api/da`, `Service/da`, `Factory/da`. `Controller` et `UserDataService` ne sont pas modifiés. Le plafond de ~5 dépendances est **ignoré pour cette étape** (à revoir en F). Un commit par sous-étape.

- **D1. `UserDataService` à la place de `getUser()` / `getUserName()` / `getUserMail()` hérités — FAIT (2026-10-15, aucun yaml).**
  - Les contrôleurs DA injectent `UserDataService` (une seule instance partagée par requête ; son constructeur ne fait aucune requête) et n'appellent plus les méthodes héritées de `Controller`. `getUser()->getNomUtilisateur()` devient `getUserName()` (même valeur, aucun `find()`). `getUser()` seulement quand l'entité est nécessaire.
  - Les services DA qui reçoivent `$user` / `$username` en paramètre injectent `UserDataService` ; on retire ces paramètres des appelants (signature d'`insertionObservation` comprise).
  - Écarts assumés : `getUser()` renvoie la même instance pendant la requête (un seul `find()` au lieu d'un par appel) ; le cas `null` (session expirée) reste inchangé, le bug 3 reste en E. Absorbe le bug 16.
  - Repérage : `grep -rn "getUser()\|getUserName()\|getUserMail()\|getNomUtilisateur()" src/Controller/da src/Api/da src/Service/da src/Factory/da` (état au 2026-10-13 : 39 / 19 / 5 occurrences ; 31 lignes référencent déjà `userDataService`).
  - **Commit proposé** : `refactor: UserDataService injecté à la place de getUser()/getUserName() dans les DA`
- **D2. Repositories dans le constructeur, selon la situation. — FAIT (2026-10-15, aucun yaml).**
  - Service : un repository utilisé par **2 méthodes ou plus** est initialisé dans le constructeur par `$this->em->getRepository(X::class)` et la propriété est **typée** (`DaObservationRepository`, etc.). Un repository utilisé par une seule méthode reste dans la méthode. Pas d'injection directe du repository (pas de yaml, pas de dépendance en plus).
  - Contrôleur : seulement si plusieurs actions s'en servent.
  - Repérage : `grep -rn "getRepository(" src/Controller/da src/Api/da src/Service/da` (115 occurrences au 2026-10-13).
  - **Fait** : 3 services — `DaEditionService` (`DemandeApproLRRepository`), `DaSoumissionDataService` (`DaAfficherRepository`, `DaSoumissionFacBlRepository`), `DaSoumissionValidationService` (`DemandeApproRepository`) ; `TraitementSoumissionDDPLService` réutilise son `daAfficherRepository` déjà initialisé. Les autres `getRepository(` inline sont à usage unique par méthode (règle : on les laisse) ou déjà dans le constructeur ; aucun contrôleur n'a un repository partagé par plusieurs actions.
  - **Commit proposé** : `refactor: repositories initialisés dans le constructeur des services DA`
- **Validation (D1 et D2)** : `php -l`, liste des entrées yaml pour l'utilisateur, parcours manuel complet (liste ci-dessous), mise à jour du statut ici.

### E. Correction des bugs — À FAIRE (après A à D)
Un commit par bug : `fix: <bug> (DaXxx)`. Numéros de l'ancien plan (« Bugs évidents »).

| Priorité | Bugs | Nature |
|---|---|---|
| **P1** (erreur fatale / perte de données) | 1 (`DaEdit*` DALR sur DAL supprimée → index indéfini), 2 (export Excel reporting IPS cassé, à revérifier : la liste DA est corrigée depuis `815e731b0` : `getData` renvoie `results`/`totals`, le contrôleur lit `reportingIps`/`qteTotale`/`montantTotal`), 7 (`DaNewAvecDitController` : `$dit` null déréférencé, `getPrixUnitaire(...)[0]` non gardé), 14 (`DaAffectationAchatController` : `find($id)` sans garde) | plantage |
| **P2** (sécurité / robustesse) | 3 (`getUser()` non gardé, `agenceServiceIpsObjet()` renvoie des null), 4 (Ghostscript : chemin Windows en dur, `echo`, pas de `escapeshellarg`, fichier source écrasé), 6 et 18 (`DaAfficherService::ajouterDansTableAffichageParNumDa` : `getDit()` sans garde, `$oldDaAffichers[0]`) | sécurité, null |
| **P3** (cohérence des données) | 5 et 17 (`getLignesRectifiees` : `->first()` peut renvoyer `false`, pas de filtre `deleted`, DALR indexés par numéro de ligne), 8 (numéros de ligne en collision, réappro mensuel), 9 (DALR sans filtre `deleted`), 10 (déjà résolu : cache `oldObservations` supprimé ; reste la clé de préfixe non gardée), 11 (obsolète : `daObservationRepository` du trait supprimé), 12 (N+1 SQL dans `DaListeDitService::ajoutNumSerieNumParc`, conditions testées deux fois, `categorie` traité différemment), 15 (`DaAfficherService` normalise `artDesi` : à vérifier que c'est fait) | données |
| **P4** | 13 (commentaires faux « 3 jours » alors que le code ajoute 5) | texte |

Le bug 16 (`getUser()->getNomUtilisateur()` vs `getUserName()` session, même valeur) est absorbé par l'étape D. Avant de commencer E, relire chaque bug dans le code actuel : certains (10, 11, 15) sont déjà résolus ou obsolètes depuis la migration ; les marquer FAIT/OBSOLÈTE au lieu de les corriger.

### F. Contrôle final — À FAIRE
- `grep -rn "new EmailDaService\|new FileUploaderForDAService\|new ExcelService" src` ne renvoie plus rien (hors services eux-mêmes).
- Aucun constructeur de service ou contrôleur DA au-dessus de ~5 dépendances sans justification écrite ici.
- Parcours complet des pages DA (liste ci-dessous).

## Points de la liste de décisions précédente, classés

| Point | Où dans ce plan |
|---|---|
| Vérification manuelle des pages touchées par 4.8 à 4.10 | transversal : fait avant d'ouvrir A1 (parcours de la liste ci-dessous) |
| Commits 4.8, 4.9, 4.10 non faits | à faire par l'utilisateur avant A1 ; messages proposés : `feat: DaListeDitService remplace DaListeDitTrait`, `feat: ReportingIpsService remplace ReportingIpsTrait`, `feat: suppression de DaTrait, champs explicites dans les contrôleurs DA` |
| Injection des `new` restants | A1 |
| Bugs signalés | E |
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
