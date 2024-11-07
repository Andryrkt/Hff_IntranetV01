import { afficherFichier, couleurDefondClick } from "./utils.js";

document.addEventListener("DOMContentLoaded", function () {
  // Sélectionne toutes les lignes du premier tableau
  const rows = document.querySelectorAll(".clickable-row");
  rows.forEach(function (row) {
    row.addEventListener("click", function () {
      couleurDefondClick(row);
      // Récupère le numéro DIT de la ligne cliquée
      const numeroDoc = this.dataset.doc;
      const nomDoc = this.dataset.nomdoc;
      const numeroVersion = this.dataset.version;
console.log(numeroDoc);
console.log(nomDoc);
console.log(numeroVersion);



      const url = `/Hffintranet/dw-chemin-fetch/${numeroDoc}/${nomDoc}/${numeroVersion}`;
      fetch(url, {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
        },
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error("Erreur HTTP " + response.status);
          }
          return response.json();
        })
        .then((data) => {
          
          console.log(data.chemin.chemin);
          afficherFichier(data.chemin.chemin);
        })
        .catch((error) => {
          console.error("Erreur lors de la récupération des données:", error);
        });
    });
  });
});
