import { configAgenceService } from "../../dit/config/listDitConfig";
import { handleAgenceChange } from "../../dit/fonctionUtils/fonctionListDit";
import {
  toUppercase,
  limitInputLength,
  allowOnlyNumbers,
} from "../../utils/inputUtils";
import { displayOverlay } from "../../utils/spinnerUtils";
import { FetchManager } from "../../api/FetchManager";
import { baseUrl } from "../../utils/config";
import { handleRowClick } from "../proposition/dalr";

const fetchManager = new FetchManager();

document.addEventListener("DOMContentLoaded", function () {
  /**===========================================================================
   * Configuration des agences et services
   **============================================================================*/

  // Attachement des événements pour les agences
  configAgenceService.emetteur.agenceInput.addEventListener("change", () =>
    handleAgenceChange("emetteur")
  );
  configAgenceService.debiteur.agenceInput.addEventListener("change", () =>
    handleAgenceChange("debiteur")
  );

  /**====================================================
   * MISE EN MAJUSCULE
   *=================================================*/
  const numDitSearchInput = document.querySelector("#dit_search_numDit");
  numDitSearchInput.addEventListener("input", () => {
    toUppercase(numDitSearchInput);
    limitInputLength(numDitSearchInput, 11);
  });

  /**===========================================
   * SEULEMENT DES CHIFFRES
   *============================================*/
  const numOrSearchInput = document.querySelector("#dit_search_numOr");
  const numDevisSearchInput = document.querySelector("#dit_search_numDevis");
  numOrSearchInput.addEventListener("input", () => {
    allowOnlyNumbers(numOrSearchInput);
    limitInputLength(numOrSearchInput, 8);
  });
  numDevisSearchInput.addEventListener("input", () => {
    allowOnlyNumbers(numDevisSearchInput);
    limitInputLength(numDevisSearchInput, 8);
  });
  allowOnlyNumbers(numDevisSearchInput);

  /**===========================================
   * EVENEMENT SUR LES CHECKBOX
   *============================================*/
  const checkboxes = document.querySelectorAll(".checkbox");
  checkboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", function () {
      checkboxes.forEach((cb) => {
        hoverTheTableRow(cb, cb === this);
      });
    });
  });

  function hoverTheTableRow(checkbox, bool) {
    let row = checkbox.parentElement.parentElement;
    checkbox.checked = bool;
    if (bool) {
      row.classList.add("table-active");
    } else {
      row.classList.remove("table-active");
    }
  }

  /**===========================================
   * EVENEMENT SUR LES LIGNES DU TABLEAU
   *============================================*/
  document.querySelectorAll('tr[role="button"]').forEach((row) => {
    row.addEventListener("click", handleRowClick);
  });

  /**===========================================
   * EVENEMENT SUR LE BOUTON SUIVANT
   *============================================*/
  const suivant = document.getElementById("suivant");
  suivant.addEventListener("click", function () {
    let checkedValue = [...checkboxes].find((cb) => cb.checked)?.value || "";
    if (checkedValue === "") {
      alert("Veuillez sélectionner un DIT");
    } else {
      /* const endpoint = "api/recup-statut-da";
      const data = {
        id: checkedValue,
      };

      fetchManager.post(endpoint, data).then((statut) => {
        console.log(statut);
        const statutNormalisé = normaliserApostrophes(statut.statut);

        let url;
        if (statutNormalisé !== null) {
          url = `${baseUrl}/demande-appro/detail/${checkedValue}`;
        } else {
          url = `${baseUrl}/demande-appro/new/${checkedValue}`;
        }
        }); */
      displayOverlay(true);
      let url = suivant
        .getAttribute("data-uri")
        .replace("__id__", checkedValue);
      window.location.href = url;
    }
  });
});

function normaliserApostrophes(str) {
  if (str) {
    return str.replace(/[’‘]/g, "'"); // remplace les apostrophes typographiques par '
  } else {
    return null;
  }
}

window.addEventListener("load", () => {
  displayOverlay(false);
});
