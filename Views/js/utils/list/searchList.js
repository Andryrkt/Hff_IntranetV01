/** RECHERCHE */
document.addEventListener("DOMContentLoaded", function () {
  const searchInputs = document.querySelectorAll(".js-search-input");

  // Fonction pour vérifier tous les filtres
  function applyAllFilters() {
    let rows = document.querySelectorAll("#tableBody tr");

    rows.forEach((row) => {
      let shouldShow = true;

      // Vérifier chaque input de recherche
      searchInputs.forEach((input) => {
        let label = input.dataset.label;
        let filter = input.value.toLowerCase().trim();

        // Si le filtre n'est pas vide, vérifier la correspondance
        if (filter !== "") {
          let cell = row.querySelector(`td[data-label="${label}"]`);
          if (cell) {
            let text = cell.textContent.toLowerCase();
            if (!text.includes(filter)) {
              shouldShow = false;
            }
          } else {
            shouldShow = false;
          }
        }
      });

      // Afficher ou masquer la ligne selon tous les critères
      row.classList.toggle("d-none", !shouldShow);
    });
  }

  // Ajouter l'écouteur sur chaque input
  searchInputs.forEach((input) => {
    input.addEventListener("keyup", applyAllFilters);
  });
});
