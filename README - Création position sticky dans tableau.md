# Guide : Création d'une Position Sticky pour les Tableaux

Ce guide explique comment rendre un tableau **sticky** avec un en-tête qui reste visible lors du scroll, tout en prenant en compte les éléments fixes au-dessus comme la **navbar** ou le **fil d'Ariane**.

---

## 1. Regrouper le contenu sticky

Tout ce qui se trouve entre le **fil d'Ariane** et le **tableau** doit être regroupé dans un `div` avec la classe `sticky-header-titre`.

```html
<div class="sticky-header-titre">
  <div class="container"></div>
</div>
```

---

## 2. Ajouter la classe sticky au tableau

Ajoutez la classe `table-sticky` à vos tableaux pour qu'ils soient pris en compte par le script :

```html
<table class="table table-sticky">
  <thead>
    <tr>
      <th>Colonne 1</th>
      <th>Colonne 2</th>
      <!-- ... -->
    </tr>
  </thead>
  <tbody>
    <!-- Données du tableau -->
  </tbody>
</table>
```

Remarques :

- Le <thead> peut contenir plusieurs lignes, le script gère la hauteur cumulée automatiquement.
- Évitez de mettre padding-top sur <tbody>, cela ne fonctionne pas correctement.

---

## 3. Ajouter le script JavaScript

Incluez le script qui gère le sticky dans le bloc `javascript` de votre template :

```html
{% block javascript %}
<script src="{{ App.base_path }}/Views/js/utils/positionSticky.js"></script>
{% endblock %}
```

**⚠️ Point essentiel à retenir :**

Dans le formulaire de recherche, l’élément d’accordéon doit impérativement avoir pour identifiant **`formAccordion`**.  
Sans cela, certaines fonctionnalités risquent de ne pas fonctionner correctement.

Exemple correct :

```html
<div class="accordion" id="formAccordion"></div>
```

Fonctionnalités du script :

- Calcule automatiquement la hauteur cumulée des éléments fixes au-dessus (navbar, fil d’Ariane, header sticky).
- Positionne chaque ligne du <thead> en sticky avec top ajusté.
- Décale le tableau avec margin-top pour que le <tbody> ne soit pas caché.
- Compatible avec plusieurs lignes d’en-tête et plusieurs tableaux sur la page.

---

## 4. Exemple complet

```html
<div class="sticky-header-titre">
  <div class="container">
    {% include "/da/shared/listeDA/_formulaireRecherche.html.twig" %}
  </div>
</div>

<table class="table table-sticky">
  <thead>
    <tr>
      <th>Nom</th>
      <th>Prénom</th>
      <th>Email</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>Dupont</td>
      <td>Jean</td>
      <td>jean.dupont@mail.com</td>
    </tr>
    <tr>
      <td>Martin</td>
      <td>Claire</td>
      <td>claire.martin@mail.com</td>
    </tr>
    <!-- autres lignes -->
  </tbody>
</table>

{% block javascript %}
<script src="{{ App.base_path }}/Views/js/utils/positionSticky.js"></script>
{% endblock %}
```

---

## 5. Visualisation ASCII (hiérarchie)

```csharp
[Navbar]           ← position fixe
[Fil d'Ariane]     ← position fixe
[Sticky Header]    ← sticky-header-titre
[Table Thead]      ← sticky
[Table Tbody]      ← contenu scrollable
```

---

Avec ces étapes, vos tableaux auront un **en-tête sticky** parfaitement fonctionnel, même avec plusieurs lignes ou des éléments fixes au-dessus.
