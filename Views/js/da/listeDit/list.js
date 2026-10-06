import {
  toUppercase,
  limitInputLength,
  allowOnlyNumbers,
} from "../../utils/inputUtils";
import { displayOverlay } from "../../utils/ui/overlay";
import { handleRowClick } from "../propositionAvecDit/dalr";
import { filterServiceByAgence } from "../../utils/agenceService/filterServiceByAgence.js";

document.addEventListener("DOMContentLoaded", function () {
  /**===========================================================================
   * Configuration des agences et services
   **============================================================================*/

  filterServiceByAgence({
    agenceSelector: "#dit_search_agenceEmetteur",
    serviceSelector: "#dit_search_serviceEmetteur",
  });

  filterServiceByAgence({
    agenceSelector: "#dit_search_agenceDebiteur",
    serviceSelector: "#dit_search_serviceDebiteur",
  });

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
      Swal.fire({
        icon: "warning",
        title: "Attention !",
        text: "Veuillez sélectionner un DIT avant de continuer.",
      });
    } else {
      displayOverlay(true);
      let url = suivant
        .getAttribute("data-uri")
        .replace("__id__", checkedValue);
      window.location.href = url;
    }
  });
});
