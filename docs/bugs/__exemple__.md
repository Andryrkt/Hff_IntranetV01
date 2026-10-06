# 🐞 Résolution de bug — [Titre du bug]

## 📌 Informations générales

- **ID / Référence** : BUG-001
- **Date** : 2026-04-23
- **Auteur** : [Nom]
- **Environnement** : (dev / staging / prod)
- **Version concernée** : v1.2.0

---

## 🧩 Description du problème

Décris clairement le bug.

> Exemple :
> Une erreur 500 se produit lors de la soumission du formulaire de login.

---

## 🔁 Étapes pour reproduire

1. Aller sur `/login`
2. Entrer des identifiants valides
3. Cliquer sur "Connexion"

---

## ❗ Résultat observé

- Erreur HTTP 500
- Message :

```bash
Call to undefined method App\Entity\User::getUsername()
```

---

## ✅ Résultat attendu

- L’utilisateur est connecté
- Redirection vers le dashboard

---

## 🔍 Analyse / Cause racine

Explique **pourquoi** le bug se produit.

> Exemple :
> La méthode `getUsername()` a été supprimée dans l'entité User, mais elle est encore utilisée dans le security provider.

---

## 🛠️ Solution appliquée

Décris précisément la correction.

```php
// Avant
$user->getUsername();

// Après
$user->getUserIdentifier();
```

---

## 🧪 Vérification

- [x] Test manuel OK
- [x] Tests automatisés passent
- [ ] Vérifié en staging
- [ ] Vérifié en production

---

## ⚠️ Effets de bord éventuels

- Impact possible sur le système d’authentification
- Vérifier les autres appels à `getUsername()`

---

## 📚 Références utiles

- Documentation Symfony Security
- Lien vers ticket Jira / GitLab
- Commit : `abc123`

---

## 💡 Notes complémentaires

Toute information utile pour le futur (optimisation, refacto à prévoir, etc.)
