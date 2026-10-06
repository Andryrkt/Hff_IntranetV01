# Composant Spinner Réutilisable

## Vue d'ensemble

Le composant spinner est un élément de chargement réutilisable qui peut être utilisé dans toute l'application pour indiquer qu'une opération est en cours.

## Installation

### 1. Inclure les fichiers CSS et JavaScript

```html
<!-- Dans votre template de base -->
<link rel="stylesheet" href="{{ asset('css/components/spinner.css') }}">
<script src="{{ asset('js/components/spinner.js') }}"></script>
```

### 2. Utiliser le composant Twig

```twig
{% include 'components/spinner.html.twig' with {
    'id': 'mon-spinner',
    'size': 'medium',
    'color': 'primary',
    'hidden': true
} %}
```

## Paramètres du composant Twig

| Paramètre | Type | Défaut | Description |
|-----------|------|--------|-------------|
| `id` | string | 'spinner' | ID unique du spinner |
| `class` | string | '' | Classes CSS additionnelles |
| `size` | string | 'medium' | Taille : 'small', 'medium', 'large' |
| `color` | string | 'primary' | Couleur : 'primary', 'secondary', 'success', 'warning', 'danger', 'info' |
| `hidden` | boolean | true | Si le spinner doit être caché par défaut |

## Utilisation JavaScript

### Méthodes de base

```javascript
// Afficher un spinner
SpinnerUtils.show('mon-spinner');

// Masquer un spinner
SpinnerUtils.hide('mon-spinner');

// Basculer l'état d'un spinner
SpinnerUtils.toggle('mon-spinner');
```

### Gestion du contenu

```javascript
// Afficher le spinner et masquer le contenu
SpinnerUtils.show('mon-spinner', {
    hideContent: true,
    contentSelector: '#mon-contenu'
});

// Masquer le spinner et afficher le contenu
SpinnerUtils.hide('mon-spinner', {
    showContent: true,
    contentSelector: '#mon-contenu'
});
```

### Spinner overlay plein écran

```javascript
// Créer un overlay
SpinnerUtils.createOverlay('overlay-spinner', {
    message: 'Chargement...',
    color: 'primary',
    size: 'large'
});

// Supprimer l'overlay
SpinnerUtils.removeOverlay('overlay-spinner');
```

### Gestion automatique avec AJAX

```javascript
async function loadData() {
    const ajaxFunction = async () => {
        // Votre logique AJAX ici
        const response = await fetch('/api/data');
        return response.json();
    };
    
    const result = await SpinnerUtils.withSpinner('mon-spinner', ajaxFunction, {
        hideContent: true,
        contentSelector: '#contenu',
        showContent: true
    });
    
    return result;
}
```

## Exemples d'utilisation

### 1. Spinner basique

```twig
{% include 'components/spinner.html.twig' with {
    'id': 'spinner-basic'
} %}
```

### 2. Spinner avec différentes tailles

```twig
<!-- Petit -->
{% include 'components/spinner.html.twig' with {
    'id': 'spinner-small',
    'size': 'small'
} %}

<!-- Grand -->
{% include 'components/spinner.html.twig' with {
    'id': 'spinner-large',
    'size': 'large'
} %}
```

### 3. Spinner avec différentes couleurs

```twig
<!-- Succès -->
{% include 'components/spinner.html.twig' with {
    'id': 'spinner-success',
    'color': 'success'
} %}

<!-- Danger -->
{% include 'components/spinner.html.twig' with {
    'id': 'spinner-danger',
    'color': 'danger'
} %}
```

### 4. Spinner dans un conteneur

```twig
<div class="spinner-container">
    <div id="mon-contenu">
        <!-- Contenu qui sera masqué -->
    </div>
    {% include 'components/spinner.html.twig' with {
        'id': 'spinner-container',
        'class': 'spinner-absolute'
    } %}
</div>
```

### 5. Spinner dans un formulaire (cas d'usage original)

```twig
<div class="spinner-container">
    <select id="agence-select" onchange="loadServices()">
        <option value="">Sélectionner une agence</option>
    </select>
    
    {% include 'components/spinner.html.twig' with {
        'id': 'spinner-service-emetteur',
        'size': 'small',
        'class': 'spinner-absolute'
    } %}
</div>

<div id="service-container-emetteur">
    <select id="service-select">
        <option value="">Sélectionner un service</option>
    </select>
</div>

<script>
function loadServices() {
    const agenceId = document.getElementById('agence-select').value;
    
    if (!agenceId) return;
    
    SpinnerUtils.show('spinner-service-emetteur');
    
    // Requête AJAX pour charger les services
    fetch(`/api/services/${agenceId}`)
        .then(response => response.json())
        .then(services => {
            const select = document.getElementById('service-select');
            select.innerHTML = '<option value="">Sélectionner un service</option>';
            
            services.forEach(service => {
                const option = document.createElement('option');
                option.value = service.id;
                option.textContent = service.name;
                select.appendChild(option);
            });
        })
        .finally(() => {
            SpinnerUtils.hide('spinner-service-emetteur');
        });
}
</script>
```

## Classes CSS disponibles

### Tailles
- `.spinner-small` - Spinner de 20px
- `.spinner-medium` - Spinner de 40px (défaut)
- `.spinner-large` - Spinner de 60px

### Couleurs
- `.spinner-primary` - Bleu (#007bff)
- `.spinner-secondary` - Gris (#6c757d)
- `.spinner-success` - Vert (#28a745)
- `.spinner-warning` - Jaune (#ffc107)
- `.spinner-danger` - Rouge (#dc3545)
- `.spinner-info` - Cyan (#17a2b8)

### Utilitaires
- `.spinner-center` - Centre le spinner
- `.spinner-absolute` - Position absolue
- `.spinner-container` - Conteneur avec position relative
- `.spinner-overlay` - Overlay plein écran

## Bonnes pratiques

1. **Utilisez des IDs uniques** pour chaque spinner
2. **Masquez le contenu** pendant le chargement pour éviter les interactions
3. **Utilisez les overlays** pour les opérations critiques
4. **Gérez les erreurs** dans vos fonctions AJAX
5. **Testez l'accessibilité** avec les lecteurs d'écran

## Personnalisation

Vous pouvez personnaliser l'apparence en modifiant le fichier `spinner.css` ou en ajoutant vos propres classes CSS.

```css
/* Exemple de personnalisation */
.mon-spinner-custom {
    width: 50px;
    height: 50px;
}

.mon-spinner-custom div {
    background: #ff6b6b;
}
```
