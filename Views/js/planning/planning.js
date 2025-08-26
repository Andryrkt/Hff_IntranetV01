import { filterRowsByColumn } from "../utils/filtre.js";

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
});
