# 🐞 Résolution de bug — Cache des proxy

## 📌 Informations générales

- **ID / Référence** : BUG-001
- **Date** : 2026-04-23
- **Auteur** : Menja
- **Environnement** : all
- **Version concernée** : all

---

## 🧩 Description du problème

Un warning se produit lors de l'envoi de requête POST sur la première page affichée (en l'occurence la page de connexion).

---

## 🔁 Étapes pour reproduire

1. Aller sur `/login`
2. Entrer des identifiants valides
3. Cliquer sur "Se connecter"

---

## ❗ Résultat observé

- Warning
- Message :

```php
Warning: require(C:\wamp64\www\Hffintranet\src\Doctrine/../../var/cache/proxies\__CG__AppEntityadminPersonnel.php): failed to open stream: No such file or directory in C:\wamp64\www\Hffintranet\vendor\doctrine\common\src\Proxy\AbstractProxyFactory.php on line 199
```

---

## ✅ Résultat attendu

- L’utilisateur est connecté
- Redirection vers la page d'accueil

---

## 🔍 Analyse / Cause racine

La cause racine est que le proxy est obsolète. Il faut le régénérer.

---

## 🛠️ Solution appliquée

Executer la ligne de commande suivante sur PowerShell:

```shell
vendor/bin/doctrine orm:generate-proxies
```
