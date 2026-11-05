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
| 3 | `DPR` | `ID_DEMANDE_PAIEMENT_REGULE` | **DDPR** : montant total à payer à 0 | DDP |
| 4 | `BAP` | `ID_BON_A_PAYER` | **BAP** : somme des soldes avance à 0 | DDP |

Le type se décide automatiquement à l'enregistrement (RG-AA-06). L'ancienne page de garde BAP MAGASIN n'existe plus dans le code : tous ces types utilisent la page de garde DDP, et le numéro reste un numéro DDP.

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

1. L'utilisateur choisit un fournisseur. Le front charge ses factures GCOT liées à des commandes réceptionnées dans IPS, dont toutes les commandes sont validées dans DocuWare (RG-AA-20), sans celles qui ont déjà une demande non refusée (RG-AA-19).
2. Il choisit une ou plusieurs factures. Les commandes correspondantes s'affichent dans un tableau, avec leur solde avance, le montant commande réceptionnée lu dans IPS (non modifiable) et le montant commande à payer calculé (RG-AA-13). Ces deux montants sont précédés de leur % du montant total de la commande (RG-AA-15). L'historique des demandes de paiement de chaque commande s'affiche dans l'onglet « Historique » (RG-AA-17).
3. Il saisit le montant total réceptionné, qui doit égaler la somme des montants réceptionnés du tableau. Le champ « Montant total à payer » affiche la somme des montants à payer et reste modifiable (RG-AA-21). Les deux libellés portent leur % (RG-AA-16).
4. Le serveur **redéduit les commandes depuis les factures**, car le select est désactivé et n'est pas posté, puis **relit dans IPS le montant réceptionné de chaque commande** (RG-AA-03).
5. Le serveur lit le montant total de chaque commande dans IPS, calcule son solde avance et son montant à payer (RG-AA-05, RG-AA-08, RG-AA-13), puis le montant de la demande (RG-AA-07).
6. Il génère un numéro `DDP`, enregistre les données, puis génère et fusionne le PDF.
7. La demande a le statut `DDPL à transmettre`, `DDPR à transmettre` ou `BAP à transmettre` selon son type (RG-AA-06), et l'utilisateur est redirigé vers la consultation.

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
| Enregistrement | `DDP` | Numéro `DDP` | Soldes et montants des commandes ; Factures et livraisons ; Numéro et fichiers joints ; Données de la page de garde ; Base de données ; Page de garde ; Fusion |
| Transmission | `DDP_TRANSMISSION` | Numéro `CLA` | Numéro CLA ; une étape « Transmission {numéro} » par demande, avec la copie DocuWare ; Base de données |

Une copie DocuWare échouée ne bloque pas la transmission (P-AA-09), mais passe son étape en échec dans ce suivi.

## 4. Règles de gestion

« Front » et « Serveur » indiquent où la règle est réellement appliquée. Une règle appliquée uniquement côté front peut être contournée par une requête forgée.

| Réf. | Règle | Front | Serveur |
|---|---|---|---|
| RG-AA-01 | Factures proposées = factures GCOT du fournisseur, sur commandes réceptionnées et validées dans DocuWare (RG-AA-20), hors pièces `regix.trs_ecr` du journal `Achmag` | ✔ | ✘ |
| RG-AA-02 | Commandes déduites des factures | ✔ | ✔ |
| RG-AA-03 | Montant commande réceptionnée lu dans IPS pour les factures sélectionnées, non modifiable. Le serveur relit IPS et ignore toute valeur postée. Introuvable pour une commande → enregistrement refusé | ✔ | ✔ |
| RG-AA-04 | Montant total réceptionné = somme des montants commande réceptionnée (± 0,01) | ✔ | ✔ |
| RG-AA-05 | Situation de chaque commande selon le cumul des % DDPA validées : ≤ 0,01 → aucun acompte ; ≥ 99,99 → payée à 100 % ; sinon → payée en partie. Cette situation ne détermine que les montants | ✔ (aperçu) | ✔ |
| RG-AA-06 | Une saisie crée une seule demande. Son type se décide à l'enregistrement : montant total à payer à 0 → DDPR (type 3, prioritaire) ; sinon somme des soldes avance à 0 → BAP (type 4) ; sinon DDPL (type 2). Le statut « à transmettre » et le dossier DocuWare suivent le type | – | ✔ |
| RG-AA-07 | `montant_a_payer` de la demande = somme des montants commande à payer de ses commandes | – | ✔ |
| RG-AA-08 | Solde avance (0 sans acompte) = somme des proformas DDPA − `montant_commande_receptionner` des demandes validées non DDPA, ramené à 0 s'il est négatif. Une réception non validée ne réduit pas le solde | ✔ (aperçu) | ✔ |
| RG-AA-09 | PDF de commande DocuWare introuvable → bouton Enregistrer désactivé | ✔ | ✘ |
| RG-AA-10 | Pièces jointes : PDF, 5 Mo maximum, sans obligation de présence côté serveur ; PJ03 désactivée | ✔ | ✔ |
| RG-AA-11 | RIB différent de ceux connus dans IPS (`fou_bqe`) → mention « RIB mis à jour » sur la page de garde | – | ✔ |
| RG-AA-12 | Valeurs fixes : agence `01`, service `NEG`, société `HF` | – | ✔ |
| RG-AA-13 | Montant commande à payer : 0 pour une commande payée à 100 %, ou si solde avance > 0 et montant réceptionné ≤ solde avance ; sinon montant réceptionné − solde avance | ✔ (aperçu) | ✔ |
| RG-AA-14 | Solde avance restant (imprimé sur le PDF, non enregistré) : solde avance − montant réceptionné si solde avance > 0 et montant réceptionné ≤ solde avance ; sinon 0. À l'écran, cette colonne est remplacée par le montant total du commande lu dans IPS (« - » s'il est introuvable) | ✔ | ✔ |
| RG-AA-15 | % en rouge et entre parenthèses, devant le montant réceptionné et le montant à payer, dans le tableau du formulaire et le tableau « Résumé des BC » du PDF : montant ÷ montant total de la commande dans IPS (`frn_cde.fcde_mtn`, fournisseur + commande) × 100. Omis si ce total est inconnu ou nul | ✔ | ✔ |
| RG-AA-16 | % en rouge dans les libellés « Montant total réceptionné » et « Montant total à payer », et devant le montant de la case « Montant à payer » du PDF : somme du tableau ÷ somme des montants totaux connus des commandes × 100 | ✔ | ✔ |
| RG-AA-17 | Onglet « Historique », ouvert par défaut, avant « commande fournisseur validée », avec un sous-onglet par commande s'il y en a plusieurs et le montant total de la commande dans le titre : demandes hors refusées avec type DDP, numéro, n° facture (de la commande, ou n° proforma pour une DDPA), date, motif, solde avance enregistré à la soumission, montant réceptionné, montant à payer, % et % cumul propres à la commande, statut | ✔ | – |
| RG-AA-18 | Commande payée à 100 % : montant réceptionné supérieur au solde avance (écart > 0,01) → enregistrement refusé | ✔ | ✔ |
| RG-AA-19 | Une facture ne se paie qu'une fois : une facture ayant une demande non refusée (dernière version, même fournisseur et société, en cours ou validée) n'est plus proposée, et son enregistrement est refusé. Elle redevient disponible si sa demande est refusée | ✔ | ✔ |
| RG-AA-20 | Toutes les commandes d'une facture doivent être validées dans DocuWare (dernière version au statut « Validée »), y compris celles qui ne sont pas réceptionnées : sinon la facture n'est pas proposée et son enregistrement est refusé | ✔ | ✔ |
| RG-AA-21 | « Montant total à payer » est modifiable. Un montant saisi (nombre positif, 0 accepté) devient le montant de la demande ; sans saisie, le serveur garde son calcul. Le champ vidé, ou un changement de commandes, revient au calcul. Les montants commande à payer ne changent pas | ✔ | ✔ |

**« Validée »** signifie toujours : statut `Validé` ou `Transmis compta`, sur la dernière version de la demande.

### Les montants du formulaire

Six montants coexistent et peuvent différer :

| Montant | Origine | Stockage | Usage |
|---|---|---|---|
| Montant commande réceptionnée | Lu dans IPS, non modifiable | `montant_commande_receptionner` (`demande_paiement_ligne`, `demande_paiement_commande`, `commande_livraison`) | Contrôle RG-AA-04, solde avance, tableau « Résumé des BC » du PDF |
| Montant total réceptionné | Saisie | Non stocké | Contrôle RG-AA-04 uniquement |
| Montant total de la commande | Lu dans IPS (`frn_cde.fcde_mtn`, fournisseur + commande), non affiché | `montant_total_commande` (`demande_paiement_ligne`, `demande_paiement_commande`) | Base des % (RG-AA-15, RG-AA-16) |
| Montant commande à payer | Calculé par le serveur à la soumission, aperçu à l'écran (RG-AA-13) | `montant_commande_a_payer` (mêmes tables) | Tableau « Résumé des BC » du PDF |
| Montant total à payer | Somme des montants commande à payer, aperçu à l'écran, ou montant saisi (RG-AA-21) | `demande_paiement.montant_a_payer` | Montant de la demande, « Montant à payer » du PDF |
| Solde avance restant | Calculé à la génération du PDF (RG-AA-14) | Non stocké | Tableau « Résumé des BC » du PDF |

## 5. Statuts

| Statut | Écrit par |
|---|---|
| `DDPL à transmettre`, `DDPR à transmettre`, `BAP à transmettre` | Soumission du formulaire, selon le type (RG-AA-06) |
| `Soumis à validation` | Transmission (`DdpApiController::transmettreBap`) |
| `Validé`, `Transmis compta`, `Refusé`, `Refusé Compta`, `Réfusé par Chef de Service`, `A valider comptabilité`, `BAP à valider par Dir Admin` | Une **moulinette** externe pour le passage à `Validé` ou refusé. Aucun code de `src/` n'écrit ces statuts, ils y sont seulement lus |

Les statuts `Refusé`, `Refusé Compta` et `Réfusé par Chef de Service` sont exclus de l'historique d'une commande (`getDdpSelonNumCde`), sauf sur la page de garde (section 7).

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

**Page de garde** : page DDP, avec le montant à payer précédé de son % (RG-AA-16), suivie des tableaux « Résumé des BC » (montants précédés de leur %, RG-AA-15), « Récapitulatif des livraisons » (une ligne par livraison, montants des commandes additionnés) et « Récapitulatif des demandes de paiement déjà enregistrées » (`DdpController::donneesPageDeGarde`, lu avant l'enregistrement de la nouvelle demande). Ce dernier tableau affiche la dernière version de chaque demande, y compris les refusées (`getDdpSelonNumCde` avec `$avecRefusees`, réservé à l'affichage car une demande refusée n'est pas payée).

**PDF final** : `{numéro}.pdf`, composé dans cet ordre de la page de garde, des fichiers du dossier de douane, des fichiers téléversés et des PDF de commande DocuWare. Un fichier absent est ignoré et tracé dans `error_log`.

| Champ | Contenu | Nom enregistré |
|---|---|---|
| `pieceJoint01` | Contrôle livraison | `PROFORMA_{numéro}.pdf` (voir P-AA-16) |
| `pieceJoint02` | RIB fournisseur | `RIB_{numéro}.pdf` |
| `pieceJoint03` | BC client (champ désactivé) | `BC_{numéro}_{index}.pdf` |
| `pieceJoint04` | Autres documents | `AUTRES FICHIERS_{numéro}.pdf` |

**Dépôt DocuWare** : uniquement à la transmission, comme le flux appro, sous `{numéro}#{CLA}.pdf`, dans `DEMANDE_DE_PAIEMENT`, `DEMANDE_DE_REGULARISATION` (DDPR) ou `BON A PAYER` (BAP). La soumission ne dépose rien.

## 8. API du formulaire

| Route | Rôle |
|---|---|
| `GET /api/info-fournisseur-ddp` | Fournisseurs (autocomplétion), une ligne par fournisseur |
| `GET /api/num-cde-frn/{fournisseur}/2` | Commandes réceptionnées et validées dans DocuWare, et liste GCOT. Avec `?flux=da` (flux DA) : commandes réceptionnées, sans RG-AA-20 |
| `GET /api/situation-avance-commande/{numCde}` | Solde avance et situation de la commande pour le tableau (`typeDdpAGenerer` désigne la situation, plus le document créé) |
| `GET /api/montant-commande-ht/{numCde}` | Montant HT et historique des demandes de la commande |
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
| P-AA-21 | Une commande payée à 100 % avait un solde avance forcé à 0, qui masquait l'avance à consommer | Commande de 22 721,88 payée à 100 % : solde affiché à 0 au lieu de 22 721,88 | Solde réel consommé réception par réception (RG-AA-08) ; montant à payer de 0 (`DdpDto::$commandesRegule`) ; réception supérieure au solde bloquée (RG-AA-18) |
| P-AA-24 | Seules les réceptions des demandes **validées** réduisent le solde (RG-AA-08) | Tant que la première demande n'est pas validée, une deuxième réception voit le solde entier : solde 22 721,88 et non 22 580,22. Le contrôle RG-AA-18 ne voit pas non plus les réceptions en attente. Deux demandes en attente restent possibles sur des factures différentes d'une même commande, pas sur la même facture (RG-AA-19) | Réceptions validées uniquement |
| P-AA-26 | Une facture déjà payée restait proposée et pouvait être soumise de nouveau | `PDV_10400386565` validée par DDP26110007 (1 374,66) : solde ramené de 21 347,22 à 19 972,56 pour la même réception | Facture retirée de la liste et refusée côté serveur tant que sa demande n'est pas refusée (RG-AA-19). Pas de contrainte en base : deux soumissions simultanées restent possibles |
| P-AA-27 | RG-AA-20 masque des factures tant qu'une seule de leurs commandes n'est pas validée, y compris des factures anciennes non réglées. Le flux DA n'est pas concerné (`?flux=da`) ; l'ancien écran et la modification utilisent la même liste filtrée | Une commande en attente bloque le paiement de toute sa facture. Seul `new_ddp` refuse l'enregistrement : `?flux=da` ne change que la liste proposée | Décision du 05/11/2026 : masquer la facture entière plutôt que de la payer en partie, ce qui rendrait le reste impayable (RG-AA-19) |
| P-AA-28 | Le montant total à payer saisi (RG-AA-21) n'est comparé à rien : ni au réceptionné, ni au solde avance, ni au calcul, et il n'est pas réparti entre les commandes | Une demande peut payer plus ou moins que le calcul, même sur une commande déjà payée à 100 %. Le PDF et l'historique peuvent alors montrer un total différent de la somme des montants commande à payer. Saisir 0 rend aussi la demande DDPR (RG-AA-06) | Décision du 05/11/2026 : saisie libre, sans plafond ni répartition |
| P-AA-25 | Pour une commande payée en partie, le solde valait le reste non payé de la commande, pas l'avance versée | Total 1 000, acompte 300, réception 800 : solde 700 et 100 à payer au lieu de 500 | Même formule pour toutes les commandes avec acompte (RG-AA-08) |
| P-AA-22 | Le magasin crée de nouveau des BAP et des DDPR (RG-AA-06), déposés dans `BON A PAYER` et `DEMANDE_DE_REGULARISATION` avec un numéro DDP et la page de garde DDP. Toute commande sans acompte validé donne un BAP | Le circuit de validation DocuWare et la moulinette doivent accepter ces documents, notamment pour le statut `BAP à valider par Dir Admin` : à confirmer avec leurs responsables avant la mise en production | Décision du 05/11/2026, remplace le « DDPL uniquement » du 02/11/2026 |
| P-AA-23 | Le montant réceptionné est cherché avec le numéro de facture GCOT sans `PDV_`, comparé exactement à `frn_fac.ffac_facext`. Une facture écrite autrement dans IPS ne donne aucun montant. Cas vérifié : `PDV_11916226B` (788,25) dans GCOT correspond à ` 11916226` dans IPS (facture 26732026, espace devant, sans `B`), avec les mêmes 5 commandes et le même total. Autre cas, non expliqué : `PDV_2311825` lie la commande 26249166 dans GCOT, mais `2311825` ne la contient pas dans IPS | Toutes les commandes de la facture restent sans montant, et la demande ne peut pas être enregistrée | Enregistrement bloqué, pas de montant compté à 0. Données non corrigées. **Adaptation du code reportée** : si le numéro exact est introuvable, chercher dans IPS sans la lettre finale et sans tenir compte des espaces, et n'accepter le résultat que si son total égale le montant GCOT au centime près. Sans ce contrôle, deux factures peuvent être mélangées : `11916226` et ` 11916226` sont deux factures différentes dans IPS. Correspondance vérifiée sur cette seule facture, à confirmer avec la personne qui saisit les factures dans IPS |
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
| P-AA-11 | `numeroFournisseur`, `numCde` et les numéros de facture sont concaténés dans le SQL IPS et GCOT (`DdpModel`, `DemandePaiementModel`) | Injection SQL possible | Corrigé le 05/11/2026 : `TableauEnString` échappe les apostrophes ; fournisseur limité aux chiffres (formulaires `DdpType`, `DemandePaiementType`, `DemandePaiementDaType`, routes `InfoFournisseurApi`) ; commande limitée aux chiffres sur `new_ddp` ; `findListeDoc` échappé. La requête reste concaténée, sans paramètres liés. Les commandes postées sur les écrans DA et ancien écran ne sont pas revues |
| P-AA-12 | Les routes API ne vérifient que la connexion, y compris `transmettre-bap-compta` | Tout utilisateur connecté peut transmettre une demande (préfixe `api_` dans `SecurityService`) | |
| P-AA-13 | `InfoFournisseurApi` active `display_errors` | Détails techniques affichés dans les réponses en cas d'erreur | |

### Écarts et dette

| Réf. | Constat | Décision |
|---|---|---|
| P-AA-14 | Guide utilisateur réaligné sur le code et séparé en deux pages par `sql/documentation_utilisateur_separation_ddp_magasin.sql` ; l'ancienne page `ddp-magasin` repasse en brouillon. Le script crée les pages absentes, puis met à jour les pages existantes par `REPLACE` | Script à exécuter sur chaque base, sans risque s'il est relancé |
| P-AA-15 | `getFactureNonReglee` porte un nom trompeur : la requête ne filtre pas sur le lettrage, et son résultat sert à **exclure** des factures. Confirmer l'intention | |
| P-AA-16 | `pieceJoint01` s'appelle `PROFORMA_…` alors que c'est un contrôle livraison | |
| P-AA-17 | Le contrôle livraison porte un `*` à l'écran mais n'est pas obligatoire côté serveur. La zone de dépôt PJ03 reste affichée alors que le champ est désactivé | |
| P-AA-18 | `DemandePaiementManager.js` : le `catch` de `chargerFichiersBcClient` utilise des variables non définies (`bloquerSiIntrouvable`, `fichier`) ; `afficherPdfCommandeValidee` ignore son paramètre `bloquerSiIntrouvable` | |
| P-AA-19 | Aucun test automatisé trouvé pour ce module (recherche par nom de fichier dans `tests/`) | |

## 11. Hors périmètre

Ces éléments utilisent en partie les mêmes tables, mais ne sont pas décrits ici : paiement à l'avance (fiche liée), demande de paiement DA/appro (`DemandePaiementDaController`), `DemandePaiementController`, modification (`EditDemandePaiementController`), dossier de régularisation (`DdpDossierRegularisationController`), liste compta (`DdpListeController`).

## 12. Historique de suivi

Évolutions du module et décisions. À compléter à chaque changement.

| Date | Commit | Changement ou décision | Réf. |
|---|---|---|---|
| 02/11/2026 | `691972555` | Création de cette fiche | |
| 02/11/2026 | `f0c870b84` | Colonnes « DDPA » et « DDP à générer » retirées du tableau du formulaire | |
| 02/11/2026 | `1ac4076cb` | Montant réceptionné séparé du montant à payer (colonne `montant_commande_receptionner`), solde avance restant, montant total à payer, solde ramené à 0, montant à payer de 0 pour une commande payée à 100 % | RG-AA-13, RG-AA-14, P-AA-20, P-AA-21 |
| 02/11/2026 | `f97e61542` | Une saisie crée une seule demande DDPL et une seule page de garde ; page BAP MAGASIN supprimée. PDF : soldes dans « Résumé des BC », une ligne par livraison, unités dans l'entête | RG-AA-06, P-AA-22 |
| 02/11/2026 | `8f7a527aa` | Transmission : numéros de demande rétablis sur les cases à cocher ; une transmission vide renvoie une erreur et ne consomme plus de numéro `CLA` | section 3.2 |
| 02/11/2026 | `2663a8df0` | Colonne `montant_total_commande` ajoutée à `demande_paiement_ligne` et `demande_paiement_commande` | section 9 |
| 02/11/2026 | `30f4c35c9` | Montant réceptionné lu dans IPS, non modifiable, enregistrement bloqué s'il est introuvable ; % en rouge sur le formulaire et le PDF | RG-AA-03, RG-AA-15, RG-AA-16 |
| 02/11/2026 | à commiter | Total à payer et % laissés vides quand aucun montant réceptionné n'est connu | |
| 02/11/2026 | à commiter | Suivi étape par étape ajouté à l'enregistrement et à la transmission | section 3.3 |
| 02/11/2026 | – | **Décision :** factures écrites différemment dans GCOT et IPS : pas de correction des données, adaptation du code reportée, enregistrement bloqué en attendant | P-AA-23 |
| 04/11/2026 | à commiter | Solde avance = proformas DDPA − réceptionné validé, pour toute commande avec acompte ; réception supérieure au solde bloquée à 100 % ; historique des demandes de paiement affiché dans un onglet du formulaire | RG-AA-08, RG-AA-17, RG-AA-18, P-AA-21, P-AA-24, P-AA-25 |
| 04/11/2026 | à commiter | Facture ayant une demande non refusée retirée de la liste et refusée à l'enregistrement ; le script du guide met à jour les pages existantes | RG-AA-19, P-AA-14, P-AA-26 |
| 04/11/2026 | à commiter | Historique : colonne n° facture ajoutée, colonnes n° commande et montant total retirées, montant total affiché dans le titre | RG-AA-17 |
| 04/11/2026 | `c408a4860` | Page de garde : demandes refusées affichées dans le récapitulatif des demandes de paiement, dernière version de chaque demande uniquement ; guide utilisateur mis à jour le 05/11/2026 | section 7 |
| 05/11/2026 | à commiter | Autocomplétion : une seule ligne par fournisseur, un RIB renseigné étant préféré à un RIB vide ; choix arbitraire entre deux RIB renseignés | section API |
| 05/11/2026 | à commiter | **Décision :** facture proposée et enregistrable seulement si toutes ses commandes sont validées dans DocuWare, sauf pour le flux DA ; guide utilisateur mis à jour | RG-AA-01, RG-AA-20, P-AA-27 |
| 05/11/2026 | à commiter | Injection SQL : apostrophes échappées dans `TableauEnString` et `findListeDoc`, numéros de fournisseur et de commande limités aux chiffres | P-AA-11 |
| 05/11/2026 | à commiter | Tableau du formulaire : « Solde avance restant » remplacé par « Montant total du commande » (écran seulement). Colonnes renommées « Montant commande réceptionnée » et « Montant commande à payer » à l'écran et sur le PDF ; guide utilisateur mis à jour | RG-AA-14 |
| 05/11/2026 | `8a6b39606` | Montant total à payer modifiable et enregistré s'il est saisi ; guide utilisateur mis à jour | RG-AA-21, P-AA-28 |
| 05/11/2026 | à commiter | **Décision :** saisie libre du montant total à payer. Type décidé à l'enregistrement : DDPR si le montant à payer est à 0, BAP si la somme des soldes avance est à 0, sinon DDPL ; guide utilisateur mis à jour | RG-AA-06, P-AA-22, P-AA-28 |
