---
title: Suivi des opérations – tâches de correction
category: Administration
description: Toute opération au résultat autre que OK devient une tâche à trier (effort, priorité), à suivre jusqu'à sa résolution.
---

| | |
|---|---|
| **Code décrit** | branche `feature/taches-suivi-operation` |
| **Page** | Administration › Historique › Suivi des opérations, onglet **Tâches** (`/admin/suivi-operation?vue=taches`) |
| **Accès** | profil HFF-ADMIN uniquement |
| **Script SQL** | `sql/historisation/suivi_operation_tache.sql` (à lancer avant le déploiement) |

## 1. Principe

Une opération dont le résultat n'est pas **OK** (ÉCHEC, INTERROMPU, ALERTE) révèle un bug à corriger. Elle crée automatiquement une **tâche**.

**Une tâche correspond à un bug, pas à une opération.** Les opérations qui cassent pour la même raison sont regroupées dans la même tâche, et chaque nouvelle occurrence augmente son compteur.

Deux opérations sont regroupées quand elles ont :
- le même type d'opération (ex. « DA - facture / BL »),
- la même étape en erreur (ex. « Contrôle mise à jour DA »),
- le même message, une fois retiré ce qui change d'une fois à l'autre : numéros (document, ligne, id), dates, chemins de fichiers, valeurs entre guillemets.

Exemple : « Copie DocuWare : DAP26119897.pdf » et « Copie DocuWare : DAP26119954.pdf » donnent la même tâche.

Une opération ne crée **qu'une seule tâche** : celle de la première étape non OK. C'est l'étape la plus précise, puisqu'une étape interne en échec fait aussi échouer les étapes qui la contiennent.

## 2. Cycle de vie d'une tâche

| Statut | Signification |
|---|---|
| **À trier** | nouvelle tâche, effort et priorité pas encore choisis |
| **À faire** | triée, en attente de traitement |
| **En cours** | correction en cours |
| **Résolu** | corrigée : la date et l'auteur de la résolution sont enregistrés |
| **Ignoré** | pas un bug (erreur de saisie, cas normal…) : le compteur continue, sans réouverture |

**Régression.** Si une tâche **Résolu** se reproduit après sa date de résolution, elle repasse automatiquement en **À faire** avec le badge jaune **Régression**. La correction n'a pas tenu, ou le problème a une autre cause.

**Priorité de départ.** **Haute** pour un ÉCHEC, **Normale** pour une ALERTE ou un INTERROMPU. Elle peut ensuite être changée.

## 3. Trier : rapide ou long

Le temps de correction ne peut pas être deviné automatiquement : c'est l'administrateur qui l'estime au tri.

| Effort | Durée estimée | Traitement conseillé |
|---|---|---|
| **Rapide** | moins de 30 min | au fil de l'eau (ex. vider la liste chaque matin), branche `hotfix/` |
| **Moyen** | une demi-journée à une journée | assigner une personne et une échéance, planifier |
| **Long** | plusieurs jours | assigner, planifier, branche `feature/` |

Choisir l'effort d'une tâche **À trier** la fait passer automatiquement en **À faire**.

## 4. Utiliser l'onglet Tâches

### Compteurs

En haut de l'onglet, quatre compteurs. Un clic sur un compteur affiche les tâches correspondantes.
- **À trier** : effort et priorité à choisir.
- **Rapides en attente** : tâches rapides à faire ou en cours.
- **Moyennes / longues** : à planifier.
- **Régressions** : bugs corrigés qui sont revenus. Le clic ouvre les tâches non terminées, où les régressions se repèrent à leur badge jaune.

Le nombre de tâches à trier est aussi affiché en pastille rouge sur l'onglet.

### Liste

Ordre de la liste : les tâches à trier d'abord, puis par priorité, régression, nombre d'occurrences et dernière occurrence.

- **Priorité, statut, effort** : enregistrés dès que la valeur change.
- **Occ.** : nombre d'occurrences. Un clic ouvre l'onglet Opérations filtré sur toutes les opérations de la tâche.
- **Assigné / échéance** : une échéance dépassée s'affiche en rouge « En retard ».
- Filtres disponibles : texte (étape, message, n° document, cause, correction), opération, statut (par défaut « Non terminées »), effort (dont « Non défini »), priorité, personne assignée.

### Détail (bouton ▾)

- Message regroupé (la version nettoyée qui sert au regroupement), dernier message complet, première occurrence.
- Champs à remplir : **Assigné à**, **Échéance**, **Cause identifiée**, **Correction** (branche, commit ou explication). Ils sont enregistrés avec le bouton **Enregistrer**.
- **Assigné à** propose les utilisateurs qui ont le rôle **INTERVENANT** (le même que pour le ticketing). Une personne déjà assignée qui a perdu ce rôle reste affichée, avec la mention « n'est plus intervenant ».

### Donner le rôle INTERVENANT à un utilisateur

Il n'existe pas d'écran pour attribuer ce rôle : il s'ajoute directement en base, dans la table `user_roles` (rôle 8 = INTERVENANT, constante `Role::ROLE_INTERVENANT`).

```sql
INSERT INTO user_roles (user_id, role_id)
SELECT u.id, 8
FROM users u
WHERE u.nom_utilisateur IN ('nom.utilisateur1', 'nom.utilisateur2')
  AND NOT EXISTS (SELECT 1 FROM user_roles x WHERE x.user_id = u.id AND x.role_id = 8);
```

Le même modèle se trouve à la section 6 de `sql/tik/remise_en_etat_ticketing.sql`. Le rôle est lu à chaque affichage de la page : il n'y a pas de cache à vider.
- Historique : « Résolu par … le … », « Modifié par … le … ».

### Depuis l'onglet Opérations

Chaque opération en problème affiche un badge avec le statut de sa tâche (et « Régression » le cas échéant). Un clic ouvre la tâche dans l'onglet Tâches.

### Reprendre l'historique

Le bouton **Reprendre l'historique** crée les tâches des opérations en problème enregistrées **avant** la mise en place des tâches. Il peut être relancé sans risque : les opérations déjà rattachées à une tâche sont ignorées.

## 5. Fonctionnement technique

| Élément | Rôle |
|---|---|
| table `suivi_operation_tache` | une ligne par tâche, unique par `signature` (sha1 du type, de l'étape et du message nettoyés) |
| colonne `historique_operation_etape.id_tache` | rattache chaque opération à sa tâche (badge et filtre `?tache=`) |
| `TacheSuiviOperation` | création / mise à jour de la tâche, nettoyage des messages, reprise de l'historique |
| `SuiviOperationService::enregistrerTache()` | appelé à la fermeture de la première étape non OK d'une opération |
| `SuiviOperationTacheRepository` | liste filtrée, compteurs, tâches des opérations affichées |
| `SuiviOperationController::modifierTache()` | route `api_suivi_operation_tache_modifier` (POST JSON, admin) |
| `SuiviOperationController::reprendreHistorique()` | route `api_suivi_operation_taches_reprise` (POST, admin) |

Points d'attention :
- L'enregistrement d'une tâche passe par la même écriture protégée que le reste du suivi : une erreur à cet endroit est écrite dans le log PHP et ne bloque jamais l'opération de l'utilisateur.
- La colonne `id_tache` n'est envoyée à l'insertion d'une étape que si une tâche existe : sans le script SQL, le suivi des opérations continue de s'enregistrer.
- En revanche, la page **Suivi des opérations** lit la table et la colonne : le script SQL doit être lancé **avant** le déploiement.
- Une occurrence plus ancienne que la dernière connue (cas de la reprise de l'historique) ne remplace pas les informations de la dernière occurrence, et ne réouvre pas une tâche résolue après elle.
- La vue Tâches est servie par la même route que le suivi (`suivi_operation_index`, paramètre `vue=taches`) : aucun droit de page supplémentaire à paramétrer.
