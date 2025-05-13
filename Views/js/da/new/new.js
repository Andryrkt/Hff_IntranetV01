import { displayOverlay } from "../../utils/spinnerUtils";
import { ajouterUneLigne } from "./dal";

document.addEventListener("DOMContentLoaded", function () {
  // localStorage.setItem('index', 0);
  initializeIndexFromExistingLines();

  document
    .getElementById("add-child")
    .addEventListener("click", ajouterUneLigne);

  document.getElementById("myForm").addEventListener("submit", function (e) {
    e.preventDefault();
    if (!document.getElementById("children-container").hasChildNodes()) {
      alert("Vous devez au moins ajouter une ligne de DA!");
    } else {
      document.getElementById("child-prototype").remove();
      document.getElementById("myForm").submit();
    }
  });
});

window.addEventListener("load", () => {
  displayOverlay(false);
});

function initializeIndexFromExistingLines() {
  let maxIndex = 0;
  document.querySelectorAll("[id^='demande_appro_form_DAL_']").forEach((el) => {
    let match = el.id.match(/demande_appro_form_DAL_(\d+)$/);
    if (match) {
      let index = parseInt(match[1]);
      if (!isNaN(index) && index > maxIndex) {
        maxIndex = index;
      }
    }
  });
  localStorage.setItem("index", maxIndex);
}
