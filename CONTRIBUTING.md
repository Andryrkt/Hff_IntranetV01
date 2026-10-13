# Contribuer à HFF Intranet

Ce document décrit comment nommer les branches et faire arriver son travail en préproduction (`dev`) puis en production (`main`).

## Branches principales

| Branche | Rôle |
|---|---|
| `main` | Code en production. On n'y pousse jamais directement : tout passe par une pull request. |
| `dev` | Préproduction. Chaque push sur `dev` est déployé automatiquement sur **HffIntranet PreProd** (voir `.github/workflows/deploy.yml`). On n'y pousse pas directement non plus : on passe par une pull request. |

## Nommer une branche

Format : `type/description-courte`, en minuscules, avec des tirets.

| Type | Usage | Exemple |
|---|---|---|
| `feature/` | Nouvelle fonctionnalité | `feature/ddp-neg` |
| `fix/` | Correction de bug | `fix/dom-calcul-indemnite` |
| `hotfix/` | Correction urgente en production, créée depuis `main` | `hotfix/blocage-soumission-bl` |
| `refactor/` | Réorganisation du code sans changement fonctionnel | `refactor/bdd-dit` |
| `docs/` | Documentation | `docs/contributing` |

À éviter : les noms sans type (`ddp`, `dit_fix`), les underscores, les noms en double (`devis_neg_fix` et `devi_neg_ips_fix`).

## Flux de travail

```
feature/xxx ──PR──> dev (PreProd, tests) ──PR──> main (Production)
```

1. Partir d'une `dev` à jour :
   ```bash
   git switch dev
   git pull
   git switch -c feature/ma-fonctionnalite
   ```
2. Faire des commits courts avec un message clair. Format conseillé : `type: description`, par exemple `fix: blocage de soumission si montant négatif`.
3. Récupérer régulièrement les changements de `dev` pour éviter les gros conflits :
   ```bash
   git fetch origin
   git merge origin/dev
   ```
4. Pousser la branche et ouvrir une pull request vers `dev`. Après la fusion, la PreProd est mise à jour automatiquement : on y teste la fonctionnalité.
   ```bash
   git push -u origin feature/ma-fonctionnalite
   ```
5. Une fois la pull request fusionnée, supprimer la branche (sur GitHub et en local) :
   ```bash
   git switch dev
   git pull
   git branch -d feature/ma-fonctionnalite
   git fetch --prune
   ```

## Mise en production

Quand la PreProd est validée, on ouvre une pull request **`dev` → `main`**.

## Correction urgente (hotfix)

1. Créer la branche depuis `main` : `git switch main && git pull && git switch -c hotfix/description`.
2. Ouvrir une pull request vers `main`.
3. Après la fusion, reporter la correction dans `dev` (pull request `main` → `dev`) pour qu'elle ne disparaisse pas à la prochaine mise en production.

## Branches abandonnées

Une branche qu'on ne fusionnera pas n'est pas laissée sur GitHub. On l'archive sous forme de tag, puis on la supprime :

```bash
git tag -a archive/nom-de-la-branche origin/nom-de-la-branche -m "Archive avant suppression"
git push origin archive/nom-de-la-branche
git push origin --delete nom-de-la-branche
```

Pour la restaurer plus tard :

```bash
git switch -c nom-de-la-branche archive/nom-de-la-branche
```

## À ne jamais commiter

- Fichiers de configuration contenant des mots de passe ou des accès à la base de données
- Exports de données (`.xlsx`, `.csv`, dumps SQL de production)
- Dossiers `vendor/` et `node_modules/`
