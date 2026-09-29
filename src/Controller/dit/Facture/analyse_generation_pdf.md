# Analyse de la génération de PDF — `DitFactureSoumisAValidationController`

**Fichier analysé** : [`src/Controller/dit/Facture/DitFactureSoumisAValidationController.php`](src/Controller/dit/Facture/DitFactureSoumisAValidationController.php)
**Point d'entrée étudié** : appel de `enregistrerPdf($dataForm, $numDit, $factureSoumisAValidation, $interneExterne, $codeSociete)` à la [ligne 146](src/Controller/dit/Facture/DitFactureSoumisAValidationController.php#L146)

---

## 1. Récupération des données

### 1.1 Vue d'ensemble des sources

Le flux mélange **trois sources de données hétérogènes** :

| Source | Accès | Exemples de tables |
|---|---|---|
| Informix (atelier/OR) | `Model::$connect` (`DatabaseInformix`) | `sav_lor`, `sav_eor`, `sav_itv`, `sav_liv` |
| SQL Server legacy | `Model::$connexion` (ODBC brut, `odbc_fetch_array`) | `facture_soumis_a_validation` |
| SQL Server via Doctrine | Repositories Symfony (DQL) | `demande_intervention`, `ors_soumis_a_validation`, `ri_soumis_a_validation` |

### 1.2 Origine de chaque paramètre passé à `enregistrerPdf`

#### `$dataForm`
Provient de `$form->getData()` ([ligne 130](src/Controller/dit/Facture/DitFactureSoumisAValidationController.php#L130)), après validation du formulaire `DitFactureSoumisAValidationType` lié à l'entité `DitFactureSoumisAValidation`. Avant d'atteindre `enregistrerPdf`, plusieurs contrôles/requêtes valident sa cohérence :

- `recupNumeroOr($numDit, $codeSociete)` (Informix, `sav_eor`, filtre `seor_refdem = numDit`) — récupère le n° d'OR rattaché à la DIT, utilisé pour pré-remplir l'entité avant soumission du formulaire.
- `recupTypeFacture($numFac, $codeSociete)` et `recupQterea($numFac, $codeSociete)` (Informix, `sav_lor`) — **chacune appelée deux fois de suite** : une fois dans le test `array_key_exists(...)` ([ligne 95](src/Controller/dit/Facture/DitFactureSoumisAValidationController.php#L95)), une seconde fois pour extraire la valeur réelle ([lignes 99-100](src/Controller/dit/Facture/DitFactureSoumisAValidationController.php#L99-L100)).
- `nombreFact()` → `recupNombreFacture($numOr, $numFac, $codeSociete)` (Informix, `COUNT(slor_numfac)` sur `sav_lor`) — vérifie que la facture existe bien pour cet OR.
- `recupNumeroSoumission($numOr, $codeSociete)` — **requête SQL Server en ODBC brut** (`$this->connexion`, pas de requête préparée) sur `facture_soumis_a_validation`, calcule `MAX(numero_soumission) + 1`.

#### `$numDit`
Paramètre de route (`{numDit}`), utilisé tel quel, sans requête pour l'obtenir — c'est une donnée d'entrée utilisateur.

#### `$factureSoumisAValidation`
Construit par `ditFactureSoumisAValidation()` (trait `DitFactureSoumisAValidationtrait`, appelée [ligne 135](src/Controller/dit/Facture/DitFactureSoumisAValidationController.php#L135)) :

```php
private function ditFactureSoumisAValidation($numDit, $dataForm, DitFactureSoumisAValidationModel $ditFactureSoumiAValidationModel, $numeroSoumission, $em, ditFactureSoumisAValidation $ditFactureSoumiAValidation): array
{
    // 1) recupInfoFact (Informix, JOIN sav_lor / sav_itv) — appel n°1
    $infoFacture = $ditFactureSoumiAValidationModel->recupInfoFact($dataForm->getNumeroOR(), ..., $codeSociete);

    // 2) findAgSevDebiteur (Doctrine, demande_intervention) — appel n°1
    $agServDebDit = $em->getRepository(DemandeIntervention::class)->findAgSevDebiteur($numDit, $codeSociete);

    foreach ($infoFacture as $value) {
        // 3) recuperationStatutItv (Informix, sav_lor/sav_eor/sav_liv) — 1 fois PAR ITV
        $statutItv = $this->statutOrsSoumisValidation($ditFactureSoumiAValidationModel, $value['numeroor'], (int)$value['numeroitv'], $codeSociete);

        // 4) findMontantValide (Doctrine, ors_soumis_a_validation) — 2 sous-requêtes — 1 fois PAR ITV
        $montantValide = $em->getRepository(DitOrsSoumisAValidation::class)
            ->findMontantValide($dataForm->getNumeroOR(), (int)$value['numeroitv'], $codeSociete)['montantItv'];

        // → construit une entité DitFactureSoumisAValidation par ITV facturé (non persistée ici)
    }

    return $factureSoumisAValidation; // tableau d'entités, une par ITV
}
```

Ce tableau représente **une ligne d'entité par intervention (ITV)** facturée, avec le montant facturé et le montant validé associés. Il n'est **pas encore persisté** à ce stade — la persistance a lieu plus tard, [ligne 161](src/Controller/dit/Facture/DitFactureSoumisAValidationController.php#L161), via `ajoutDataFactureAValidation()`.

Juste après, `conditionSurInfoFacture()` ([lignes 274-297](src/Controller/dit/Facture/DitFactureSoumisAValidationController.php#L274-L297)) est appelée pour vérifier que chaque ITV facturé a bien un rapport d'intervention (RI) soumis :

```php
private function conditionSurInfoFacture(...)
{
    // recupInfoFact — appel n°2, MÊMES paramètres que ci-dessus
    $infoFacture = $ditFactureSoumiAValidationModel->recupInfoFact($dataForm->getNumeroOR(), $ditFactureSoumiAValidation->getNumeroFact(), $codeSociete);

    if ($infoFacture[0]['typeor'] === 210 && $codeAgenceUser === '60') {
        return false; // dérogation agence 60
    }

    // findRiSoumis (Doctrine, ri_soumis_a_validation)
    $riSoumis = $this->getEntityManager()->getRepository(DitRiSoumisAValidation::class)->findRiSoumis($ditFactureSoumiAValidation->getNumeroOR(), $codeSociete);

    // bloque la soumission si un ITV facturé n'a pas de RI soumis correspondant
    ...
}
```

#### `$interneExterne`
Obtenu via `DitRepository::findInterneExterne($numDit, $codeSociete)` ([ligne 144](src/Controller/dit/Facture/DitFactureSoumisAValidationController.php#L144)) :

```php
// src/Repository/dit/DitRepository.php
public function findInterneExterne($numDit, string $codeSociete)
{
    return $this->createQueryBuilder('d')
        ->select('d.internetExterne')
        ->where('d.numeroDemandeIntervention = :numDit')
        ->andWhere('d.codeSociete = :codeSociete')
        ->setParameter('numDit', $numDit)
        ->setParameter('codeSociete', $codeSociete)
        ->getQuery()
        ->getSingleScalarResult();
}
```

DQL simple sur `DemandeIntervention` (table `demande_intervention`), renvoie `'INTERNE'` ou `'EXTERNE'`. Cette valeur pilote ensuite le nommage du PDF et le circuit de fusion des pièces jointes.

#### `$codeSociete`
Récupéré une seule fois en tout début de méthode via `$this->getSecurityService()->getCodeSocieteUser()` ([ligne 66](src/Controller/dit/Facture/DitFactureSoumisAValidationController.php#L66)) — dépend de la session utilisateur, aucune requête BDD dédiée.

### 1.3 Ce qui se passe *dans* `enregistrerPdf`

```php
public function enregistrerPdf($dataForm, string $numDit, $factureSoumisAValidation, string $interneExterne, string $codeSociete)
{
    // (a) Informix — jointure sav_eor/sav_lor/sav_itv, agrégation montants pièces/MO/achats/lubrifiants
    $orSoumisFact = $this->ditFactureSoumiAValidationModel->recupOrSoumisValidation($this->ditFactureSoumiAValidation->getNumeroOR(), $dataForm->getNumeroFact(), $codeSociete);

    // (b) Informix — sav_eor, numéro de devis
    $numDevis = $this->ditModel->recupererNumdevis($this->ditFactureSoumiAValidation->getNumeroOR(), $codeSociete);

    // (c) voir détail ci-dessous : ré-exécute recupInfoFact + findAgSevDebiteur + findOneBy(DemandeIntervention) + boucle par ITV
    $statut = $this->affectationStatutFac($this->getEntityManager(), $numDit, $dataForm, $this->ditFactureSoumiAValidationModel, $this->ditFactureSoumiAValidation, $codeSociete);

    // (d) pas de BDD — agrégation en mémoire des données déjà chargées
    $montantPdf = $this->montantpdf($factureSoumisAValidation, $statut, $orSoumisFact);

    // (e) Doctrine — 2 sous-requêtes DQL (MAX version + SELECT statut IN (...))
    $estFactureConformAOr = $this->estFactureConformAOr($factureSoumisAValidation, $codeSociete);

    // (f) Informix — sav_lor, CASE WHEN COUNT(*)>0 THEN 'PF' ELSE 'CF'
    $etatOr = $this->etatOr($dataForm, $this->ditFactureSoumiAValidationModel, $codeSociete);

    // (g) Doctrine — findOneBy(DemandeIntervention) PUIS persist/flush
    $this->modificationEtatFacturDit($etatOr, $numDit, $codeSociete);

    // (h) aucune BDD — génération TCPDF pure à partir des données déjà calculées
    return $this->genererPdfFacture->GenererPdfFactureSoumisAValidation($this->ditFactureSoumiAValidation, $numDevis, $montantPdf, $etatOr, $this->nomUtilisateur()['mailUtilisateur'], $interneExterne, $estFactureConformAOr);
}
```

Détail de `affectationStatutFac()` (trait) :

```php
private function affectationStatutFac($em, $numDit, $dataForm, DitFactureSoumisAValidationModel $ditFactureSoumiAValidationModel, $ditFactureSoumiAValidation, $codeSociete)
{
    // recupInfoFact — appel n°3, MÊMES paramètres que dans ditFactureSoumisAValidation() et conditionSurInfoFacture()
    $infoFacture = $ditFactureSoumiAValidationModel->recupInfoFact(...);

    // findAgSevDebiteur — appel n°2, redondant avec ditFactureSoumisAValidation()
    $agServDebDit = $em->getRepository(DemandeIntervention::class)->findAgSevDebiteur($numDit, $codeSociete);

    // findOneBy(demande_intervention) — pour lire uniquement getMigration()
    $migration = $em->getRepository(DemandeIntervention::class)
        ->findOneBy(['numeroDemandeIntervention' => $numDit, 'codeSociete' => $codeSociete])
        ->getMigration();

    foreach ($infoFacture as $value) {
        // findNbrItv (DQL COUNT)
        $orSoumisValidationRepo->findNbrItv($value['numeroor'], $codeSociete);

        // findStatutByNumeroVersionMax — 2 sous-requêtes DQL (MAX version + SELECT statut)
        $orSoumisValidationRepo->findStatutByNumeroVersionMax($value['numeroor'], (int)$value['numeroitv'], $codeSociete);

        // findMontantValide — appel n°2 PAR ITV (déjà fait dans ditFactureSoumisAValidation), 2 sous-requêtes DQL
        $orSoumisValidationRepo->findMontantValide($value['numeroor'], (int)$value['numeroitv'], $codeSociete)['montantItv'];

        // cascade de comparaisons → libellé de statut par ITV + compteurs de contrôle
    }

    return ['statutFac' => [...], 'nombreStatutControle' => [...]];
}
```

Le résultat `$statut` alimente `montantpdf()` qui, elle, ne fait **aucun accès BDD** : elle recombine en mémoire les données déjà chargées (`$factureSoumisAValidation`, `$statut['statutFac']`, `$orSoumisFact`) pour produire les tableaux affichés dans le PDF (détail par ITV, totaux, récapitulatif OR).

Enfin, `GenererPdfFactureSoumisAValidation()` (service `GenererPdfFactureAValidation`) construit le PDF avec TCPDF à partir de ces données déjà en mémoire — **aucun accès BDD** — puis écrit le fichier sur disque via `fileUploaderService` (chemin dépendant de `$interneExterne` : `factureValidation_...` en interne, `validation_facture_client_...` sinon).

### 1.4 Schéma récapitulatif du flux

```
factureSoumisAValidation()
 ├─ recupNumeroOr                          (Informix)      × 1
 ├─ recupTypeFacture                       (Informix)      × 2 (redondant)
 ├─ recupQterea                            (Informix)      × 2 (redondant)
 ├─ recupNombreFacture (nombreFact)        (Informix)      × 1
 ├─ recupNumeroSoumission                  (SQL Server ODBC brut) × 1
 ├─ ditFactureSoumisAValidation()
 │   ├─ recupInfoFact                      (Informix)      appel n°1/3
 │   ├─ findAgSevDebiteur                  (Doctrine)      appel n°1/2
 │   └─ [pour chaque ITV] recuperationStatutItv + findMontantValide  (Informix + Doctrine×2) appel n°1/2 par ITV
 ├─ conditionSurInfoFacture()
 │   ├─ recupInfoFact                      (Informix)      appel n°2/3 (redondant)
 │   └─ findRiSoumis                       (Doctrine)      × 1
 ├─ findInterneExterne                     (Doctrine)      × 1
 └─ enregistrerPdf()                                        ← point d'entrée analysé
      ├─ recupOrSoumisValidation           (Informix)      × 1
      ├─ recupererNumdevis                 (Informix)      × 1
      ├─ affectationStatutFac()
      │   ├─ recupInfoFact                 (Informix)      appel n°3/3 (redondant)
      │   ├─ findAgSevDebiteur             (Doctrine)      appel n°2/2 (redondant)
      │   ├─ findOneBy(DemandeIntervention)                1ère occurrence (redondante avec modificationEtatFacturDit)
      │   └─ [pour chaque ITV] findNbrItv + findStatutByNumeroVersionMax(×2) + findMontantValide(×2)  appel n°2/2 par ITV
      ├─ montantpdf()                      (aucune BDD, agrégation mémoire)
      ├─ estFactureConformAOr()
      │   └─ findOrSoumisValid             (Doctrine, ×2 sous-requêtes)
      ├─ etatOr()
      │   └─ recupEtatOr                   (Informix)      × 1
      ├─ modificationEtatFacturDit()
      │   └─ findOneBy(DemandeIntervention)                2e occurrence identique + persist/flush
      └─ GenererPdfFactureSoumisAValidation (aucune BDD, TCPDF)
```

---

## 2. Améliorations proposées

### 2.1 `recupInfoFact($numOR, $numFact, $codeSociete)` — exécutée 3 fois à l'identique

Appelée dans `ditFactureSoumisAValidation()`, `conditionSurInfoFacture()` et `affectationStatutFac()`, toujours avec les **mêmes trois paramètres**. C'est une jointure Informix (`sav_lor`/`sav_itv`) potentiellement coûteuse, ré-exécutée 3 fois pour produire exactement le même résultat.

**Suggestion** : calculer `$infoFacture` **une seule fois** dans `factureSoumisAValidation()` et le transmettre en paramètre aux trois méthodes qui en ont besoin.

```php
// Dans factureSoumisAValidation(), avant les appels :
$infoFacture = $this->ditFactureSoumiAValidationModel->recupInfoFact($numOrBaseDonner[0]['numor'], $numFac, $codeSociete);

// Puis passer $infoFacture en paramètre à :
// - ditFactureSoumisAValidation($numDit, $dataForm, ..., $infoFacture)
// - conditionSurInfoFacture(..., $infoFacture)
// - enregistrerPdf(..., $infoFacture) → affectationStatutFac(..., $infoFacture)
```

Gain estimé : **-2 requêtes Informix par soumission de facture** (la jointure étant l'une des plus coûteuses du flux).

### 2.2 `findAgSevDebiteur($numDit, $codeSociete)` — exécutée 2 fois à l'identique

Appelée dans `ditFactureSoumisAValidation()` et `affectationStatutFac()` avec les mêmes paramètres.

**Suggestion** : la calculer une fois dans `factureSoumisAValidation()` (ou dans `enregistrerPdf()`) et la passer en paramètre, au lieu de la recalculer dans chaque méthode consommatrice.

### 2.3 `findMontantValide($numOr, $numItv, $codeSociete)` — 2 appels par ITV, soit 4 requêtes DQL par ITV

Cette méthode interne exécute déjà 2 requêtes (MAX version puis SELECT montant). Elle est appelée une fois par ITV dans `ditFactureSoumisAValidation()` et une seconde fois par ITV dans `affectationStatutFac()` — pour une facture de 10 ITV, cela représente **40 requêtes DQL** rien que pour cette donnée.

**Suggestions** :
- **Court terme** : calculer les montants validés par ITV une seule fois (dans `ditFactureSoumisAValidation()`), et transmettre le résultat (`[numeroItv => montantValide]`) à `affectationStatutFac()` au lieu de le recalculer.
- **Moyen terme** : remplacer les 2 sous-requêtes (MAX + SELECT) par **une seule requête avec sous-requête corrélée ou fenêtrage**, par exemple :

```php
// Au lieu de : MAX(numeroVersion) puis SELECT ... WHERE numeroVersion = :max
// Une seule requête DQL avec sous-requête :
$qb = $this->createQueryBuilder('osv');
$qb->where('osv.numeroOR = :numOr')
   ->andWhere('osv.numeroItv = :numItv')
   ->andWhere('osv.codeSociete = :codeSociete')
   ->andWhere($qb->expr()->eq(
       'osv.numeroVersion',
       '(SELECT MAX(osv2.numeroVersion) FROM ' . DitOrsSoumisAValidation::class . ' osv2
         WHERE osv2.numeroOR = osv.numeroOR AND osv2.codeSociete = osv.codeSociete)'
   ))
   ->setParameters(['numOr' => $numOr, 'numItv' => $numItv, 'codeSociete' => $codeSociete]);
```

- **Alternative plus performante** : charger **en une seule requête** toutes les lignes `ors_soumis_a_validation` de la version max pour l'OR (au lieu d'une requête par ITV), puis indexer le résultat en mémoire par `numeroItv` :

```php
// Une requête pour tout l'OR au lieu d'une requête par ITV
$toutesLesLignesVersionMax = $orSoumisValidationRepo->findAllByNumeroVersionMax($numOr, $codeSociete);
$montantParItv = [];
foreach ($toutesLesLignesVersionMax as $ligne) {
    $montantParItv[$ligne->getNumeroItv()] = $ligne->getMontantItv();
}
// Puis dans la boucle : $montantParItv[$value['numeroitv']] ?? 0
```

Cela transforme un pattern **N+1 requêtes** (une paire de requêtes par ITV) en **2 requêtes fixes** (MAX version + toutes les lignes de cette version), quel que soit le nombre d'ITV.

### 2.4 `findOneBy(DemandeIntervention)` — chargé 2 fois pour la même DIT

`affectationStatutFac()` charge l'entité `DemandeIntervention` uniquement pour lire `getMigration()`, puis `modificationEtatFacturDit()` la recharge intégralement pour appeler `setEtatFacturation()` + `persist()`/`flush()`.

**Suggestion** : charger l'entité **une seule fois** (par exemple dans `enregistrerPdf()`), la réutiliser pour lire `getMigration()` et pour `setEtatFacturation()` :

```php
public function enregistrerPdf($dataForm, string $numDit, $factureSoumisAValidation, string $interneExterne, string $codeSociete)
{
    $demandeIntervention = $this->getEntityManager()->getRepository(DemandeIntervention::class)
        ->findOneBy(['numeroDemandeIntervention' => $numDit, 'codeSociete' => $codeSociete]);

    $statut = $this->affectationStatutFac(..., $demandeIntervention); // réutilise ->getMigration()
    ...
    $this->modificationEtatFacturDit($etatOr, $demandeIntervention);  // réutilise l'entité déjà managée
}

private function modificationEtatFacturDit($etatOr, DemandeIntervention $demandeIntervention): void
{
    $demandeIntervention->setEtatFacturation($etatOr);
    $this->getEntityManager()->flush(); // persist() inutile : l'entité est déjà managée par Doctrine
}
```

Notons également que `persist()` est inutile ici : l'entité provient d'un `findOneBy()`, elle est donc déjà **managée** par l'`EntityManager` — un simple `flush()` suffit à propager le changement.

### 2.5 `recupTypeFacture` / `recupQterea` — doublées par un test suivi d'une relecture

```php
// Ligne 95 : test d'existence — 1re exécution
if (!array_key_exists(0, $this->ditFactureSoumiAValidationModel->recupTypeFacture($numFac, $codeSociete)) || ...) {
    ...
} else {
    // Ligne 99 : relecture de la même requête — 2e exécution
    $typeFacture = (int)$this->ditFactureSoumiAValidationModel->recupTypeFacture($numFac, $codeSociete)[0];
    $qterea = (int)$this->ditFactureSoumiAValidationModel->recupQterea($numFac, $codeSociete)[0];
}
```

**Suggestion** : stocker le résultat dans une variable et le réutiliser :

```php
$typeFactureResult = $this->ditFactureSoumiAValidationModel->recupTypeFacture($numFac, $codeSociete);
$qtereaResult = $this->ditFactureSoumiAValidationModel->recupQterea($numFac, $codeSociete);

if (!array_key_exists(0, $typeFactureResult) || !array_key_exists(0, $qtereaResult)) {
    $message = "...";
    $this->historiqueOperation->sendNotificationSoumission($message, $numFac, 'dit_index');
} else {
    $typeFacture = (int)$typeFactureResult[0];
    $qterea = (int)$qtereaResult[0];
}
```

Élimine 2 requêtes Informix par soumission.

### 2.6 Requête ODBC brute non préparée (`recupNumeroSoumission`)

`recupNumeroSoumission()` interroge SQL Server via ODBC brut (`odbc_fetch_array`), sans requête préparée visible dans le code exploré — un risque d'injection SQL si `$numOr`/`$codeSociete` ne sont pas strictement contrôlés en amont. Bien que hors du périmètre strict de la génération PDF, ce point mérite une **revue de sécurité dédiée** (paramétrage de la requête ou migration vers Doctrine/DQL comme le reste du flux).

### 2.7 Regroupement en fin de flux : possibilité de mise en cache locale par requête

Plusieurs de ces requêtes (`recupInfoFact`, `findAgSevDebiteur`, `findMontantValide`) partagent la même clé fonctionnelle (`numOr` + `numFact`/`numItv` + `codeSociete`) sur toute la durée d'une même requête HTTP. Une solution transverse, si le refactoring complet des signatures est jugé trop invasif à court terme, consiste à introduire un **cache mémoire local à la requête** (tableau associatif clé → résultat) dans le modèle :

```php
class DitFactureSoumisAValidationModel extends Model
{
    private array $cacheInfoFact = [];

    public function recupInfoFact($numOR, $numFact, $codeSociete)
    {
        $cleCache = "{$numOR}|{$numFact}|{$codeSociete}";
        if (isset($this->cacheInfoFact[$cleCache])) {
            return $this->cacheInfoFact[$cleCache];
        }

        // ... requête Informix existante ...

        return $this->cacheInfoFact[$cleCache] = $resultat;
    }
}
```

**Avantage** : réduction immédiate du nombre de requêtes sans changer les signatures ni le flux d'appel entre les méthodes du contrôleur/trait.
**Limite** : ne résout pas la duplication de logique (le code appelant continue d'appeler la méthode plusieurs fois), c'est un correctif pragmatique — la vraie solution long terme reste de **calculer une fois et transmettre par paramètre** (sections 2.1 à 2.4), qui rend le flux de données explicite et testable.

### 2.8 Tableau de synthèse des optimisations

| Donnée redondante | Nb d'appels identiques actuels | Nb cible | Gain |
|---|---|---|---|
| `recupInfoFact` (Informix, jointure) | 3 | 1 | -2 requêtes/jointure par soumission |
| `findAgSevDebiteur` (Doctrine) | 2 | 1 | -1 requête par soumission |
| `findMontantValide` (Doctrine, 2 sous-requêtes) | 2 × nb ITV | 1 requête globale (indépendante du nb d'ITV) | de `4×N` à `2` requêtes |
| `findOneBy(DemandeIntervention)` | 2 | 1 | -1 requête par soumission |
| `recupTypeFacture` / `recupQterea` | 2 chacune | 1 chacune | -2 requêtes par soumission |

Sur une facture à 10 ITV, ces optimisations permettraient de passer d'environ **~55 requêtes BDD** (Informix + Doctrine confondues, hors requêtes indépendantes comme `recupOrSoumisValidation`, `recupererNumdevis`, `findOrSoumisValid`, `recupEtatOr`) à environ **~15 requêtes**, soit une réduction d'environ 70 % des allers-retours BDD sur ce flux.

---

## 3. Conclusion

La génération du PDF de soumission de facture (`enregistrerPdf`) repose sur un flux de données déjà largement pré-calculé en amont (`$dataForm`, `$factureSoumisAValidation`, `$interneExterne`), mais **recalcule plusieurs informations déjà obtenues plus tôt dans `factureSoumisAValidation()`** — en particulier `recupInfoFact`, `findAgSevDebiteur`, `findMontantValide` et l'entité `DemandeIntervention`. Ces redondances proviennent d'un manque de partage d'état entre les méthodes du contrôleur et du trait `DitFactureSoumisAValidationtrait`, chacune récupérant ses propres données au lieu de les recevoir en paramètre.

Les optimisations proposées (calcul unique + transmission par paramètre, regroupement des requêtes par ITV en une requête par OR, réutilisation de l'entité Doctrine déjà chargée) ne nécessitent pas de changement de schéma de données ni de logique métier : elles consistent à **restructurer le passage de données entre les méthodes existantes**, ce qui limite le risque de régression tout en réduisant significativement la charge sur les bases Informix et SQL Server à chaque soumission de facture.
