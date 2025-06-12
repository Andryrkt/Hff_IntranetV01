import { afficherFichier, couleurDefondClick } from "./utils.js";
import { FetchManager } from "../api/FetchManager.js";

// Instanciation de FetchManager avec la base URL
const fetchManager = new FetchManager();

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
      const chemin = this.dataset.chemin;
      console.log(numeroDoc, nomDoc, numeroVersion, chemin);
      afficherFichier(chemin);

      // const url = `dw-chemin-fetch/${numeroDoc}/${nomDoc}/${numeroVersion}`;
      // fetchManager
      //   .get(url)
      //   .then((data) => {
      //     console.log(data);
      //     afficherFichier(data.chemin.chemin);
      //   })
      //   .catch((error) => {
      //     console.error("Erreur lors de la récupération des données:", error);
      //   });
    });
  });
});
