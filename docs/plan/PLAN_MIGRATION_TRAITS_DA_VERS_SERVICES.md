# Plan : remplacer les traits `Controller/Traits/da` par des services `App\Service\da`

## Contexte

27 traits (`src/Controller/Traits/da/**`) sont utilisés par ~20 contrôleurs et 6 classes hors contrôleurs. Ils portent de l'état (repositories initialisés par des `initXxxTrait()` appelés dans chaque constructeur), dupliquent beaucoup de code et dépendent de `App\Controller\Controller` (`getEntityManager`, `getUser`, `getSecurityService`, `agenceServiceIpsObjet`, `getTwig`, `getUrlGenerator`).
`DaService` existe déjà (`em` + `FileUploaderForDAService`) mais est une migration partielle : seul `DaDetailReapproController` l'utilise. `DaAfficherService` (`em`) existe aussi, **mais aucun contrôleur ni trait ne l'utilise encore** : il est écrit, pas branché.

**Mise à jour (état du code au 2026-10-07, vs plan initial `9309cbf64`)** : aucun trait n'a été migré ni supprimé (27 traits toujours présents, mêmes consommateurs). Seuls changements : `DaAffectationAchatController` utilise maintenant aussi `DaNewAchatTrait` (nouveau bouton « passer la DA au demandeur » : `traitementTransmissionDA` appelle `insertionObservation` et `ajouterDaDansTableAffichageParent`) et définit sa propre `getButtonName` ; `ActionSurNonDispoController` a été déplacé vers `src/Api/da/ActionSurNonDispoApi.php` (utilise `EmailDaService`, pas de trait) ; `DaAfficherMapper` et `listeDaController` ont reçu des modifications d'affichage sans lien avec les traits. Statuts ci-dessous : **FAIT** / **PARTIEL** / **À FAIRE**.

**Décisions validées** : analyse seule (aucune modification de fichier) ; remplacement complet des traits ; PHP 7.4, **pas de `readonly`** ; services indépendants de `AbstractController`, dépendances injectées par constructeur (autowiring, yaml géré par l'utilisateur) ; plusieurs petits services par domaine, **composition et non héritage** ; le contrôleur lit `Request`/`Form` et passe des valeurs simples au service ; appels `$this->service->méthode()` ; user/session via `App\Service\UserData\UserDataService` ; comportement strictement préservé, bugs seulement signalés ; code mort signalé d'abord.

## Principes de conception

1. Un constructeur ne contient que ce que **toutes** les méthodes du service utilisent. Une dépendance utilisée par une seule méthode devient un service dédié ou est injectée dans le service qui en a vraiment besoin.
2. Les repositories se tirent de `$em` dans le constructeur seulement s'ils servent à la majorité des méthodes ; sinon appel ponctuel `$this->em->getRepository()`.
3. Pas d'`init...()`, pas de `new` en dur dans le constructeur (EmailDaService, FileUploader, ExcelService, DaModel, ...), pas de requête SQL dans un constructeur (voir `setAllFournisseurs` : charge aujourd'hui les fournisseurs à chaque instanciation de contrôleur → getter paresseux).
4. Aucune dépendance à `Request`, `FormInterface`, `render`, `redirectToRoute`, `addFlash`.

## Cartographie traits → services (`src/Service/da/`)

| Service cible | Reprend | Dépendances constructeur (indicatif) |
|---|---|---|
| `DaService` (existant, allégé) — **PARTIEL** : `getJoursRestants`, `insertionObservation` (avec `$username`), `getLignesRectifiees`, `appliquerChangementStatut` y sont déjà ; reste `normalizeTypographicChars` et `appliquerStatutDemandeDevisEnCours`, et aucun contrôleur ne l'appelle encore (sauf `DaDetailReapproController`) | `DaTrait` : `insertionObservation`, `getJoursRestants`, `getLignesRectifieesDA`, `appliquerChangementStatut`, `normalizeTypographicChars` ; `DaDemandeDevisTrait::appliquerStatutDemandeDevisEnCours` | `em`, `FileUploaderForDAService` |
| `DaAfficherService` — **PARTIEL** (service écrit mais jamais utilisé) : contient déjà `getDeletedLineNumbers` (copie de `DaTrait`), `generateDaAfficherOnCreationDa` (= `DaNewTrait::ajouterDaDansTableAffichage`) et `generateDaAfficherOnCreationDaParent` (= `DaNewAchatTrait::ajouterDaDansTableAffichageParent`) ; **reste** `DaAfficherTrait::ajouterDansTableAffichageParNumDa`, et la dépendance `UserDataService` (remplace `getUserName`) ; puis brancher les contrôleurs. Appelant ajouté : `DaAffectationAchatController::traitementTransmissionDA` | `DaAfficherTrait::ajouterDansTableAffichageParNumDa`, `DaNewTrait::ajouterDaDansTableAffichage`, `DaNewAchatTrait::ajouterDaDansTableAffichageParent` | `em`, `UserDataService` (remplace `getUserName`) |
| `creation/DaCreationService` — **À FAIRE** | `initialisationDemandeAppro{Achat,AvecDit,ReapproMensuel}`, `generateDemandApproLinesFromReappros` (1 seule copie), `handleAgenceEtServiceDebiteur`, `dateLivraisonPrevueDA`, `getDatePlannigOr` | `em`, `UserDataService` (user, agence/service IPS, code société), calcul jours ouvrables (voir risque 3) |
| `DaFournisseurService` — **À FAIRE** | `setAllFournisseurs` (doublon `DaNewAvecDitTrait` / `DaPropositionAvecDitTrait`), `DaModel` | `DaModel` ou `em`, `UserDataService` ; chargement paresseux |
| `modification/DaEditionService` — **À FAIRE** | `DaEditTrait` (`getAncienDAL`, `deleteDALR`, `peutModifier`) + `modificationDa`/`modificationDAL` (1 copie au lieu de 2, reçoit des valeurs, pas un `FormInterface`) | `em`, `DaService`, `FileUploaderForDAService` |
| `detail/DaDetailService` — **À FAIRE** | `prepareDataForDisplayDetail` (AvecDit/Direct fusionnés, route de suppression en paramètre) | `UrlGeneratorInterface` |
| `validation/DaValidationService` — **À FAIRE** | `DaValidationTrait` (`validerDemandeApproAvecLignes`, `mettreAJourChoixDalr`, `exporterDaEnExcelEtPdf`, helpers Excel), `DaValidationReapproTrait` (`modifierStatut`, `validerDemande`, `refuserDemande`) | `em`, `UserDataService`, `DaService`, `DaAfficherService`, `ExcelService` |
| `validation/DaSoumissionValidationService` — **À FAIRE** | `ajouterDansDaSoumisAValidation` (3 versions → 1), `creationPDF{AvecDit,Direct,Reappro}`, `fusionAndCopyToDW`, `copyPDFToDW`, conversion Ghostscript | `em`, générateurs PDF (`GenererPdfDa*`), `TraitementDeFichier` |
| `affectation/DaAffectationService` — **À FAIRE** | `DaAffectationTrait` | `em`, `UserDataService`, `DaService`, `DaAfficherService`, `DaSoumissionValidationService` |
| `DaListeDitService` — **À FAIRE** | `DaListeDitTrait::data`, `criteriaIsObjectEmpty`, `ajoutNumSerieNumParc` ; la lecture du formulaire/session reste dans le contrôleur | `em`, `DitModel`, `SessionInterface` (il n'existe pas de classe `SessionService`) ou `UserDataService` |
| `DaIconService` — **FAIT** | `MarkupIconTrait` (sans état, sans dépendance) | aucune |
| `DaPrixFournisseurService` — **FAIT** | `PrixFournisseurTrait` (`gererPrixFournisseurs`, `formatPrix`) pour `EmailDaService` et `PdfTableMatriceGenerator` | aucune |
| `reappro/ReportingIpsService` — **À FAIRE** | `ReportingIpsTrait::getData` | `ReportingIpsModel`, `RollingMonthsService` |

Les traits « fantômes » (`DaPropositionTrait` vide, `DaNewDirectTrait`, `DaNewReapproPonctuelTrait`) n'ont pas de service : ils sont supprimés (voir code mort).

## Constructeur de `DaService` (votre question initiale)

Aujourd'hui : `em`, `FileUploaderForDAService` injectés ; 4 repositories + `FileCheckerService` construits dans le constructeur.
- `FileUploaderForDAService` : utilisé par `insertionObservation` seulement. Indispensable tant que cette méthode reste là ; sinon à déplacer avec elle.
- `FileCheckerService` : utilisé par `getAllDdpPath` seulement → à injecter dans le service qui porte les chemins de documents, pas dans `DaService`.
- `getDemandeAppro` / `getObservations` / `getDevisPjPath*` : usages différents (lecture vs documents rattachés). Les méthodes `getBaIntranetPath`, `getDevisPjPathDaLine`, `getDevisPjPathObservation`, `getOrPath`, `getAllDdpPath` sont appelées **dynamiquement** (`$service->$method(...)` dans `DocRattacheService`), donc ne pas les renommer/déplacer sans adapter `DocRattacheService` ; candidates à un `DaDocumentService` dans une phase ultérieure.
- `getJoursRestants`, `getLignesRectifiees`, `appliquerChangementStatut` : **FAIT côté service** (présentes dans `DaService`), mais jamais appelées ; à brancher lors de la migration de `DaTrait`. `getDeletedLineNumbers` n'est plus à prévoir dans `DaService` : elle est dans `DaAfficherService`.
- `insertionObservation` du service prend un `$username` explicite, contrairement au trait (`getUserName()` implicite) : le contrôleur (ex. `DaAffectationAchatController`) doit le fournir via `UserDataService`.

## Code mort à signaler (suppression après accord)

- Traits entiers non utilisés : `DaNewDirectTrait`, `DaNewReapproPonctuelTrait` (**vérifié 2026-10-07**). `DaPropositionTrait` n'est **pas** mort : c'est un wrapper vide de `DaTrait`, utilisé par `DaPropositionAvecDitTrait` et `DaPropositionDirectTrait` ; il disparaît avec la migration de `DaTrait`, pas avant.
- `DaDetailTrait` : ses 11 méthodes ne sont jamais appelées (`normalizePaths*`, `getBaIntranetPath`, `getBaDocuWarePath`, `getOrPath`, `getBcPath`, `getFacBlPath`, `getDevisPjPathDal`, `getDevisPjPathObservation`) + (`DaDetailTrait` référence aussi `dwDaReapproRepository`/`dwDaReapproPRepository` déclarés nulle part : planterait si appelé) les repos/modèles `dwBcApproRepository`, `dwFacBlRepository`, `dwDaDirectRepository`, `ditOrsSoumisAValidationRepository`, `dossierInterventionAtelierModel` des traits de détail. Ce rôle est déjà tenu par `DocRattacheService` → `DaService`.
- Méthodes : `DaListeDitTrait::agenceServiceEmetteur` et `agenceServiceEmetteurOption` (la 2e se nomme `Option`, :107) ; `DaService::getLignesRectifiees` (aucun appelant) ; `ReportingIpsTrait::calculQteEtMontantTotals` ; `DaDemandeDevisTrait::initDaDemandeDevisTrait` ; `cheminDeBase` (`DaValidationReapproTrait`) ; `$ditOrsSoumisAValidationRepository` (`DaPropositionAvecDitTrait`).
- `use` inutiles : `MarkupIconTrait` dans `DaSearchType` et `DaListCdeFrnController` ; `DaTrait` dans `DitOrsSoumisAValidationController` ; `lienGenerique` redondant dans les contrôleurs de détail ; `DaTrait` redondant avec `DaAfficherTrait` dans `DaAfficherController`. (Vérifié : `demandeApproParentRepository` de `DaAffectationTrait` **est utilisé** par `DaAffectationAchatController:35`, donc retiré de la liste.)

## Bugs évidents à signaler (comportement préservé, aucun correctif dans cette migration)

1. `DaEditAvecDitTrait:94` et `DaEditDirectTrait:94` : DALR dont la ligne DAL vient d'être supprimée → index indéfini puis appel sur null.
2. `ExportExcelController` appelle `ReportingIpsTrait::getData` (:69), qui lit `$this->rollingMonthsService` non défini dans ce contrôleur (défini seulement dans `ReportingIpsController`) ; en plus `getData` renvoie `['results','totals']` alors que le contrôleur lit `['reportingIps']`, `['qteTotale']`, `['montantTotal']` (:45-62) : l'export Excel est cassé deux fois.
3. `getUser()` non gardé (`DaValidationTrait:30`, `DaValidationReapproTrait:45`, `DaTrait:96`) ; `agenceServiceIpsObjet()` peut renvoyer des null avant `->getCodeAgence()` (`DaNewAchatTrait:35-45`, idem `DaNewAvecDitTrait:56-58`).
4. `DaValidationDirectTrait` : chemin Ghostscript Windows en dur (:146), `echo` dans la logique métier (:165), commande sans `escapeshellarg` (:159), fichier source écrasé (:170). `PdfConversionTrait` existe déjà.
5. `DaService::getLignesRectifiees:108` : `->first()` peut renvoyer `false` dans le tableau ; ne filtre pas `deleted` contrairement à `DaTrait:124` ; méthode sans appelant.
6. `DaAfficherTrait:62-64` : `$demandeAppro` non vérifié avant `getDit()`.
7. `DaNewAvecDitController:46-52` : `$dit = find()` peut être null et est déréférencé ; `:121` `getPrixUnitaire(...)[0]` non gardé. (`DaNewAvecDitTrait:153` est en fait sûr : `getNumeroEtStatutOr` renvoie toujours un tableau de 2 éléments.)
8. `DaNewReapproMensuelTrait:67-99` : numéros de ligne qui peuvent entrer en collision.
9. `DaValidationTrait:63` : DALR chargées sans filtre `deleted` (l'entité n'a pas de champ version ; asymétrique avec DAL).
10. `DaAffectationTrait:208-218` : cache `oldObservations` ignorant le numéro de DA ; `:148` clé de préfixe non gardée.
11. `DaPropositionAvecDitTrait::$daObservationRepository` jamais initialisée par son propre trait (initialisée par `DaDetailAvecDitTrait`).
12. `DaListeDitTrait` : N+1 SQL dans `ajoutNumSerieNumParc` (:187-191) ; conditions testées deux fois (:154-157) ; `criteria['categorie']` traité différemment des autres.
13. Commentaires faux : « 3 jours » alors que le code ajoute 5 (Direct :48, Mensuel :59, Ponctuel :49 ; `DaNewAvecDitTrait:146` annonce 3 pour 5/7/10/15 jours).
14. **Nouveau** : `DaAffectationAchatController:35-37` déréférence `find($id)` sans garde (id inconnu → erreur fatale) ; `getButtonName` y renvoie `'N\A'` (les traits renvoient `''`) et `traitementFormulaire` notifie `'type' => 'error'` alors que le reste du code utilise `'danger'`. Sa `getButtonName` publique masque celle, privée, de `DaNewAchatTrait` (pas de conflit), qui est donc morte dans ce contrôleur ; `initDaNewAchatTrait` n'y est pas appelé (sans effet, idempotent).

15. **Nouveau (2026-10-08)** : `DaAfficherService::generateDaAfficherOnCreationDa` / `…Parent` ne normalisent pas `artDesi` (les traits `DaNewTrait:46`, `DaNewAchatTrait:97` le font) → divergence à corriger dans le service (3.0), sinon régression silencieuse (apostrophes/guillemets Word).
16. **Nouveau** : signature d'`insertionObservation` différente entre trait et service (voir « Étape 3 »), et `getUser()->getNomUtilisateur()` (trait) vs `getUserName()` session (service) : risque de valeur différente.
17. **Nouveau** : `DaService::getLignesRectifiees` (relations Doctrine, `->first()` possible `false`, pas de filtre `deleted`) ≠ `DaTrait::getLignesRectifieesDA` (requêtes, DALR indexés par numéro de ligne même sans lien DAL). Alignée sur le trait en 3.0.
18. **Nouveau** : `DaAfficherTrait::ajouterDansTableAffichageParNumDa` fait `->getDit()` sans garde (voir 6) et `$oldDaAffichers[0]` après `!empty` : sûr uniquement si le repository renvoie un tableau indexé à partir de 0.

## Doublons à fusionner pendant la migration

`getButtonName` (6 copies : `DaNewAchatTrait`, `DaNewAvecDitTrait`, `DaNewDirectTrait`, `DaNewReapproMensuelTrait`, `DaNewReapproPonctuelTrait` + nouvelle copie publique dans `DaAffectationAchatController` → 1 helper côté contrôleur) ; `generateDemandApproLinesFromReappros` (2) ; `DaTrait::getDeletedLineNumbers` / `insertionObservation` / `ajouterDaDansTableAffichage*` déjà dupliqués dans `DaAfficherService` et `DaService` (supprimer côté trait à la migration) ; `modificationDa`/`modificationDAL` (2) ; `prepareDataForDisplayDetail` (2) ; `ajouterDansDaSoumisAValidation` (3) ; `exporter{AvecDit,Direct}EnExcelEtPdf` (même corps, callback différent) ; `setAllFournisseurs` (2) ; `modifierStatut` vs `appliquerChangementStatut` ; la déclaration de `daObservationRepository` dans 5 traits ; `ConvertirLesPdf`/`convertPdfWithGhostscript` (~20 classes, hors périmètre sauf celles de `Traits/da`).

## Remplacement des helpers de `Controller` (étape 0)

| Helper `Controller` | Dans un service |
|---|---|
| `getUserName`, `getUser` (`?User`), `getUserId`, `getUserMail` | `UserDataService` (mêmes noms ; `getProfilId` renvoie `?int` au lieu de `string`) |
| code société, codes/ids agence et service de l'utilisateur | `UserDataService::getCodeSociete()`, `getCodeAgenceUser()`, `getCodeServiceUser()`, `getAgenceIdUser()`, `getServiceIdUser()` |
| `estAdmin`, `estAppro`, `estAtelier`, `estEnergie`… | `SecurityService` (à injecter ; alias dans `services_framework.yaml`) |
| `getSessionService()` | `SessionInterface` Symfony |
| `getEntityManager`, `getTwig`, `getUrlGenerator` | `EntityManagerInterface`, `Twig\Environment`, `UrlGeneratorInterface` |
| `agenceServiceIpsObjet()` | **aucun équivalent** : à porter dans `DaCreationService` (`em` + `UserDataService`) ; renvoie `null` pour agence et service en cas d'échec |
| `redirectToRoute()` | jamais dans un service (fait un `exit`) |

Consommateurs de `DaIconService` / `DaPrixFournisseurService` : instanciés par `new` (comme `PermissionDaService`), car `DaAfficherMapper`, `EmailDaService` et `PdfTableMatriceGenerator` sont eux-mêmes créés par `new`.

## Ordre de migration recommandé (quand vous passerez à l'implémentation)

0. **FAIT** — Lire `UserDataService`, `SecurityService`, `SessionService`, `Controller.php` (`agenceServiceIpsObjet`, `getUserName`) pour confirmer ce qui remplace `getUser`/session/agence.
1. **FAIT (2026-10-07, non commité)** — Code mort supprimé : `DaNewDirectTrait`, `DaNewReapproPonctuelTrait`, `DaDetailTrait` (les traits de détail font `use DaTrait` directement), repos/modèles inutilisés des traits de détail et de `DaPropositionAvecDitTrait`, `DaListeDitTrait::agenceServiceEmetteur`/`Option`, `ReportingIpsTrait::calculQteEtMontantTotals`, `initDaDemandeDevisTrait`, `cheminDeBase` de `DaValidationReapproTrait`, `use` inutiles (`MarkupIconTrait` ×2, `DaTrait` ×2, `lienGenerique` ×2). Restent : `DaPropositionTrait` (wrapper utilisé), `DaService::getLignesRectifiees` (sans appelant mais à brancher à l'étape 3).
2. **FAIT (2026-10-07, non commité)** — Services sans dépendance : `DaIconService`, `DaPrixFournisseurService` (consommateurs : `DaAfficherMapper`, `EmailDaService`, `PdfTableMatriceGenerator`) → valide le câblage yaml.
3. `DaService` (déjà là, **PARTIEL**) étendu avec `DaTrait`, puis `DaAfficherService` (**PARTIEL** : compléter avec `ajouterDansTableAffichageParNumDa`, puis brancher ~10 contrôleurs ; aujourd'hui aucun ne l'utilise). **→ détaillé ci-dessous (« Étape 3 : découpage »), un contrôleur par partie.**
4. Par domaine, un contrôleur à la fois : création → édition → détail → validation/soumission → proposition → affectation → liste DIT → reappro.
5. Supprimer chaque trait dès que plus personne ne l'utilise ; fin : `grep "Traits\\\\da"` ne doit rien renvoyer.

## Étape 3 : découpage (un contrôleur par partie, du plus simple au plus risqué)

**Règles de travail validées (2026-10-08)**
- Périmètre : uniquement ce qui relève de `DaTrait`, `DaAfficherTrait`, `DaNewTrait`, `DaNewAchatTrait` et `DaDemandeDevisTrait::appliquerStatutDemandeDevisEnCours`. Les autres traits d'un contrôleur restent jusqu'à l'étape 4 ; un contrôleur peut donc utiliser trait **et** service. Si un contrôleur n'a besoin que du trait, il n'est pas migré.
- Les traits qui font `use DaTrait` (`DaValidationTrait`, `DaEditTrait`, `DaDetail*Trait`, `DaPropositionTrait`, `DaDemandeDevisTrait`, `DaNewTrait`, `DaAffectationTrait` via `DaAfficherTrait`…) ne sont **pas** migrés ici (étape 4). `DaTrait` et `DaAfficherTrait` ne sont supprimés que quand plus aucun consommateur.
- Divergence trait/service : le service est aligné sur le trait ; la divergence est signalée ici et l'utilisateur tranche (par défaut : version du trait). Comportement strictement préservé, bugs signalés seulement.
- **Aucun commit par Claude** : après chaque petite étape et chaque contrôleur, Claude prépare le message de commit, l'utilisateur commite. Le plan (statuts FAIT/PARTIEL) est mis à jour après chaque partie.
- Avant chaque contrôleur : vérifier qui instancie ou étend le contrôleur (injection constructeur). Après chaque partie : `php bin/console lint:container`, `grep -r "Traits\\\\da" src`, parcours manuel de la liste de la partie. Claude liste à l'utilisateur les déclarations yaml à ajouter (yaml géré par l'utilisateur ; `DaService`/`DaAfficherService`/`UserDataService` supposés autowirables).

### Partie 3.0 : préparer les services (aucun contrôleur touché) — **FAIT (2026-10-08, non commité)**

Implémenté tel que décrit ci-dessous ; `php -l` OK. Reste à faire par l'utilisateur : déclaration yaml éventuelle de `DaAfficherService` (arguments `EntityManagerInterface`, `DaService`, `UserDataService`) puis `lint:container`.

`DaService`
- Ajouter `normalizeTypographicChars` (identique au trait, à rendre `public`).
- Aligner `getLignesRectifiees` sur `DaTrait::getLignesRectifieesDA` : nouvelle signature `(string $numeroDA, int $version): array` (le service actuel prend `iterable $lignesDAL`) ; mêmes requêtes (DAL non `deleted` de la version, tous les DALR du numéro indexés par numéro de ligne si `choix`). La méthode reste sans appelant dans cette partie ; elle est appelée par `DaAfficherService` (3.0) et le sera ailleurs à l'étape 4.
- Ajouter `appliquerStatutDemandeDevisEnCours(DemandeAppro, string $username)`.
- Aucune dépendance de constructeur nouvelle (`em` et les repositories DAL/DALR y sont déjà).

`DaAfficherService`
- Compléter `generateDaAfficherOnCreationDa` et `generateDaAfficherOnCreationDaParent` : **divergence trouvée**, les traits (`DaNewTrait:46`, `DaNewAchatTrait:97`) appellent `normalizeTypographicChars($daAfficher->getArtDesi())` avant `persist`, pas les services → à ajouter.
- Ajouter `ajouterDansTableAffichageParNumDa(string $numDa, bool $validationDA = false, string $statut = '', $dateDemande = null)` (copie de `DaAfficherTrait`) ; remplace `getUserName()` par `UserDataService::getUserName()`, `getLignesRectifieesDA`/`normalizeTypographicChars` par `DaService`.
- Constructeur : `em`, `DaService`, `UserDataService` (+ repositories `DemandeAppro`, `DemandeApproL`, `DaAfficher`). Pas de cycle : `DaService` ne dépend pas de `DaAfficherService`. Remarque : `UserDataService` ne sert qu'à une méthode (principe 1) ; acceptable, sinon passer `$username` en paramètre comme pour `insertionObservation`.
- `getDeletedLineNumbers` y est déjà (identique au trait).

Commit proposé : `feat: DaService et DaAfficherService alignés sur DaTrait/DaAfficherTrait`
Vérification : `lint:container`, `debug:container App\Service\da`.

### Contrôleurs (ordre de migration)

Piège commun : `DaService::insertionObservation($numDa, $observation, **$username**, ?$files)` alors que le trait est `($numDa, $observation, ?$files)`. Les appels `insertionObservation(..., $daObservation->getFileNames())` doivent passer `$this->getUserName()` en 3e argument ; sinon `TypeError`. Autre piège : le trait enregistre `$this->getUser()->getNomUtilisateur()`, le service reçoit `getUserName()` (session `user_info['username']`) : à confirmer que les deux valeurs sont identiques.

| Partie | Contrôleur | Appels à basculer | Traits retirables | Parcours manuel |
|---|---|---|---|---|
| 3.1 **FAIT (2026-10-08, non commité ; contrôleur déplacé vers `src/Api/da/`, namespace `App\Api\da`)** | `DaAfficherController` | `ajouterDansTableAffichageParNumDa` (:32) | `DaAfficherTrait` | Ouvrir la liste/la page qui déclenche l'appel avec statut OR validé ; vérifier la nouvelle ligne `DaAfficher` (version +1, statut OR) |
| 3.2 **FAIT (2026-10-08, non commité ; `DaDemandeDevisTrait` supprimé, `find` via `DaService::getDemandeAppro`)** | `DemandeDevisController` | `appliquerStatutDemandeDevisEnCours` (:38), `ajouterDansTableAffichageParNumDa` (:40) | `DaDemandeDevisTrait`, `DaAfficherTrait` | Demander un devis sur une DA ; statut DA « demande devis », `devisDemandePar` rempli, ligne `DaAfficher` créée |
| 3.3 | `DaAffectationAchatController` | `insertionObservation` (:93), `ajouterDaDansTableAffichageParent` (:96) ; **le contrôleur garde `DaAffectationTrait`** qui appelle encore l'ancien trait | `DaNewAchatTrait` (sa `getButtonName` est propre) | Passer la DA au demandeur avec motif ; affecter une DA ; observation créée, ligne `DaAfficher` parente |
| 3.4 | `DaNewAchatController` | `getJoursRestants` (:121), `insertionObservation` (:141), `ajouterDaDansTableAffichageParent` (:144) | garde `DaNewAchatTrait` (initialisation, etc.) | Créer une DA Achat avec observation ; `joursDispo` correct ; ligne `DaAfficher` ; caractères typographiques normalisés |
| 3.5 | `DaNewReApproMensuelController` | `getJoursRestants` (:90), `insertionObservation` (:111), `ajouterDaDansTableAffichage` (:114) | garde `DaNewReapproMensuelTrait` | Créer une DA Reappro mensuel (création et re-création `firstCreation=false`) |
| 3.6 | `DaNewAvecDitController` | `getJoursRestants` (:123), `insertionObservation` (:147), `ajouterDaDansTableAffichage` (:150, avec `$dit`) | garde `DaNewAvecDitTrait` | Créer une DA avec DIT (avec/sans observation) ; DIT rattachée dans `DaAfficher` |
| 3.7 | `DaDetailDirectController`, 3.8 `DaDetailAvecDitController` | `insertionObservation` (:102/:108), `appliquerChangementStatut` (:105/:111), `ajouterDansTableAffichageParNumDa` (:107/:113) | `DaAfficherTrait` (si plus aucun appel direct) ; `DaDetail*Trait` restent | Afficher le détail ; ajouter une observation avec/sans PJ ; autoriser l'émetteur (changement statut) |
| 3.9 | `DaEditDirectController`, 3.10 `DaEditAvecDitController` | `ajouterDansTableAffichageParNumDa` (×3), `insertionObservation` ; `getJoursRestants` est dans `DaEditXTrait` → reste | `DaAfficherTrait` | Modifier une DA (lignes ajoutées/supprimées/modifiées) ; observation ; nouvelle version `DaAfficher` |
| 3.11 | `DaValidationAvecDitController`, 3.12 `DaValidationDirectController`, 3.13 `DaValidationReapproMensuelController` | `ajouterDansTableAffichageParNumDa` (:45/:47) ; `insertionObservation` (:91, :132) ; **`DaValidationReapproTrait` appelle encore l'ancien trait (reste)** | `DaAfficherTrait` seulement pour AvecDit/Direct | Valider une DA (statut, date validation, Excel/PDF), refuser, observation |
| 3.14 | `DaPropositionRefAvecDitController`, 3.15 `DaPropositionArticleDirectController` | `insertionObservation` (×3), `ajouterDansTableAffichageParNumDa` (×7/×6) | `DaAfficherTrait` | Proposer/choisir/valider des lignes, observation, retour au statut précédent, validation avec DW |
| 3.16 | `DaDetailReapproController` | déjà sur `DaService` (référence) ; brancher `getLignesRectifiees` si pertinent | — | — |
| 3.17 | **Branchement final** | `DaService::getLignesRectifiees` appelé par `DaAfficherService` ; supprimer `DaTrait::getLignesRectifieesDA`/`getDeletedLineNumbers`/etc. seulement si plus aucun consommateur (sinon étape 4) | — | Régression : valider + modifier une DA |

Points à vérifier avant 3.3, 3.11–3.13 : `DaAffectationTrait:92,102` et `DaValidationReapproTrait:102,108` continuent d'appeler `$this->insertionObservation` / `ajouterDansTableAffichageParNumDa` ; `DaTrait`/`DaAfficherTrait` doivent rester disponibles pour ces contrôleurs jusqu'à l'étape 4.

## Vérification (à définir avec vous : pas de tests automatisés)

- `php bin/console lint:container` et `debug:container App\Service\da` après chaque étape (détecte les dépendances manquantes et cycles).
- `grep -r "Traits\\\\da" src` : liste des utilisateurs restants, doit diminuer à chaque étape.
- Parcours manuel par contrôleur migré : créer, modifier, valider (AvecDit, Direct, Reappro), afficher le détail, export Excel/PDF, affectation Achat, liste DIT.
- Risques : (1) `Controller::redirectToRoute` fait `exit`, donc un service ne doit jamais l'appeler ; (2) les traits et leur contrôleur partagent des propriétés privées homonymes, relire chaque contrôleur pour les `$this->xxxRepository` utilisés directement ; (3) `JoursOuvrablesTrait` (hors `Traits/da`) est utilisé par les traits de création : décider avant l'étape 4 si le service l'utilise tel quel ou si on le convertit aussi.
