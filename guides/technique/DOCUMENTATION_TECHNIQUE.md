# Documentation Technique - Guide d'utilisation

## 🎯 Fonctionnalité

Le système de documentation technique permet d'afficher et de naviguer dans tous les guides du projet de manière organisée et intuitive.

## 🔗 Accès

- **URL principale** : `/Hffintranet/documentation`
- **Navigation** : Bouton "Documentation Technique" dans la barre de navigation (visible pour les utilisateurs avec le rôle 7)

## 📁 Structure des guides

### Configuration
- **Chemin** : `guides/configuration/`
- **Contenu** : Guides de configuration LDAP, sécurité, etc.
- **Fichiers** : `ldap_config.md`, `SECURITE_CONFIGURATION.md`, etc.

### Fonctionnel
- **Chemin** : `guides/fonctionnel/`
- **Contenu** : Documentation métier et fonctionnelle
- **Fichiers** : `bugAResoudre.md`, `processus.md`, etc.

### Migrations
- **Chemin** : `guides/migrations/`
- **Contenu** : Guides des migrations Doctrine
- **Fichiers** : `GUIDE_MIGRATIONS_DOCTRINE.md`

### Technique
- **Chemin** : `guides/technique/`
- **Contenu** : Documentation technique et architecture
- **Fichiers** : `architecture_recente.md`, etc.

## 🎨 Interface utilisateur

### Page d'accueil (`/documentation`)
- **Vue d'ensemble** : Cartes pour chaque catégorie de documentation
- **Statistiques** : Nombre de documents par catégorie
- **Navigation** : Liens directs vers chaque catégorie
- **Liens rapides** : Accès direct aux guides les plus importants

### Page de catégorie (`/documentation/{category}`)
- **Liste des fichiers** : Tous les documents de la catégorie
- **Métadonnées** : Taille et date de modification
- **Navigation** : Fil d'Ariane et bouton retour

### Page de document (`/documentation/{category}/{file}`)
- **Affichage** : Contenu markdown rendu en HTML
- **Navigation** : Fil d'Ariane complet
- **Formatage** : Syntaxe highlightée pour le code

## 🔧 Fonctionnalités techniques

### Parsing Markdown
- **Headers** : H1, H2, H3 avec styles appropriés
- **Code** : Blocs de code avec syntaxe highlightée
- **Liens** : Liens internes et externes
- **Formatage** : Gras, italique, listes, tableaux

### Navigation
- **Fil d'Ariane** : Navigation hiérarchique
- **Boutons retour** : Retour à la catégorie ou à l'accueil
- **Liens contextuels** : Liens vers les guides connexes

### Responsive Design
- **Mobile** : Interface adaptée aux petits écrans
- **Tablette** : Mise en page optimisée
- **Desktop** : Interface complète avec grilles

## 📝 Ajout de nouveaux guides

### 1. Créer le fichier
```bash
# Dans le dossier approprié
touch guides/category/nouveau_guide.md
```

### 2. Structure du fichier
```markdown
# Titre du Guide

Description du guide...

## Section 1
Contenu...

## Section 2
Contenu...
```

### 3. Accessibilité
- Le fichier sera automatiquement détecté
- Accessible via l'interface web
- Inclus dans les statistiques

## 🎨 Personnalisation

### Styles CSS
- **Fichier** : Intégré dans les templates Twig
- **Classes** : Préfixe `.documentation-`
- **Thème** : Cohérent avec l'interface existante

### Templates Twig
- **Index** : `Views/templates/documentation/index.html.twig`
- **Catégorie** : `Views/templates/documentation/category.html.twig`
- **Document** : `Views/templates/documentation/file.html.twig`

## 🚀 Utilisation

1. **Accéder** : Cliquer sur "Documentation Technique" dans la navigation
2. **Explorer** : Parcourir les catégories disponibles
3. **Lire** : Consulter les guides dans l'interface web
4. **Naviguer** : Utiliser les liens et le fil d'Ariane

## 🔍 Dépannage

### Problèmes courants
- **Fichier non trouvé** : Vérifier le chemin et l'extension `.md`
- **Contenu non affiché** : Vérifier la syntaxe markdown
- **Navigation cassée** : Vérifier les routes Symfony

### Logs
- **Erreurs** : Consulter les logs PHP
- **Routes** : Vérifier la configuration de routing
- **Templates** : Vérifier les fichiers Twig

## 📈 Améliorations futures

- **Recherche** : Fonction de recherche dans les guides
- **Favoris** : Marquer les guides importants
- **Historique** : Suivi des consultations
- **Commentaires** : Système de commentaires sur les guides
- **Versioning** : Gestion des versions des guides
