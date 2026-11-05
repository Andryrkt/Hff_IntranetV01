---
title: Demande de paiement magasin – à l'avance
category: Demande de paiement
description: DDPA, acompte sur facture proforma avant l'arrivée de la marchandise, de la saisie à la transmission en validation.
---

| | |
|---|---|
| **Code décrit** | branche `feature/ddp-neg`, commit `30f4c35c9` (02/11/2026) |
| **État du code** | pas encore sur `main` |
| **Guide utilisateur** | page `ddp-magasin-avance` de la table `documentation_utilisateur` |
| **Fiche liée** | Demande de paiement magasin – après arrivage |

> **Source de ce document.** Aucun cahier des charges n'est présent dans le dépôt. Ce document décrit donc **ce que fait le code**, pas ce qui a été spécifié. Les écarts et les décisions à prendre sont regroupés à la section 10.

## 1. Objet

Payer un acompte au fournisseur, sur facture proforma, pour une commande validée mais pas encore réceptionnée. L'utilisateur remplit un formulaire dans l'intranet. L'application génère et enregistre un PDF. La demande est ensuite transmise en validation depuis l'écran de consultation, et c'est à ce moment que le PDF est déposé dans DocuWare.

| ID type | Code en base | Constante | Document | Page de garde |
|---|---|---|---|---|
| 1 | `DPA` | `ID_DEMANDE_PAIEMENT_A_L_AVANCE` | **DDPA** | DDP |

Les DDPA validées sont ensuite déduites du montant à payer après arrivage (voir la fiche liée).

## 2. Composants

| Rôle | Fichier |
|---|---|
| Contrôleur (soumission, fichiers) | [DdpController](../../../src/Controller/ddp/DdpController.php) |
| Initialisation du DTO, numéro, copie des fichiers | [DdpFactory](../../../src/Factory/ddp/DdpFactory.php) |
| Formulaire et DTO | [DdpType](../../../src/Form/ddp/DdpType.php), [DdpDto](../../../src/Dto/ddp/DdpDto.php) |
| Cumul des % validés | [DemandePaiementRepository](../../../src/Repository/ddp/DemandePaiementRepository.php) `::getCumulRatioDdpValideParCommande` |
| Commandes à ne plus proposer | [DemandePaiementRepository](../../../src/Repository/ddp/DemandePaiementRepository.php) `::getCommandesAvecDdpAvanceEnCours`, `::getCommandesAvanceSoldees` |
| Persistance | [Service/ddp/Magasin](../../../src/Service/ddp/Magasin/), [Mapper/ddp/Magasin](../../../src/Mapper/ddp/Magasin/), `DemandePaiementCommandeMapper` |
| Requêtes IPS | [DdpModel](../../../src/Model/ddp/DdpModel.php), [DemandePaiementModel](../../../src/Model/ddp/DemandePaiementModel.php) |
| Page de garde | [GeneratePdfDdp](../../../src/Service/genererPdf/GeneratePdfDdp.php) |
| API du formulaire | [InfoFournisseurApi](../../../src/Api/ddp/InfoFournisseurApi.php) |
| Consultation et transmission | [BonApayerController](../../../src/Controller/da/ddp/BonApayerController.php), [DdpApiController](../../../src/Api/ddp/DdpApiController.php) |
| Vue et front | [magasin/new.html.twig](../../../Views/templates/ddp/magasin/new.html.twig), [DemandePaiementManager.js](../../../Views/js/ddp/DemandePaiementManager.js) |
| Constantes | [TypeDemandePaiementConstants](../../../src/Constants/ddp/TypeDemandePaiementConstants.php), [StatutConstants](../../../src/Constants/ddp/StatutConstants.php) |

Routes principales : `new_ddp` (`/ddp/new/1`) et `da_bon_a_payer` (`/demande-appro/consultation-facture/0`).

## 3. Flux

### 3.1 Saisie et enregistrement

1. L'utilisateur choisit un fournisseur. Le front charge les commandes **validées dans DocuWare** (dernière version) et **non réceptionnées dans IPS**, sans celles qui ont une DDPA en cours (RG-AV-13) ou qui sont déjà soldées (RG-AV-06).
2. Il choisit une seule commande. Le front affiche l'historique de ses demandes et bloque si elle est déjà soldée.
3. Il saisit le n° et le montant de la facture proforma, voit le montant total de la commande remonté d'IPS, puis joint ses fichiers.
4. Le serveur relit le montant total dans IPS et bloque s'il est introuvable (RG-AV-04), puis contrôle le blocage à 100 %, le dépassement du cumul, l'absence de DDPA en cours et le plafond (RG-AV-06, RG-AV-14, RG-AV-13, RG-AV-03).
5. Le serveur génère un numéro `DDP`, enregistre les données, puis génère et fusionne le PDF.
6. La demande a le statut `DDPA à transmettre`, et l'utilisateur est redirigé vers la consultation.

### 3.2 Transmission en validation

Depuis la consultation, l'utilisateur coche des demandes `… à transmettre`, puis le front appelle `POST /api/transmettre-bap-compta` :

Si aucun numéro de demande n'est reçu, le serveur répond par une erreur, sans générer de numéro `CLA`. Sinon :

1. un numéro `CLA` commun est généré ;
2. chaque demande passe à `Soumis à validation`, avec `numero_cla`, `date_soumission_compta`, `deposer_dw` et `date_depot_dw` renseignés ;
3. le PDF est copié dans DocuWare sous le nom `{numéro}#{CLA}.pdf`.

### 3.3 Suivi étape par étape

Écran **Admin › Historique › Suivi des opérations** (`SuiviOperationService`, table `historique_operation_etape`). Chaque étape y figure avec son statut, sa durée, ses requêtes et son erreur éventuelle. Les contrôles qui refusent le formulaire avant ces étapes n'y figurent pas.

| Opération | Type | N° document | Étapes |
|---|---|---|---|
| Enregistrement | `DDP` | Numéro `DDP` | Numéro et fichiers joints ; Base de données ; Page de garde ; Fusion |
| Transmission | `DDP_TRANSMISSION` | Numéro `CLA` | Numéro CLA ; une étape « Transmission {numéro} » par demande, avec la copie DocuWare ; Base de données |

Une copie DocuWare échouée ne bloque pas la transmission (P-AV-09), mais passe son étape en échec dans ce suivi.

## 4. Règles de gestion

« Front » et « Serveur » indiquent où la règle est réellement appliquée. Une règle appliquée uniquement côté front peut être contournée par une requête forgée.

| Réf. | Règle | Front | Serveur |
|---|---|---|---|
| RG-AV-01 | Commandes proposées = validées DW et non réceptionnées IPS | ✔ | ✘ |
| RG-AV-02 | Une seule commande par demande | ✔ | ✔ |
| RG-AV-03 | Montant proforma ≤ 20 000, quelle que soit la devise | ✔ | ✔ |
| RG-AV-04 | Montant total de la commande non modifiable, lu dans IPS (`frn_cde.fcde_mtn`, fournisseur + commande) ; introuvable ou nul → enregistrement bloqué. Le serveur relit IPS et ignore la valeur postée | ✔ | ✔ |
| RG-AV-05 | % d'une DDPA = montant proforma ÷ montant total de la commande × 100 ; 0 si le total est absent | – | ✔ |
| RG-AV-06 | Demande refusée si le cumul des % des DDPA validées ≥ 99,99, et commande plus proposée. En dessous, la commande reste proposée : une avance peut se payer en plusieurs fois, sans dépasser RG-AV-14 | ✔ | ✔ |
| RG-AV-07 | PDF de commande DocuWare introuvable → bouton Enregistrer désactivé | ✔ | ✘ |
| RG-AV-08 | Pièces jointes : PDF, 5 Mo maximum, sans obligation de présence côté serveur ; PJ03 désactivée | ✔ | ✔ |
| RG-AV-09 | RIB différent de ceux connus dans IPS (`fou_bqe`) → mention « RIB mis à jour » sur la page de garde | – | ✔ |
| RG-AV-10 | Valeurs fixes : agence `01`, service `NEG`, société `HF` | – | ✔ |
| RG-AV-11 | N° facture proforma obligatoire, 50 caractères maximum, enregistré dans `numero_facture` et imprimé sur la page de garde | ✔ | ✔ |
| RG-AV-12 | Onglet « Historique », ouvert par défaut, avec le montant total de la commande dans le titre : type DDP, numéro, n° facture, date, motif, montant HT, %, % cumul, statut, puis montant HT, total déjà validé et montant restant | ✔ | – |
| RG-AV-13 | Une seule DDPA en cours par commande : une commande ayant une DDPA ni validée ni refusée (dernière version, même fournisseur et société) n'est plus proposée, et son enregistrement est refusé. Elle redevient disponible si cette demande est refusée ou validée, sauf si le cumul atteint alors RG-AV-06 | ✔ | ✔ |
| RG-AV-14 | Demande refusée si le cumul des % des DDPA validées + le % de la nouvelle demande dépasse 100,01. Le message indique le montant maximum. Les DDPA en cours ne comptent pas : RG-AV-13 n'en autorise aucune sur la commande à ce moment | ✔ | ✔ |

**« Validée »** signifie toujours : statut `Validé` ou `Transmis compta`, sur la dernière version de la demande.

## 5. Statuts

| Statut | Écrit par |
|---|---|
| `DDPA à transmettre` | Soumission du formulaire |
| `Soumis à validation` | Transmission (`DdpApiController::transmettreBap`) |
| `Validé`, `Transmis compta`, `Refusé`, `Refusé Compta`, `Réfusé par Chef de Service`, `A valider comptabilité` | Une **moulinette** externe pour le passage à `Validé` ou refusé. Aucun code de `src/` n'écrit ces statuts, ils y sont seulement lus |

Les statuts `Refusé`, `Refusé Compta` et `Réfusé par Chef de Service` sont exclus de l'historique d'une commande (`getDdpSelonNumCde`).

## 6. Données

### Tables de l'intranet

| Table | Contenu pour une DDPA |
|---|---|
| `demande_paiement` | Une ligne. `montant_a_payer` = montant proforma, `montant_total_commande` = total IPS, `numero_commande` = la commande, `numero_facture` = n° facture proforma (vide sur les demandes plus anciennes) |
| `demande_paiement_ligne` | Une ligne. `numero_facture` = n° facture proforma (`-` sur les demandes plus anciennes), `montant_facture` = montant proforma, `ratio_montant_payer` = % de la demande, `montant_total_commande` = total IPS, `montant_commande_receptionner` et `montant_commande_a_payer` vides |
| `demande_paiement_commande` | Une ligne. `client = 'magasin'`, `montant_total_commande` = total IPS, `montant_commande_receptionner`, `montant_commande_a_payer` et `solde_avance` vides |
| `document_demande_paiement` | Une ligne par fichier joint. Ne contient pas le PDF final |
| `type_demande` | Référentiel des types |
| `dw_commande` | Chemins des PDF de commande DocuWare, en lecture seule ici |

`commande_livraison` n'est pas alimentée pour une DDPA.

### Systèmes externes

| Système | Accès via | Usage |
|---|---|---|
| IPS (Informix) | `frn_llf`, `frn_liv`, `frn_cde`, `fou_bqe`, `agr_tab`, `agr_dev` | Réceptions, montant HT, RIB, modes de paiement, devises |
| DocuWare | Dossier de dépôt (`basePathDocuWare`) | Dépôt des PDF |

## 7. Fichiers et DocuWare

**Dossier de travail** : `{BASE_PATH_FICHIER}/ddp/{numéro}/`.

**PDF final** : `{numéro}.pdf`, composé dans cet ordre de la page de garde, des fichiers téléversés et des PDF de commande DocuWare. Un fichier absent est ignoré et tracé dans `error_log`.

| Champ | Contenu | Nom enregistré |
|---|---|---|
| `pieceJoint01` | Proforma facture fournisseur | `PROFORMA_{numéro}.pdf` |
| `pieceJoint02` | RIB fournisseur | `RIB_{numéro}.pdf` |
| `pieceJoint03` | BC client (champ désactivé) | `BC_{numéro}_{index}.pdf` |
| `pieceJoint04` | Autres documents | `AUTRES FICHIERS_{numéro}.pdf` |

**Dépôt DocuWare** : uniquement à la transmission, comme le flux appro, dans `DEMANDE_DE_PAIEMENT/{numéro}#{CLA}.pdf`. La soumission ne dépose rien.

## 8. API du formulaire

| Route | Rôle |
|---|---|
| `GET /api/info-fournisseur-ddp` | Fournisseurs (autocomplétion), une ligne par fournisseur |
| `GET /api/num-cde-frn/{fournisseur}/1` | Commandes validées et non réceptionnées, sans DDPA en cours et pas encore soldées |
| `GET /api/montant-commande-ht/{numCde}` | Montant HT, totaux, historique, `ratioAvanceValide`, `commandeSoldee` |
| `GET /api/montant-total-commande/{fournisseur}/{numCde}` | Montant total de la commande affiché dans le champ |
| `GET /api/fichiers-commande-fournisseur/{cdes}`, `/api/fichiers-or-valide/{cdes}`, `/api/fichiers-bc-client/{cdes}`, `/api/telecharger-fichier-commande-dw` | Aperçu des PDF DocuWare |
| `POST /api/transmettre-bap-compta` | Transmission en validation (écran de consultation) |

## 9. Déploiement et configuration

**Scripts SQL**, dans [sql/COMPTA/ddp/](../../../sql/COMPTA/ddp/), à passer pour tout le module, car DDPA et après arrivage partagent les mêmes entités :

- `alter_demande_paiement_numero_commande.sql` : passe `numero_commande` en `NVARCHAR(MAX)` ;
- `demande_paiement_ajout_montant_total_commande.sql` : ajoute `montant_total_commande` et supprime `montant_facture_proforma` ;
- `ddp_ajout_montant_commande_a_payer.sql` : ajoute ou renomme `montant_commande_a_payer` ;
- `ddp_ajout_montant_commande_receptionner.sql` : ajoute `montant_commande_receptionner`, y copie les montants existants et recalcule `montant_commande_a_payer` ;
- `demande_paiement_commande_ajout_solde_avance.sql` : ajoute `solde_avance` ;
- `ddp_ajout_montant_total_commande.sql` : ajoute `montant_total_commande` à `demande_paiement_ligne` et `demande_paiement_commande`.

**Prérequis** :

- applications `DDP` et `CLA` présentes dans la table des applications (compteurs de numéros) ;
- type 1 présent dans `type_demande` ;
- variable d'environnement `BASE_PATH_FICHIER` ;
- accès du serveur web au dossier DocuWare.

**Accès** : `SecurityService` contrôle la permission `VOIR` par route pour les pages. Pour les routes API, il exige seulement une session ou un token JWT valide.

## 10. Points à arbitrer

Constats issus de la lecture du code ; aucun n'a été reproduit en exécution. La colonne « Décision » reste à compléter.

### Risque de paiement

| Réf. | Constat | Conséquence concrète | Décision |
|---|---|---|---|
| P-AV-01 | Le blocage (RG-AV-06) ne regardait que le cumul déjà validé, sans y ajouter le montant de la nouvelle demande, et deux DDPA en cours étaient possibles | 88 % validés puis une nouvelle DDPA de 20 % : le cumul atteignait 108 % sans aucun blocage | Une seule DDPA en cours par commande (RG-AV-13) ; nouvelle demande refusée si elle fait dépasser 100 % (RG-AV-14) |
| P-AV-02 | Le serveur accepte la commande postée (`choices` = valeurs soumises) ; RG-AV-01 et RG-AV-07 ne sont appliquées que côté front | Une requête forgée peut créer une DDPA sur une commande non validée ou sans PDF | |
| P-AV-03 | Le DTO garde la société `HF` à la soumission, alors que l'API utilise la société de l'utilisateur | Pour un utilisateur d'une autre société, le blocage affiché et le blocage serveur peuvent différer | |
| P-AV-04 | `getSommeMontantDdpValideParCommande` et `getCumulRatioDdpValideParCommande` comparent `numero_commande = :num`, alors que ce champ peut contenir `cde1;cde2` en après arrivage | Dans l'historique du formulaire, « Total déjà validé » est sous-estimé si la commande figure dans une demande après arrivage multi-commandes | |
| P-AV-05 | Le plafond de 20 000 ne tient pas compte de la devise | Le même seuil chiffré s'applique à toutes les devises | |
| P-AV-06 | Le % de l'historique se base sur le total enregistré à la création de chaque DDPA. Les demandes antérieures à RG-AV-04 gardent leur total saisi, sans contrôle | Sur ces anciennes demandes, un total erroné fausse encore le % puis le classement après arrivage | |
| P-AV-18 | RG-AV-13 n'est contrôlée au serveur que sur `new_ddp`. Le flux DA (`demande_paiement_da`) n'est volontairement pas bloqué, car « Confirmation Demande paiement » y confirme une DDPA déjà en cours. `new_demande_paiement` n'a pas ce contrôle, n'a pas de lien dans le menu, mais reste accessible par son URL. Aucune contrainte en base | Une DDPA peut encore être créée par ces écrans, ou par deux soumissions simultanées, sur une commande ayant déjà une DDPA en cours | Blocage sur `new_ddp` uniquement |
| P-AV-19 | Une DDPA validée sans montant total (ancienne demande) compte pour 0 % (RG-AV-05), et une DDPA validée multi-commandes n'entre pas dans le cumul | Une commande réellement payée à 100 % peut rester proposée | |

### Robustesse

| Réf. | Constat | Conséquence concrète | Décision |
|---|---|---|---|
| P-AV-07 | Aucune transaction : plusieurs `flush`, puis génération des fichiers | Une erreur sur le PDF laisse une demande enregistrée sans son PDF | |
| P-AV-08 | Le double dépôt DocuWare (soumission et transmission) a été retiré | Les demandes créées avant cette modification et encore « à transmettre » ont déjà un `{numéro}.pdf` dans DocuWare, et en auront un second à la transmission | Dépôt à la transmission seulement, comme l'appro |
| P-AV-09 | La transmission ne revérifie pas le statut côté serveur, et une erreur en cours de boucle n'annule pas les copies déjà faites | Une demande déjà traitée peut être repassée à `Soumis à validation` ; des fichiers restent dans DocuWare sans changement de statut | |
| P-AV-10 | `Validé` et les refus sont posés par une moulinette externe, absente du dépôt. `DdpController` et la transmission n'alimentent pas `historique_statut_ddp` | RG-AV-06 dépend du passage de la moulinette : tant qu'elle n'a pas tourné, une DDPA déjà validée compte comme non validée. L'historique des statuts reste vide pour la soumission et la transmission | |

### Sécurité

| Réf. | Constat | Conséquence concrète | Décision |
|---|---|---|---|
| P-AV-11 | `numeroFournisseur` (champ texte et paramètre d'URL) est concaténé dans le SQL IPS (`DdpModel`, `DemandePaiementModel`) | Injection SQL possible | |
| P-AV-12 | Les routes API ne vérifient que la connexion, y compris `transmettre-bap-compta` | Tout utilisateur connecté peut transmettre une demande (préfixe `api_` dans `SecurityService`) | |
| P-AV-13 | `InfoFournisseurApi` active `display_errors` | Détails techniques affichés dans les réponses en cas d'erreur | |

### Écarts et dette

| Réf. | Constat | Décision |
|---|---|---|
| P-AV-14 | Guide utilisateur réaligné sur le code et séparé en deux pages par `sql/documentation_utilisateur_separation_ddp_magasin.sql` ; l'ancienne page `ddp-magasin` repasse en brouillon | Script à exécuter sur chaque base |
| P-AV-15 | La proforma porte un `*` à l'écran mais n'est pas obligatoire côté serveur. La zone de dépôt PJ03 reste affichée alors que le champ est désactivé | |
| P-AV-16 | `DemandePaiementManager.js` : le `catch` de `chargerFichiersBcClient` utilise des variables non définies (`bloquerSiIntrouvable`, `fichier`) ; `afficherPdfCommandeValidee` ignore son paramètre `bloquerSiIntrouvable` | |
| P-AV-17 | Aucun test automatisé trouvé pour ce module (recherche par nom de fichier dans `tests/`) | |

## 11. Hors périmètre

Ces éléments utilisent en partie les mêmes tables, mais ne sont pas décrits ici : paiement après arrivage (fiche liée), demande de paiement DA/appro (`DemandePaiementDaController`), `DemandePaiementController`, modification (`EditDemandePaiementController`), dossier de régularisation (`DdpDossierRegularisationController`), liste compta (`DdpListeController`).

## 12. Historique de suivi

Évolutions du module et décisions. À compléter à chaque changement.

| Date | Commit | Changement ou décision | Réf. |
|---|---|---|---|
| 02/11/2026 | `691972555` | Création de cette fiche | |
| 02/11/2026 | `8f7a527aa` | Transmission : numéros de demande rétablis sur les cases à cocher ; une transmission vide renvoie une erreur et ne consomme plus de numéro `CLA` | section 3.2 |
| 02/11/2026 | `2663a8df0` | Montant total de la commande remonté d'IPS ; colonne `montant_total_commande` ajoutée à `demande_paiement_ligne` et `demande_paiement_commande` | RG-AV-04 |
| 02/11/2026 | `30f4c35c9` | **Décision :** montant total non modifiable, enregistrement bloqué s'il est introuvable dans IPS ou nul ; le % de la DDPA se calcule sur ce montant | RG-AV-04, RG-AV-05 |
| 02/11/2026 | à commiter | Suivi étape par étape ajouté à l'enregistrement et à la transmission | section 3.3 |
| 04/11/2026 | à commiter | N° facture proforma saisi et enregistré dans `numero_facture` ; historique déplacé dans l'onglet « Historique », avec le montant total dans le titre | RG-AV-11, RG-AV-12 |
| 05/11/2026 | `0d504bf61` | **Décision :** une commande n'est plus proposée que si une DDPA est en cours ou si le cumul validé atteint 100 % ; l'avance en plusieurs fois reste possible. Soumission refusée côté serveur sur `new_ddp` si une DDPA est en cours ; guide utilisateur mis à jour | RG-AV-06, RG-AV-13, P-AV-01, P-AV-18, P-AV-19 |
| 05/11/2026 | `0d504bf61` | **Décision :** nouvelle demande refusée, au front et au serveur, si elle fait dépasser 100 % au cumul des DDPA validées | RG-AV-14, P-AV-01 |
| 05/11/2026 | à commiter | Autocomplétion : une seule ligne par fournisseur, un RIB renseigné étant préféré à un RIB vide ; choix arbitraire entre deux RIB renseignés | section API |
