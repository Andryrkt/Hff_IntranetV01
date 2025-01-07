// document.addEventListener("DOMContentLoaded", function () {
//   const toutLivrerBtn = document.querySelector("#tout-livre");
//   const partiellementLivreBtn = document.querySelector("#partiellement-livre");
//   const partiellementDispoBtn = document.querySelector("#partiellement-dispo");
//   const completNonLivreBtn = document.querySelector("#complet-non-livre");
//   const backOrderBtn = document.querySelector("#back-order");
//   const toutAfficherBtn = document.querySelector("#tout-afficher");

//   toutLivrerBtn.addEventListener("click", () => {

//   });
// });

document.addEventListener("DOMContentLoaded", function () {
  const buttons = {
    "tout-livre": "tout-livre",
    "partiellement-livre": "partiellement-livre",
    "partiellement-dispo": "partiellement-dispo",
    "complet-non-livre": "complet-non-livre",
    "back-order": "back-order",
    "tout-afficher": null, // Tout afficher n'a pas de classe spécifique
  };

  // Ajoute un gestionnaire d'événement pour chaque bouton
  for (const [buttonId, filterClass] of Object.entries(buttons)) {
    const button = document.getElementById(buttonId);
    if (button) {
      button.addEventListener("click", () => filterRowsByColumn(filterClass));
    }
  }

  function filterRowsByColumn(filterClass) {
    const rows = document.querySelectorAll("table tbody tr");

    rows.forEach((row) => {
      let hasMatchingCell = false;

      // Parcourt toutes les cellules de la ligne
      const cells = row.querySelectorAll("td");
      cells.forEach((cell) => {
        const links = cell.querySelectorAll("a"); // Tous les liens dans la cellule

        if (!filterClass) {
          // Si "Tout afficher", montre toutes les lignes et cellules
          links.forEach((link) => (link.style.display = ""));
          hasMatchingCell = true; // La ligne reste visible
        } else {
          // Filtre par classe
          let cellMatches = false;
          links.forEach((link) => {
            if (link.classList.contains(filterClass)) {
              link.style.display = ""; // Affiche les liens correspondant
              cellMatches = true;
            } else {
              link.style.display = "none"; // Cache les liens non correspondants
            }
          });

          if (cellMatches) {
            hasMatchingCell = true; // Marque la ligne comme ayant une correspondance
          }
        }
      });

      // Masque ou affiche la ligne entière
      row.style.display = hasMatchingCell ? "" : "none";
    });
  }
});
