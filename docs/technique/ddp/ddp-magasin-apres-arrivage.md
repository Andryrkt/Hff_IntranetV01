---
title: Demande de paiement magasin – après arrivage
category: Demande de paiement
description: BAP, DDPL et DDPR, paiement sur factures fournisseur après réception, de la saisie à la transmission en validation.
---

| | |
|---|---|
| **Code décrit** | branche `feature/ddp-neg`, commit `2e3069de7` (02/11/2026), plus le retrait du dépôt DocuWare à la soumission, non commité à cette date |
| **État du code** | pas encore sur `main` ; 13 fichiers du module diffèrent aussi de `dev` |
| **Guide utilisateur** | page `ddp-magasin-apres-arrivage` de la table `documentation_utilisateur` |
| **Fiche liée** | Demande de paiement magasin – à l'avance |

> **Source de ce document.** Aucun cahier des charges n'est présent dans le dépôt. Ce document décrit donc **ce que fait le code**, pas ce qui a été spécifié. Les écarts et les décisions à prendre sont regroupés à la section 10.

## 1. Objet

Payer le fournisseur une fois la marchandise arrivée, à partir de ses factures. L'utilisateur remplit un formulaire dans l'intranet. L'application génère et enregistre un PDF. La demande est ensuite transmise en validation depuis l'écran de consultation, et c'est à ce moment que le PDF est déposé dans DocuWare.

**Formulaire et document sont deux notions distinctes.** Ce formulaire ne crée pas forcément un seul document : chaque commande reçoit le type qui correspond à ses acomptes (DDPA) validés.

| ID type | Code en base | Constante | Document | Quand | Page de garde |
|---|---|---|---|---|---|
| 2 | `DPL` | `ID_DEMANDE_PAIEMENT_APRES_ARRIVAGE` | **DDPL** | DDPA validées entre 0 et 100 % | DDP |
| 3 | `DPR` | `ID_DEMANDE_PAIEMENT_REGULE` | **DDPR** | DDPA validées à 100 % | DDP |
| 4 | `BAP` | `ID_BON_A_PAYER` | **BAP** | Aucune DDPA validée | BAP MAGASIN |

L'ID 2 sert à la fois de type de formulaire et de type de document (DDPL).

## 2. Composants

| Rôle | Fichier |
|---|---|
| Contrôleur (soumission, regroupement, fichiers) | [DdpController](../../../src/Controller/ddp/DdpController.php) |
| Initialisation du DTO, numéro, copie des fichiers | [DdpFactory](../../../src/Factory/ddp/DdpFactory.php) |
| Formulaire et DTO | [DdpType](../../../src/Form/ddp/DdpType.php), [DdpDto](../../../src/Dto/ddp/DdpDto.php) |
| Situation d'avance d'une commande | [DemandePaiementRepository](../../../src/Repository/ddp/DemandePaiementRepository.php) `::getSituationAvanceParCommande` |
| Persistance | [Service/ddp/Magasin](../../../src/Service/ddp/Magasin/), [Mapper/ddp/Magasin](../../../src/Mapper/ddp/Magasin/), `DemandePaiementCommandeMapper`, `CommandeLivraisonMapper` |
| Requêtes IPS et GCOT | [DdpModel](../../../src/Model/ddp/DdpModel.php), [DemandePaiementModel](../../../src/Model/ddp/DemandePaiementModel.php) |
| Pages de garde | [GeneratePdfDdp](../../../src/Service/genererPdf/GeneratePdfDdp.php), [GenererPdfBapMagasin](../../../src/Service/genererPdf/bap/GenererPdfBapMagasin.php) |
| API du formulaire | [InfoFournisseurApi](../../../src/Api/ddp/InfoFournisseurApi.php) |
| Consultation et transmission | [BonApayerController](../../../src/Controller/da/ddp/BonApayerController.php), [DdpApiController](../../../src/Api/ddp/DdpApiController.php) |
| Vue et front | [magasin/new.html.twig](../../../Views/templates/ddp/magasin/new.html.twig), [DemandePaiementManager.js](../../../Views/js/ddp/DemandePaiementManager.js) |
| Constantes | [TypeDemandePaiementConstants](../../../src/Constants/ddp/TypeDemandePaiementConstants.php), [StatutConstants](../../../src/Constants/ddp/StatutConstants.php) |

Routes principales : `new_ddp` (`/ddp/new/2`) et `da_bon_a_payer` (`/demande-appro/consultation-facture/0`).

## 3. Flux

### 3.1 Saisie et enregistrement

1. L'utilisateur choisit un fournisseur. Le front charge ses factures GCOT liées à des commandes réceptionnées dans IPS.
2. Il choisit une ou plusieurs factures. Les commandes correspondantes s'affichent dans un tableau, avec leur solde avance et le montant à payer pré-rempli depuis le montant réceptionné IPS (modifiable). Le type de document à créer n'est pas affiché.
3. Il saisit le montant à payer global, qui doit égaler la somme du tableau.
4. Le serveur **redéduit les commandes depuis les factures**, car le select est désactivé et n'est pas posté.
5. `groupesParSituation()` classe chaque commande (RG-AA-05) et rattache chaque facture au groupe de ses commandes (RG-AA-06).
6. Pour **chaque groupe**, le serveur clone le DTO, génère un numéro (`BAP` ou `DDP`), enregistre les données, puis génère et fusionne le PDF. Les fichiers téléversés sont déplacés pour le premier groupe, puis copiés pour les suivants (`copieFichiersSource`).
7. La confirmation liste tous les numéros créés et redirige vers la consultation.

### 3.2 Transmission en validation

Depuis la consultation, l'utilisateur coche des demandes `… à transmettre`, puis le front appelle `POST /api/transmettre-bap-compta` :

1. un numéro `CLA` commun est généré ;
2. chaque demande passe à `Soumis à validation`, avec `numero_cla`, `date_soumission_compta`, `deposer_dw` et `date_depot_dw` renseignés ;
3. le PDF est copié dans DocuWare sous le nom `{numéro}#{CLA}.pdf`.

## 4. Règles de gestion

« Front » et « Serveur » indiquent où la règle est réellement appliquée. Une règle appliquée uniquement côté front peut être contournée par une requête forgée.

| Réf. | Règle | Front | Serveur |
|---|---|---|---|
| RG-AA-01 | Factures proposées = factures GCOT du fournisseur, sur commandes réceptionnées, hors pièces `regix.trs_ecr` du journal `Achmag` | ✔ | ✘ |
| RG-AA-02 | Commandes déduites des factures | ✔ | ✔ |
| RG-AA-03 | Montant du commande à payer obligatoire pour chaque commande | ✔ | ✔ |
| RG-AA-04 | Montant à payer = somme des montants du commande à payer (± 0,01) | ✔ | ✔ |
| RG-AA-05 | Classement selon le cumul des % DDPA validées : ≤ 0,01 → BAP ; ≥ 99,99 → DDPR ; sinon → DDPL | – | ✔ |
| RG-AA-06 | Une facture dont les commandes tombent dans des groupes différents bloque toute la soumission | ✘ | ✔ |
| RG-AA-07 | Une demande par groupe ; son `montant_a_payer` = somme des montants FOB de ses factures | – | ✔ |
| RG-AA-08 | Solde avance (DDPL, sinon 0) = total commande de la dernière DDPA − (somme des proformas DDPA + `montant_commande_a_payer` des demandes validées non DDPA) | ✔ (aperçu) | ✔ |
| RG-AA-09 | PDF de commande DocuWare introuvable → bouton Enregistrer désactivé | ✔ | ✘ |
| RG-AA-10 | Pièces jointes : PDF, 5 Mo maximum, sans obligation de présence côté serveur ; PJ03 désactivée | ✔ | ✔ |
| RG-AA-11 | RIB différent de ceux connus dans IPS (`fou_bqe`) → mention « RIB mis à jour » sur la page DDP (pas sur la page BAP MAGASIN) | – | ✔ |
| RG-AA-12 | Valeurs fixes : agence `01`, service `NEG`, société `HF` | – | ✔ |

**« Validée »** signifie toujours : statut `Validé` ou `Transmis compta`, sur la dernière version de la demande.

### Les trois montants du formulaire

Trois montants coexistent et peuvent différer :

| Montant | Origine | Stockage | Usage |
|---|---|---|---|
| Montant du commande à payer | Pré-rempli depuis IPS, modifiable | `montant_commande_a_payer` (`demande_paiement_ligne`, `demande_paiement_commande`, `commande_livraison`) | Contrôle RG-AA-04, solde avance, tableau du PDF BAP |
| Montant à payer saisi | Saisie | Non stocké | Contrôle RG-AA-04 uniquement |
| Montant des factures | FOB GCOT (`TRZT_Facture.Montant_FOB`) | `demande_paiement.montant_a_payer` | Montant de la demande, « MONTANT BAP » du PDF |

## 5. Statuts

| Statut | Écrit par |
|---|---|
| `BAP à transmettre`, `DDPL à transmettre`, `DDPR à transmettre` | Soumission du formulaire |
| `Soumis à validation` | Transmission (`DdpApiController::transmettreBap`) |
| `Validé`, `Transmis compta`, `Refusé`, `Refusé Compta`, `Réfusé par Chef de Service`, `A valider comptabilité`, `BAP à valider par Dir Admin` | Une **moulinette** externe pour le passage à `Validé` ou refusé. Aucun code de `src/` n'écrit ces statuts, ils y sont seulement lus |

Les statuts `Refusé`, `Refusé Compta` et `Réfusé par Chef de Service` sont exclus de l'historique d'une commande (`getDdpSelonNumCde`).

## 6. Données

### Tables de l'intranet

| Table | Contenu | Particularités |
|---|---|---|
| `demande_paiement` | Une ligne par demande | `numero_commande` et `numero_facture` sont des listes séparées par `;`. `montant_total_commande` reste vide |
| `demande_paiement_ligne` | Une ligne par couple commande/facture | `montant_facture` contient le total de la demande (voir P-AA-03) |
| `demande_paiement_commande` | Une ligne par commande | `client = 'magasin'`, `montant_commande_a_payer`, `solde_avance`. Permet de retrouver une commande dans une demande multi-commandes |
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

**PDF final** : `{numéro}.pdf`, composé dans cet ordre de la page de garde, des fichiers du dossier de douane, des fichiers téléversés et des PDF de commande DocuWare. Un fichier absent est ignoré et tracé dans `error_log`.

| Champ | Contenu | Nom enregistré |
|---|---|---|
| `pieceJoint01` | Contrôle livraison | `PROFORMA_{numéro}.pdf` (voir P-AA-16) |
| `pieceJoint02` | RIB fournisseur | `RIB_{numéro}.pdf` |
| `pieceJoint03` | BC client (champ désactivé) | `BC_{numéro}_{index}.pdf` |
| `pieceJoint04` | Autres documents | `AUTRES FICHIERS_{numéro}.pdf` |

**Dépôt DocuWare** : uniquement à la transmission, comme le flux appro. La soumission ne dépose rien.

| Type | Dossier | Nom |
|---|---|---|
| BAP | `BON A PAYER` | `{numéro}#{CLA}.pdf` |
| DDPR (`DPR`) | `DEMANDE_DE_REGULARISATION` | `{numéro}#{CLA}.pdf` |
| DDPL | `DEMANDE_DE_PAIEMENT` | `{numéro}#{CLA}.pdf` |

## 8. API du formulaire

| Route | Rôle |
|---|---|
| `GET /api/info-fournisseur-ddp` | Fournisseurs (autocomplétion) |
| `GET /api/num-cde-frn/{fournisseur}/2` | Commandes réceptionnées et liste GCOT |
| `GET /api/situation-avance-commande/{numCde}` | Solde avance du tableau (renvoie aussi % DDPA, proformas et document à générer, non affichés) |
| `GET /api/montant-commande-receptionnee/{fournisseur}/{cdes}/{factures}` | Pré-remplissage du tableau |
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
- `demande_paiement_commande_ajout_solde_avance.sql` : ajoute `solde_avance`.

**Prérequis** :

- applications `DDP`, `BAP` et `CLA` présentes dans la table des applications (compteurs de numéros) ;
- types 2 à 4 présents dans `type_demande` ;
- variable d'environnement `BASE_PATH_FICHIER` ;
- accès du serveur web à `\\192.168.0.15\GCOT_DATA\TRANSIT` et au dossier DocuWare.

**Accès** : `SecurityService` contrôle la permission `VOIR` par route pour les pages. Pour les routes API, il exige seulement une session ou un token JWT valide.

## 10. Points à arbitrer

Constats issus de la lecture du code ; aucun n'a été reproduit en exécution. La colonne « Décision » reste à compléter.

### Risque de paiement

| Réf. | Constat | Conséquence concrète | Décision |
|---|---|---|---|
| P-AA-01 | Le classement (RG-AA-05) ne compte que les DDPA **validées** ; le guide utilisateur annonce ce comportement | Une commande avec une DDPA en cours passe en BAP sans déduction de l'acompte | |
| P-AA-02 | Le montant saisi est contrôlé puis remplacé par le total FOB (section 4) | Saisie de 1 000 et factures à 950 : la demande est enregistrée à 950 alors que le tableau du PDF affiche 1 000 | |
| P-AA-03 | `DdpLigneMapper` met le total de la demande dans `montant_facture` de chaque ligne | Une demande de 3 lignes à 900 donne 2 700 si on additionne la colonne | |
| P-AA-04 | Le serveur accepte les factures postées (`choices` = valeurs soumises) ; RG-AA-01 et RG-AA-09 ne sont appliquées que côté front | Une requête forgée peut soumettre une facture exclue de la liste ou une commande sans PDF | |
| P-AA-05 | Le DTO garde la société `HF` à la soumission, alors que l'API utilise la société de l'utilisateur | Pour un utilisateur d'une autre société, le solde affiché peut différer de celui enregistré, et le document créé peut ne pas correspondre à ses acomptes | |
| P-AA-06 | Les filtres de la consultation comparent `numero_commande` et `numero_facture` à une valeur exacte, alors que ces champs peuvent contenir `a;b` | Recherche par commande ou facture sans résultat pour une demande multiple | |

### Robustesse

| Réf. | Constat | Conséquence concrète | Décision |
|---|---|---|---|
| P-AA-07 | Aucune transaction : plusieurs `flush`, puis fichiers, groupe par groupe | Une erreur sur le 2ᵉ groupe laisse le 1ᵉʳ créé. Une nouvelle saisie peut créer un doublon | |
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
