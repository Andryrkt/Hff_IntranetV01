import { afficherFichier, couleurDefondClick } from "./utils.js";

document.addEventListener("DOMContentLoaded", function () {
  // Sélectionne toutes les lignes du premier tableau
  const rows = document.querySelectorAll(".clickable-row");
  rows.forEach(function (row) {
    row.addEventListener("click", function () {
      couleurDefondClick(row);
      // Récupère le numéro DIT de la ligne cliquée
      const chemin = this.dataset.chemin;
      afficherFichier(chemin);
    });
  });
});
