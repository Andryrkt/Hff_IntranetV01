# Algorithme de Détermination du Statut BC

## Fonction principale : `statutBc(DaAfficher)`

### Étape 0 : Initialisation
```
Récupérer l'Entity Manager
```

### Étape 1 : Extraction des données de DaAfficher (version max)
```
Extraire :
  - ref (référence article)
  - numDit (numéro demande DIT)
  - numDa (numéro demande appro)
  - designation (désignation article)
  - numeroOr (numéro OR)
  - statutOr (statut OR)
  - statutBc (statut BC)
  - statutDa (statut DA)
```

### Étape 2 : Vérification des conditions de retour vide
```
SI doitRetournerVide(statutDa, statutOr) ALORS
  RETOURNER ''
FIN SI

Conditions de retour vide :
  - si statutOr == 'DA refusée' ou statuOr == 'A valider chef de service' alors retourner VRAI;
  - si statutDa == 'Bon d’achats validé' alors retourner VRAI;
  statutDaIntranet = [ 'Proposition achats', 'Demande d’achats','Création demande initiale'];
  - si statutDa est parmis statutDaDansDaAfficher alors retourn VRAI sinon FAUX
```

### Étape 3 : Détermination du type de DA
```
Déterminer le type selon daTypeId :
  - daDirect = si (daTypeId == TYPE_DA_DIRECT) alors VRAI sinon FAUX
  - daViaOR = si (daTypeId == TYPE_DA_AVEC_DIT) alors VRAI sinon FAUX
  - daReappro = si (daTypeId == TYPE_DA_REAPPRO) alors VRAI sinon FAUX
```

### Étape 4 : Mise à jour des informations OR
```
SI NON daDirect ALORS
  Mettre à jour les informations OR dans DaAfficher
  (numéro OR, date planning OR)
FIN SI
```

### Étape 5 : Modification du statut DA si nécessaire
```
SI statutOr = "DA à modifier" ET statutDa ≠ "En cours de création" ALORS
  statutDa ← "EN_COURS_CREATION"
FIN SI
```

### Étape 6 : Récupération des informations IPS
```
Récupérer depuis IPS :
  - infoDaDirect (pour DA Direct)
  - situationCde (pour DA Via OR)
```

### Étape 7 : Vérification des conditions d'arrêt
```
SI (NonDispo OU 
    (numeroOr = NULL ET daViaOR) OU 
    (numeroOr ≠ NULL ET statutOr vide) OU 
    (situationCde vide ET daViaOR)) ALORS
  RETOURNER statutBc actuel
FIN SI
```

### Étape 8 : Récupération des informations de commande
```
Récupérer :
  - numCde (numéro de commande)
  - statutSoumissionBc (statut soumission BC)

Règles de récupération numCde :
  - SI daDirect : numCde = infoDaDirect[0]['num_cde']
  - SI daViaOR : numCde = situationCde[0]['num_cde']
  - SI daReappro : numCde = numeroOr
```

### Étape 9 : Récupération des quantités
```
Récupérer depuis IPS :
  - qteDem (quantité demandée)
  - qteDispo (quantité disponible/à livrer)
  - qteLivee (quantité livrée)
  - qteReliquat (quantité en attente)
```

### Étape 10 : Évaluation des quantités
```
Calculer les états de livraison :
  - partiellementDispo = (qteDem ≠ qteALivrer ET qteLivee = 0 ET qteALivrer > 0) ET soumissionFait
  - completNonLivrer = ((qteDem = qteALivrer ET qteLivee < qteDem) OU 
                        (qteALivrer > 0 ET qteDem = qteALivrer + qteLivee)) ET soumissionFait
  - tousLivres = (qteDem = qteLivee ET qteDem ≠ 0) ET soumissionFait
  - partiellementLivre = (qteLivee > 0 ET qteLivee ≠ qteDem ET 
                          qteDem > qteLivee + qteALivrer) ET soumissionFait

Où soumissionFait = (EstFactureBlSoumis OU EstBlReapproSoumis)
```

### Étape 11 : Mise à jour de la situation commande
```
Mettre à jour dans DaAfficher :
  - positionBc
  - numeroCde
  - (pour daReappro : vérifier qteDem >= qteLivee avant mise à jour numeroCde)
```

### Étape 12 : Mise à jour des quantités de commande
```
Mettre à jour dans DaAfficher :
  - qteEnAttent
  - qteLivrer
  - qteDispo
  - qteDemIps
```

---

## Détermination du statut BC (ordre de priorité)

### Cas 1 : DA Direct et DA Reappro - Pas dans OR
```
SI ((situationCde vide ET daViaOR ET statutOr = "Validé") OU 
    (daReappro ET statutOr = "DA validée" ET numeroCde = NULL)) ALORS
  RETOURNER "PAS DANS OR"
FIN SI
```

### Cas 2 : DA Direct / DA Via OR - À générer
```
SI NON daReappro ET doitGenererBc() ALORS
  RETOURNER "A générer"
FIN SI

Conditions doitGenererBc :
  Pour daDirect :
    - statutOr = "DA validée" ET (infoDaDirect vide OU num_cde vide)
  Pour daViaOR :
    - statutDa = "Bon d’achats validé" ET statutOr = "Validé" ET 
      (situationCde vide OU num_cde vide)
```

### Cas 3 : DA Direct / DA Via OR - À éditer
```
SI NON daReappro ET doitEditerBc() ALORS
  RETOURNER "A éditer"
FIN SI

Conditions doitEditerBc :
  Pour daDirect :
    - num_cde > 0 ET position_livraison = "--" ET 
      position_bc ∈ ["TE", "EC"]
  Pour daViaOR :
    - num_cde > 0 ET slor_natcm = "C" ET position_livraison = "--" ET 
      position_bc ∈ ["TE", "EC"]
```

### Cas 4 : DA Direct / DA Via OR - À soumettre à validation
```
SI NON daReappro ET doitSoumettreBc() ALORS
  RETOURNER "A soumettre à validation"
FIN SI

Conditions doitSoumettreBc :
Pour daDirect :
  - num_cde > 0 ET
  - position_bc = "ED" (ou "TE" si position_livraison ≠ "--") ET
  - statutBc ∉ ["Soumis à validation", "A valider DA", "Validé", "Clôturé", "Refusé"] ET
  - BC n'existe pas encore dans da_bc_soumission
Pour daViaOR :
    - num_cde > 0 ET
    - slor_natcm = 'C'
  - position_bc = "ED" (ou "TE" si position_livraison ≠ "--") ET
  - statutBc ∉ ["Soumis à validation", "A valider DA", "Validé", "Clôturé", "Refusé"] ET
  - BC n'existe pas encore dans da_bc_soumission
```

### Cas 5 : DA Direct / DA Via OR - À envoyer au fournisseur
```
SI NON daReappro ET doitEnvoyerBc() ALORS
  RETOURNER "A envoyer au fournisseur"
FIN SI

Conditions doitEnvoyerBc :
  - position_bc = "ED" ET
  - statutSoumissionBc ∈ ["Validé", "Clôturé"] ET
  - BcEnvoyerFournisseur = FALSE
```

### Cas 6 : BC envoyé au fournisseur (sans facture/BL)
```
SI NON daReappro ET BcEnvoyerFournisseur ET NON EstFactureBlSoumis ALORS
  RETOURNER "BC envoyé au fournisseur"
FIN SI
```

### Cas 7 : DA Reappro - Cession à générer
```
SI daReappro ET numeroOr = NULL ET statutOr = "DA validée" ALORS
  RETOURNER "CESSION_A_GENERER"
FIN SI
```

### Cas 8 : DA Reappro - En cours de préparation (BL non soumis)
```
SI daReappro ET numeroOr ≠ NULL ET statutOr = "DA validée" ET 
   EstBlReapproSoumis = FALSE ALORS
  RETOURNER "EN_COURS_DE_PREPARATION"
FIN SI
```

### Cas 9 : États de livraison (tous types de DA)
```
SI partiellementDispo ALORS
  RETOURNER "Partiellement dispo"
SINON SI completNonLivrer ALORS
  RETOURNER "Complet non livré"
SINON SI tousLivres ALORS
  RETOURNER "Tous livrés"
SINON SI partiellementLivre ALORS
  RETOURNER "Partiellement livré"
FIN SI
```

### Cas 10 : BC envoyé avec facture/BL soumis
```
SI EstFactureBlSoumis ALORS
  RETOURNER "BC envoyé au fournisseur"
FIN SI
```

### Cas 11 : DA Direct / DA Via OR - Statut soumission
```
SI daDirect OU daViaOR ALORS
  RETOURNER statutSoumissionBc
FIN SI
```

### Cas 12 : DA Reappro - En cours de préparation (BL soumis)
```
SI daReappro ET numeroOr ≠ NULL ET statutOr = "DA validée" ET 
   EstBlReapproSoumis = TRUE ALORS
  RETOURNER "EN_COURS_DE_PREPARATION"
FIN SI
```

### Cas 13 : Par défaut
```
RETOURNER ''
```

---

## Diagramme de flux simplifié

```
DÉBUT
  ↓
[0] Initialisation
  ↓
[1] Extraction données DaAfficher
  ↓
[2] Conditions retour vide ? → OUI → RETOURNER ''
  ↓ NON
[3] Déterminer type DA (Direct/ViaOR/Reappro)
  ↓
[4] Mettre à jour info OR (si nécessaire)
  ↓
[5] Modifier statut DA (si nécessaire)
  ↓
[6] Récupérer infos IPS
  ↓
[7] Conditions d'arrêt ? → OUI → RETOURNER statutBc
  ↓ NON
[8] Récupérer infos commande
  ↓
[9] Récupérer quantités
  ↓
[10] Évaluer quantités
  ↓
[11-12] Mettre à jour DaAfficher
  ↓
[13] Déterminer statut BC selon ordre de priorité
  ↓
RETOURNER statut BC
```

---

## Types de DA et leurs statuts possibles

### DA Direct
- PAS DANS OR
- A générer
- A éditer
- A soumettre à validation
- A envoyer au fournisseur
- BC envoyé au fournisseur
- Partiellement dispo / Complet non livré / Tous livrés / Partiellement livré
- Statut soumission BC (de la table da_bc_soumission)

### DA Via OR
- PAS DANS OR
- A générer
- A éditer
- A soumettre à validation
- A envoyer au fournisseur
- BC envoyé au fournisseur
- Partiellement dispo / Complet non livré / Tous livrés / Partiellement livré
- Statut soumission BC (de la table da_bc_soumission)

### DA Reappro
- PAS DANS OR
- CESSION_A_GENERER
- EN_COURS_DE_PREPARATION
- Partiellement dispo / Complet non livré / Tous livrés / Partiellement livré
- BC envoyé au fournisseur

---

## Constantes et valeurs importantes

### Statuts DA
- STATUT_VALIDE
- STATUT_EN_COURS_CREATION
- STATUT_SOUMIS_ATE
- STATUT_SOUMIS_APPRO
- STATUT_AUTORISER_MODIF_ATE
- STATUT_DW_VALIDEE
- STATUT_DW_REFUSEE = 'DA refusée'
- STATUT_DW_A_VALIDE
- STATUT_DW_A_MODIFIER

### Statuts OR
- STATUT_VALIDE (DitOrsSoumisAValidation)
- STATUT_DW_VALIDEE
- STATUT_DW_REFUSEE
- STATUT_DW_A_VALIDE
- STATUT_DW_A_MODIFIER

### Positions BC
- POSITION_TERMINER
- POSITION_ENCOUR
- POSITION_EDITER

### Statuts Soumission BC
- STATUT_SOUMISSION
- STATUT_A_VALIDER_DA
- STATUT_VALIDE
- STATUT_CLOTURE
- STATUT_REFUSE
- STATUT_CESSION_A_GENERER
- STATUT_EN_COURS_DE_PREPARATION

### Types DA
- TYPE_DA_AVEC_DIT = 0
- TYPE_DA_DIRECT = 1
- TYPE_DA_REAPPRO = 2