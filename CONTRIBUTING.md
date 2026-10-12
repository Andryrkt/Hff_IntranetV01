# Contribuer à HFF Intranet

Ce document décrit comment nommer les branches et faire arriver son travail dans `main`.

## Branches principales

| Branche | Rôle |
|---|---|
| `main` | Code en production. On n'y pousse jamais directement : tout passe par une pull request. |
| `dev` | Intégration et tests avant la mise en production. |

## Nommer une branche

Format : `type/description-courte`, en minuscules, avec des tirets.

| Type | Usage | Exemple |
|---|---|---|
| `feature/` | Nouvelle fonctionnalité | `feature/ddp-neg`, `feature/profil-user` |
| `fix/` | Correction de bug | `fix/dom-calcul-indemnite` |
| `hotfix/` | Correction urgente en production, créée depuis `main` | `hotfix/blocage-soumission-bl` |
| `refactor/` | Réorganisation du code sans changement fonctionnel | `refactor/bdd-dit` |
| `docs/` | Documentation | `docs/contributing` |

À éviter : les noms sans type (`ddp`, `dit_fix`), les underscores, les noms en double (`devis_neg_fix` et `devi_neg_ips_fix`).

## Flux de travail

1. Partir d'une `main` à jour :
   ```bash
   git switch main
   git pull
   git switch -c feature/ma-fonctionnalite
   ```
2. Faire des commits courts avec un message clair. Format conseillé : `type: description`, par exemple `fix: blocage de soumission si montant négatif`.
3. Récupérer régulièrement les changements de `main` pour éviter les gros conflits :
   ```bash
   git fetch origin
   git merge origin/main
   ```
4. Pousser la branche et ouvrir une pull request vers `main` :
   ```bash
   git push -u origin feature/ma-fonctionnalite
   ```
5. Une fois la pull request fusionnée, supprimer la branche (sur GitHub et en local) :
   ```bash
   git switch main
   git pull
   git branch -d feature/ma-fonctionnalite
   git fetch --prune
   ```

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
