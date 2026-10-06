# 🐞 Résolution de bug — [Titre du bug]

## 📌 Informations générales

- **ID / Référence** : BUG-NGP-001
- **Date** : 2026-04-23
- **Auteur** : Menja
- **Environnement** : all
- **Version concernée** : all

---

## 🧩 Description du problème

Lors de la connexion, un utilisateur qui n'est associé à aucun profil se connecte mais un message d'erreur apparait.

---

## 🔁 Étapes pour reproduire

1. Aller sur `/login`
2. Entrer des identifiants valides
3. Cliquer sur "Se connecter"

---

## ❗ Résultat observé

- Message :

```bash
Aucun profil trouvé pour l'utilisateur : [username]. Veuillez contacter le support informatique.
```

---

## ✅ Résultat attendu

- L’utilisateur est connecté
- Redirection vers l'accueil `/`

---

## 🔍 Analyse / Cause racine

Ceci se produit lorsque l'utilisateur n'est associé à aucun profil. (cf. table `users_profils`)

---

## 🛠️ Solution appliquée

Associer l'utilisateur à son profil.

### Solution dans l'interface

1. Se connecter en tant qu' ADMIN ou SUPER-ADMIN
2. Cliquer sur "Administrateur" dans le menu de droite
3. Puis cliquer sur "Utilisateurs" situé dans "Accès & Sécurité"
4. Rechercher l'utilisateur concerné en tapant son matricule ou nom dans le formulaire de recherche
5. Cliquer sur l'ellipse à gauche de la ligne de l'utilisateur concerné
6. Cliquer sur "Modifier"
7. Associer l'utilisateur à son profil dans le champ "Profils"
8. Cliquer sur "Enregistrer"

### Solution dans la base de données

Avant d'exécuter cette commande, il faut s'assurer que l'utilisateur et le profil existent, en éxecutant tout simplement la partie après le `SELECT` (qui sert à recupérer l'ID de l'utilisateur et du profil).

```sql
INSERT into users_profils
(user_id, profil_id)
SELECT u.id, p.id from users u, profil p
where u.nom_utilisateur ='[username]' and p.designation_profil='[profil_name]'
```
