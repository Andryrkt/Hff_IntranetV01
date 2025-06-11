import { displayOverlay } from "../../utils/spinnerUtils";
import { mergeCellsTable } from "./tableHandler";
import { configAgenceService } from "../../dit/config/listDitConfig.js";
import { handleAgenceChange } from "../../dit/fonctionUtils/fonctionListDit.js";
import { allowOnlyNumbers } from "../../magasin/utils/inputUtils.js";

document.addEventListener("DOMContentLoaded", function () {
  const designations = document.querySelectorAll(".designation-btn");
  designations.forEach((designation) => {
    designation.addEventListener("click", function () {
      let numeroLigne = this.getAttribute("data-numero-ligne");
      localStorage.setItem("currentTab", numeroLigne);
    });
  });
  mergeCellsTable(0); // fusionne le tableau
});

window.addEventListener("load", () => {
  displayOverlay(false);
});

/**===========================================================================
 * Configuration des agences et services
 *===========================================================================*/

// Attachement des événements pour les agences
configAgenceService.emetteur.agenceInput.addEventListener("change", () =>
  handleAgenceChange("emetteur")
);

configAgenceService.debiteur.agenceInput.addEventListener("change", () =>
  handleAgenceChange("debiteur")
);

/**==================================================
 * valider seulement les chiffres
 ===================================================*/

const idMaterielInput = document.querySelector("#da_search_idMateriel");
idMaterielInput.addEventListener("input", () =>
  allowOnlyNumbers(idMaterielInput)
);
