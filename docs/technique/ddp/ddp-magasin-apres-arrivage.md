---
title: Demande de paiement magasin – après arrivage
category: Demande de paiement
description: DDPL, paiement sur factures fournisseur après réception, de la saisie à la transmission en validation.
---

| | |
|---|---|
| **Code décrit** | branche `feature/ddp-neg`, commit `30f4c35c9` (02/11/2026) |
| **État du code** | pas encore sur `main` |
| **Guide utilisateur** | page `ddp-magasin-apres-arrivage` de la table `documentation_utilisateur` |
| **Fiche liée** | Demande de paiement magasin – à l'avance |

> **Source de ce document.** Aucun cahier des charges n'est présent dans le dépôt. Ce document décrit donc **ce que fait le code**, pas ce qui a été spécifié. Les écarts et les décisions à prendre sont regroupés à la section 10.

## 1. Objet

Payer le fournisseur une fois la marchandise arrivée, à partir de ses factures. L'utilisateur remplit un formulaire dans l'intranet. L'application génère et enregistre un PDF. La demande est ensuite transmise en validation depuis l'écran de consultation, et c'est à ce moment que le PDF est déposé dans DocuWare.

**Une saisie crée une seule demande**, avec un seul numéro et une seule page de garde, quels que soient le nombre de factures et de commandes. Les acomptes (DDPA) validés n'influencent que les montants, commande par commande.

| ID type | Code en base | Constante | Document | Page de garde |
|---|---|---|---|---|
| 2 | `DPL` | `ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE` | **DDPL** | DDP |

Le formulaire ne crée plus de BAP (type 4) ni de DDPR (type 3). Les demandes de ces types déjà enregistrées restent consultables et transmissibles. Leur ancienne page de garde BAP MAGASIN n'existe plus dans le code, mais leurs PDF déjà générés restent en place.

## 2. Composants

| Rôle | Fichier |
|---|---|
| Contrôleur (soumission, fichiers) | [DdpController](../../../src/Controller/ddp/DdpController.php) |
| Initialisation du DTO, numéro, copie des fichiers | [DdpFactory](../../../src/Factory/ddp/DdpFactory.php) |
| Formulaire et DTO | [DdpType](../../../src/Form/ddp/DdpType.php), [DdpDto](../../../src/Dto/ddp/DdpDto.php) |
| Situation d'avance d'une commande | [DemandePaiementRepository](../../../src/Repository/ddp/DemandePaiementRepository.php) `::getSituationAvanceParCommande` |
| Persistance | [Service/ddp/Magasin](../../../src/Service/ddp/Magasin/), [Mapper/ddp/Magasin](../../../src/Mapper/ddp/Magasin/), `DemandePaiementCommandeMapper`, `CommandeLivraisonMapper` |
| Requêtes IPS et GCOT (dont `getMontantCommandeReceptionnee` et `getMontantTotalCommande`) | [DdpModel](../../../src/Model/ddp/DdpModel.php), [DemandePaiementModel](../../../src/Model/ddp/DemandePaiementModel.php) |
| Page de garde | [GeneratePdfDdp](../../../src/Service/genererPdf/GeneratePdfDdp.php) |
| API du formulaire | [InfoFournisseurApi](../../../src/Api/ddp/InfoFournisseurApi.php) |
| Consultation et transmission | [BonApayerController](../../../src/Controller/da/ddp/BonApayerController.php), [DdpApiController](../../../src/Api/ddp/DdpApiController.php) |
| Vue et front | [magasin/new.html.twig](../../../Views/templates/ddp/magasin/new.html.twig), [DemandePaiementManager.js](../../../Views/js/ddp/DemandePaiementManager.js) |
| Constantes | [TypeDemandePaiementConstants](../../../src/Constants/ddp/TypeDemandePaiementConstants.php), [StatutConstants](../../../src/Constants/ddp/StatutConstants.php) |

Routes principales : `new_ddp` (`/ddp/new/2`) et `da_bon_a_payer` (`/demande-appro/consultation-facture/0`).

## 3. Flux

### 3.1 Saisie et enregistrement

1. L'utilisateur choisit un fournisseur. Le front charge ses factures GCOT liées à des commandes réceptionnées dans IPS.
2. Il choisit une ou plusieurs factures. Les commandes correspondantes s'affichent dans un tableau, avec leur solde avance, le montant du commande réceptionné lu dans IPS (non modifiable) et le montant du commande à payer calculé (RG-AA-13). Ces deux montants sont précédés de leur % du montant total de la commande (RG-AA-15).
3. Il saisit le montant total réceptionné, qui doit égaler la somme des montants réceptionnés du tableau. Le champ « Montant total à payer », non modifiable, affiche la somme des montants à payer. Les deux libellés portent leur % (RG-AA-16).
4. Le serveur **redéduit les commandes depuis les factures**, car le select est désactivé et n'est pas posté, puis **relit dans IPS le montant réceptionné de chaque commande** (RG-AA-03).
5. Le serveur lit le montant total de chaque commande dans IPS, calcule son solde avance et son montant à payer (RG-AA-05, RG-AA-08, RG-AA-13), puis le montant de la demande (RG-AA-07).
6. Il génère un numéro `DDP`, enregistre les données, puis génère et fusionne le PDF.
7. La demande a le statut `DDPL à transmettre`, et l'utilisateur est redirigé vers la consultation.

### 3.2 Transmission en validation

Depuis la consultation, l'utilisateur coche des demandes `… à transmettre`, puis le front appelle `POST /api/transmettre-bap-compta` :

Si aucun numéro de demande n'est reçu, le serveur répond par une erreur, sans générer de numéro `CLA`. Sinon :

1. un numéro `CLA` commun est généré ;
2. chaque demande passe à `Soumis à validation`, avec `numero_cla`, `date_soumission_compta`, `deposer_dw` et `date_depot_dw` renseignés ;
3. le PDF est copié dans DocuWare sous le nom `{numéro}#{CLA}.pdf`.

## 4. Règles de gestion

« Front » et « Serveur » indiquent où la règle est réellement appliquée. Une règle appliquée uniquement côté front peut être contournée par une requête forgée.

| Réf. | Règle | Front | Serveur |
|---|---|---|---|
| RG-AA-01 | Factures proposées = factures GCOT du fournisseur, sur commandes réceptionnées, hors pièces `regix.trs_ecr` du journal `Achmag` | ✔ | ✘ |
| RG-AA-02 | Commandes déduites des factures | ✔ | ✔ |
| RG-AA-03 | Montant du commande réceptionné lu dans IPS pour les factures sélectionnées, non modifiable. Le serveur relit IPS et ignore toute valeur postée. Introuvable pour une commande → enregistrement refusé | ✔ | ✔ |
| RG-AA-04 | Montant total réceptionné = somme des montants du commande réceptionnés (± 0,01) | ✔ | ✔ |
| RG-AA-05 | Situation de chaque commande selon le cumul des % DDPA validées : ≤ 0,01 → aucun acompte ; ≥ 99,99 → payée à 100 % ; sinon → payée en partie. Cette situation ne détermine que les montants | ✔ (aperçu) | ✔ |
| RG-AA-06 | Une saisie crée une seule demande, de type 2, quelles que soient les situations de ses commandes | – | ✔ |
| RG-AA-07 | `montant_a_payer` de la demande = somme des montants du commande à payer de ses commandes | – | ✔ |
| RG-AA-08 | Solde avance (commande payée en partie, sinon 0) = total commande de la dernière DDPA − (somme des proformas DDPA + `montant_commande_receptionner` des demandes validées non DDPA), ramené à 0 s'il est négatif | ✔ (aperçu) | ✔ |
| RG-AA-09 | PDF de commande DocuWare introuvable → bouton Enregistrer désactivé | ✔ | ✘ |
| RG-AA-10 | Pièces jointes : PDF, 5 Mo maximum, sans obligation de présence côté serveur ; PJ03 désactivée | ✔ | ✔ |
| RG-AA-11 | RIB différent de ceux connus dans IPS (`fou_bqe`) → mention « RIB mis à jour » sur la page de garde | – | ✔ |
| RG-AA-12 | Valeurs fixes : agence `01`, service `NEG`, société `HF` | – | ✔ |
| RG-AA-13 | Montant du commande à payer : 0 pour une commande payée à 100 %, ou si solde avance > 0 et montant réceptionné ≤ solde avance ; sinon montant réceptionné − solde avance | ✔ (aperçu) | ✔ |
| RG-AA-14 | Solde avance restant (affiché et imprimé, non enregistré) : solde avance − montant réceptionné si solde avance > 0 et montant réceptionné ≤ solde avance ; sinon 0 | ✔ | ✔ |
| RG-AA-15 | % en rouge et entre parenthèses, devant le montant réceptionné et le montant à payer, dans le tableau du formulaire et le tableau « Résumé des BC » du PDF : montant ÷ montant total de la commande dans IPS (`frn_cde.fcde_mtn`, fournisseur + commande) × 100. Omis si ce total est inconnu ou nul | ✔ | ✔ |
| RG-AA-16 | % en rouge dans les libellés « Montant total réceptionné » et « Montant total à payer », et devant le montant de la case « Montant à payer » du PDF : somme du tableau ÷ somme des montants totaux connus des commandes × 100 | ✔ | ✔ |

**« Validée »** signifie toujours : statut `Validé` ou `Transmis compta`, sur la dernière version de la demande.

### Les montants du formulaire

Six montants coexistent et peuvent différer :

| Montant | Origine | Stockage | Usage |
|---|---|---|---|
| Montant du commande réceptionné | Lu dans IPS, non modifiable | `montant_commande_receptionner` (`demande_paiement_ligne`, `demande_paiement_commande`, `commande_livraison`) | Contrôle RG-AA-04, solde avance, tableau « Résumé des BC » du PDF |
| Montant total réceptionné | Saisie | Non stocké | Contrôle RG-AA-04 uniquement |
| Montant total de la commande | Lu dans IPS (`frn_cde.fcde_mtn`, fournisseur + commande), non affiché | `montant_total_commande` (`demande_paiement_ligne`, `demande_paiement_commande`) | Base des % (RG-AA-15, RG-AA-16) |
| Montant du commande à payer | Calculé par le serveur à la soumission, aperçu à l'écran (RG-AA-13) | `montant_commande_a_payer` (mêmes tables) | Tableau « Résumé des BC » du PDF |
| Montant total à payer | Somme des montants du commande à payer, aperçu à l'écran | `demande_paiement.montant_a_payer` | Montant de la demande, « Montant à payer » du PDF |
| Solde avance restant | Calculé à l'écran et à la génération du PDF (RG-AA-14) | Non stocké | Écran, tableau « Résumé des BC » du PDF |

## 5. Statuts

| Statut | Écrit par |
|---|---|
| `DDPL à transmettre` | Soumission du formulaire |
| `Soumis à validation` | Transmission (`DdpApiController::transmettreBap`) |
| `Validé`, `Transmis compta`, `Refusé`, `Refusé Compta`, `Réfusé par Chef de Service`, `A valider comptabilité`, `BAP à valider par Dir Admin` | Une **moulinette** externe pour le passage à `Validé` ou refusé. Aucun code de `src/` n'écrit ces statuts, ils y sont seulement lus |

Les statuts `Refusé`, `Refusé Compta` et `Réfusé par Chef de Service` sont exclus de l'historique d'une commande (`getDdpSelonNumCde`).

## 6. Données

### Tables de l'intranet

| Table | Contenu | Particularités |
|---|---|---|
| `demande_paiement` | Une ligne par demande | `numero_commande` et `numero_facture` sont des listes séparées par `;`. `montant_total_commande` reste vide : le total est enregistré par commande dans `demande_paiement_ligne` et `demande_paiement_commande` |
| `demande_paiement_ligne` | Une ligne par couple commande/facture | `montant_facture` contient le total de la demande (voir P-AA-03). `montant_total_commande` = total IPS de la commande |
| `demande_paiement_commande` | Une ligne par commande | `client = 'magasin'`, `montant_total_commande`, `montant_commande_receptionner`, `montant_commande_a_payer`, `solde_avance`. Permet de retrouver une commande dans une demande multi-commandes |
| `commande_livraison` | Une ligne par commande et livraison | `numero_facture` regroupe les factures de la demande, séparées par `;` |
| `document_demande_paiement` | Une ligne par fichier joint | Ne contient pas le PDF final |
| `type_demande` | Types de demande | Référentiel |
| `dw_commande` | Chemins des PDF de commande DocuWare | Lecture seule ici |

### Systèmes externes

| Système | Accès via | Usage |
|---|---|---|
| IPS (Informix) | `frn_llf`, `frn_liv`, `frn_cde`, `frn_fac`, `fou_bqe`, `agr_tab`, `agr_dev` | Réceptions, montants, RIB, modes de paiement, devises |
| IPS (`regix.trs_ecr`) | `getFactureNonReglee` | Pièces à exclure de la liste des factures |
| GCOT (SQL Server) | `TRZT_Dossier_Douane`, `TRZT_Facture`, `GCOT_Facture`, `GCOT_Facture_Ligne`, `GCOT_Gestion_Document` | Factures, dossiers de douane, documents |
| Partage `\\192.168.0.15\GCOT_DATA\TRANSIT` | Système de fichiers | Fichiers PDV, LTA, HAWB |
| DocuWare | Dossier de dépôt (`basePathDocuWare`) | Dépôt des PDF |

## 7. Fichiers et DocuWare

**Dossier de travail** : `{BASE_PATH_FICHIER}/ddp/{numéro}/`.

**Page de garde** : page DDP, avec le montant à payer précédé de son % (RG-AA-16), suivie des tableaux « Résumé des BC » (montants précédés de leur %, RG-AA-15), « Récapitulatif des livraisons » (une ligne par livraison, montants des commandes additionnés) et « Récapitulatif des demandes de paiement déjà enregistrées » (`DdpController::donneesPageDeGarde`, lu avant l'enregistrement de la nouvelle demande).

**PDF final** : `{numéro}.pdf`, composé dans cet ordre de la page de garde, des fichiers du dossier de douane, des fichiers téléversés et des PDF de commande DocuWare. Un fichier absent est ignoré et tracé dans `error_log`.

| Champ | Contenu | Nom enregistré |
|---|---|---|
| `pieceJoint01` | Contrôle livraison | `PROFORMA_{numéro}.pdf` (voir P-AA-16) |
| `pieceJoint02` | RIB fournisseur | `RIB_{numéro}.pdf` |
| `pieceJoint03` | BC client (champ désactivé) | `BC_{numéro}_{index}.pdf` |
| `pieceJoint04` | Autres documents | `AUTRES FICHIERS_{numéro}.pdf` |

**Dépôt DocuWare** : uniquement à la transmission, comme le flux appro, dans `DEMANDE_DE_PAIEMENT/{numéro}#{CLA}.pdf`. La soumission ne dépose rien.

## 8. API du formulaire

| Route | Rôle |
|---|---|
| `GET /api/info-fournisseur-ddp` | Fournisseurs (autocomplétion) |
| `GET /api/num-cde-frn/{fournisseur}/2` | Commandes réceptionnées et liste GCOT |
| `GET /api/situation-avance-commande/{numCde}` | Solde avance et situation de la commande pour le tableau (`typeDdpAGenerer` désigne la situation, plus le document créé) |
| `GET /api/montant-commande-receptionnee/{fournisseur}/{cdes}/{factures}` | Montants réceptionnés affichés dans le tableau |
| `GET /api/montant-total-commande/{fournisseur}/{numCde}` | Montant total de la commande, base des % |
| `GET /api/montant-facture/{fournisseur}/{factures}/2` | Total FOB des factures |
| `GET /api/liste-doc/{dossier}`, `/api/recuperer-fichier` | Documents du dossier de douane |
| `GET /api/fichiers-commande-fournisseur/{cdes}`, `/api/fichiers-or-valide/{cdes}`, `/api/fichiers-bc-client/{cdes}`, `/api/telecharger-fichier-commande-dw` | Aperçu des PDF DocuWare |
| `POST /api/transmettre-bap-compta` | Transmission en validation (écran de consultation) |

Les requêtes IPS et GCOT peuvent durer plusieurs dizaines de secondes pour un gros fournisseur. C'est pourquoi l'API libère le verrou de session (`libererSession()`) et le front met en cache `num-cde-frn`.

## 9. Déploiement et configuration

**Scripts SQL**, dans [sql/COMPTA/ddp/](../../../sql/COMPTA/ddp/), à passer pour tout le module, car après arrivage et DDPA partagent les mêmes entités :

- `alter_demande_paiement_numero_commande.sql` : passe `numero_commande` en `NVARCHAR(MAX)` ;
- `demande_paiement_ajout_montant_total_commande.sql` : ajoute `montant_total_commande` et supprime `montant_facture_proforma` ;
- `ddp_ajout_montant_commande_a_payer.sql` : ajoute ou renomme `montant_commande_a_payer` ;
- `ddp_ajout_montant_total_commande.sql` (après le précédent) : ajoute `montant_total_commande` à `demande_paiement_ligne` et `demande_paiement_commande` ;
- `ddp_ajout_montant_commande_receptionner.sql` : ajoute `montant_commande_receptionner`, y copie les montants existants et recalcule `montant_commande_a_payer` ;
- `demande_paiement_commande_ajout_solde_avance.sql` : ajoute `solde_avance`.

**Prérequis** :

- applications `DDP` et `CLA` présentes dans la table des applications (compteurs de numéros) ;
- type 2 présent dans `type_demande` ;
- variable d'environnement `BASE_PATH_FICHIER` ;
- accès du serveur web à `\\192.168.0.15\GCOT_DATA\TRANSIT` et au dossier DocuWare.

**Accès** : `SecurityService` contrôle la permission `VOIR` par route pour les pages. Pour les routes API, il exige seulement une session ou un token JWT valide.

## 10. Points à arbitrer

Constats issus de la lecture du code ; aucun n'a été reproduit en exécution. La colonne « Décision » reste à compléter.

### Risque de paiement

| Réf. | Constat | Conséquence concrète | Décision |
|---|---|---|---|
| P-AA-01 | La situation (RG-AA-05) ne compte que les DDPA **validées** ; le guide utilisateur annonce ce comportement | Une commande avec une DDPA en cours n'a aucune déduction de l'acompte | |
| P-AA-02 | Le montant de la demande est le total à payer : il vaut 0 quand le solde avance couvre tout le réceptionné | Solde 500 et réceptionné 300 : une demande à 0 est créée et transmise en validation | Une demande à 0 passe toujours en validation |
| P-AA-20 | Le solde avance additionne tout le réceptionné validé (RG-AA-08) et pouvait devenir négatif | Solde 500 et réceptionné 800 : 300 à payer, puis solde à -300 une fois validé. Une réception de 100 aurait donné 400 à payer au lieu de 100 | Solde ramené à 0 |
| P-AA-21 | Une commande payée à 100 % a un solde avance forcé à 0, donc son montant à payer égalait tout le réceptionné | Commande de 1 000 déjà payée à 100 % par acomptes : elle aurait été demandée à 1 000 | Montant à payer de 0 (`DdpDto::$commandesRegule`) |
| P-AA-22 | Le magasin ne crée plus que des DDPL : tout part dans `DEMANDE_DE_PAIEMENT`, plus rien dans `BON A PAYER` ni `DEMANDE_DE_REGULARISATION` | Le circuit de validation DocuWare et la moulinette reçoivent un seul type de document : à confirmer avec leurs responsables, notamment pour le statut `BAP à valider par Dir Admin` | |
| P-AA-23 | Le montant réceptionné est cherché avec le numéro de facture GCOT. Si ce numéro diffère dans IPS, la commande n'a aucun montant. Cas vu : `PDV_2311825` dans GCOT, référence `23111825` sur la livraison 26437759 dans IPS (cause probable, à confirmer) | La commande 26249166 reste sans montant, et la demande ne peut pas être enregistrée tant que le numéro n'est pas corrigé | Enregistrement bloqué, pas de montant compté à 0 |
| P-AA-03 | `DdpLigneMapper` met le total de la demande dans `montant_facture` de chaque ligne | Une demande de 3 lignes à 900 donne 2 700 si on additionne la colonne | |
| P-AA-04 | Le serveur accepte les factures postées (`choices` = valeurs soumises) ; RG-AA-01 et RG-AA-09 ne sont appliquées que côté front | Une requête forgée peut soumettre une facture exclue de la liste ou une commande sans PDF | |
| P-AA-05 | Le DTO garde la société `HF` à la soumission, alors que l'API utilise la société de l'utilisateur | Pour un utilisateur d'une autre société, le solde affiché peut différer de celui enregistré | |
| P-AA-06 | Les filtres de la consultation comparent `numero_commande` et `numero_facture` à une valeur exacte, alors que ces champs peuvent contenir `a;b` | Recherche par commande ou facture sans résultat pour une demande multiple | |

### Robustesse

| Réf. | Constat | Conséquence concrète | Décision |
|---|---|---|---|
| P-AA-07 | Aucune transaction : plusieurs `flush`, puis génération des fichiers | Une erreur sur le PDF laisse une demande enregistrée sans son PDF. Une nouvelle saisie peut créer un doublon | |
| P-AA-08 | Le double dépôt DocuWare (soumission et transmission) a été retiré | Les demandes créées avant cette modification et encore « à transmettre » ont déjà un `{numéro}.pdf` dans DocuWare, et en auront un second à la transmission | Dépôt à la transmission seulement, comme l'appro |
| P-AA-09 | La transmission ne revérifie pas le statut côté serveur, et une erreur en cours de boucle n'annule pas les copies déjà faites | Une demande déjà traitée peut être repassée à `Soumis à validation` ; des fichiers restent dans DocuWare sans changement de statut | |
| P-AA-10 | `Validé` et les refus sont posés par une moulinette externe, absente du dépôt. `DdpController` et la transmission n'alimentent pas `historique_statut_ddp` | RG-AA-05 et RG-AA-08 dépendent du passage de la moulinette : tant qu'elle n'a pas tourné, une DDPA déjà validée compte comme non validée (voir P-AA-01). L'historique des statuts reste vide pour la soumission et la transmission | |

### Sécurité

| Réf. | Constat | Conséquence concrète | Décision |
|---|---|---|---|
| P-AA-11 | `numeroFournisseur` (champ texte et paramètre d'URL) et `numCde` sont concaténés dans le SQL IPS et GCOT (`DdpModel`, `DemandePaiementModel`) | Injection SQL possible | |
| P-AA-12 | Les routes API ne vérifient que la connexion, y compris `transmettre-bap-compta` | Tout utilisateur connecté peut transmettre une demande (préfixe `api_` dans `SecurityService`) | |
| P-AA-13 | `InfoFournisseurApi` active `display_errors` | Détails techniques affichés dans les réponses en cas d'erreur | |

### Écarts et dette

| Réf. | Constat | Décision |
|---|---|---|
| P-AA-14 | Guide utilisateur réaligné sur le code et séparé en deux pages par `sql/documentation_utilisateur_separation_ddp_magasin.sql` ; l'ancienne page `ddp-magasin` repasse en brouillon | Script à exécuter sur chaque base |
| P-AA-15 | `getFactureNonReglee` porte un nom trompeur : la requête ne filtre pas sur le lettrage, et son résultat sert à **exclure** des factures. Confirmer l'intention | |
| P-AA-16 | `pieceJoint01` s'appelle `PROFORMA_…` alors que c'est un contrôle livraison | |
| P-AA-17 | Le contrôle livraison porte un `*` à l'écran mais n'est pas obligatoire côté serveur. La zone de dépôt PJ03 reste affichée alors que le champ est désactivé | |
| P-AA-18 | `DemandePaiementManager.js` : le `catch` de `chargerFichiersBcClient` utilise des variables non définies (`bloquerSiIntrouvable`, `fichier`) ; `afficherPdfCommandeValidee` ignore son paramètre `bloquerSiIntrouvable` | |
| P-AA-19 | Aucun test automatisé trouvé pour ce module (recherche par nom de fichier dans `tests/`) | |

## 11. Hors périmètre

Ces éléments utilisent en partie les mêmes tables, mais ne sont pas décrits ici : paiement à l'avance (fiche liée), demande de paiement DA/appro (`DemandePaiementDaController`), `DemandePaiementController`, modification (`EditDemandePaiementController`), dossier de régularisation (`DdpDossierRegularisationController`), liste compta (`DdpListeController`).
