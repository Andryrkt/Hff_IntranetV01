document.addEventListener("DOMContentLoaded", function () {
  // Sélection de tous les inputs de recherche dans le DOM
  const searchInputs = document.querySelectorAll(".js-search-input");

  // Cache pour stocker les références des cellules par label
  // Structure: Map { label -> Map { row -> cell } }
  // Cela évite de faire des querySelector répétés sur les mêmes éléments
  const cellCache = new Map();

  // Sélection de toutes les lignes du tableau une seule fois
  const rows = document.querySelectorAll("#tableBody tr");

  /**
   * Initialise le cache des cellules pour optimiser les recherches futures
   * Cette fonction parcourt chaque ligne du tableau et stocke les références
   * des cellules organisées par leur attribut data-label
   */
  function initializeCellCache() {
    // Pour chaque input de recherche, on crée une entrée dans le cache
    searchInputs.forEach((input) => {
      const label = input.dataset.label;
      const labelCache = new Map();

      // Pour chaque ligne, on trouve et stocke la cellule correspondante
      rows.forEach((row) => {
        const cell = row.querySelector(`td[data-label="${label}"]`);
        if (cell) {
          // Stockage de la cellule avec le texte en minuscules pour comparaison
          labelCache.set(row, {
            element: cell,
            text: cell.textContent.toLowerCase(),
          });
        }
      });

      // Ajout de toutes les cellules de ce label dans le cache principal
      cellCache.set(label, labelCache);
    });
  }

  /**
   * Fonction principale qui applique tous les filtres de recherche
   * Elle parcourt chaque ligne et vérifie si elle correspond à TOUS les critères
   * Approche: Logique AND - toutes les conditions doivent être remplies
   */
  function applyAllFilters() {
    // Collecte de tous les filtres actifs (non vides)
    // Structure: Array [ { label, filter } ]
    const activeFilters = [];

    searchInputs.forEach((input) => {
      const filter = input.value.toLowerCase().trim();
      if (filter !== "") {
        activeFilters.push({
          label: input.dataset.label,
          filter: filter,
        });
      }
    });

    // Si aucun filtre n'est actif, afficher toutes les lignes
    if (activeFilters.length === 0) {
      rows.forEach((row) => row.classList.remove("d-none"));
      return;
    }

    // Parcours de chaque ligne pour vérifier les critères
    rows.forEach((row) => {
      let shouldShow = true;

      // Vérification de chaque filtre actif
      // Si UN SEUL filtre ne correspond pas, la ligne est masquée
      for (const { label, filter } of activeFilters) {
        // Récupération de la cellule depuis le cache (pas de querySelector!)
        const labelCache = cellCache.get(label);
        const cachedCell = labelCache?.get(row);

        // Si la cellule n'existe pas ou ne contient pas le texte recherché
        if (!cachedCell || !cachedCell.text.includes(filter)) {
          shouldShow = false;
          break; // Optimisation: arrêt dès qu'un critère échoue
        }
      }

      // Application de la classe d-none selon le résultat
      // toggle(class, false) = ajoute la classe
      // toggle(class, true) = retire la classe
      row.classList.toggle("d-none", !shouldShow);
    });
  }

  // Initialisation du cache au chargement de la page
  initializeCellCache();

  // Ajout de l'écouteur d'événement sur chaque input de recherche
  // À chaque frappe (keyup), tous les filtres sont ré-appliqués
  searchInputs.forEach((input) => {
    input.addEventListener("keyup", applyAllFilters);
  });
});
