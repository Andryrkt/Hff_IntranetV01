import { displayOverlay } from "../../utils/spinnerUtils";
import { mergeCellsRecursiveTable } from "./tableHandler";
import { AutoComplete } from "../../utils/AutoComplete.js";
import { FetchManager } from "../../api/FetchManager.js";
const fetchManager = new FetchManager();

window.addEventListener("load", () => {
  displayOverlay(false);
});

document.addEventListener("DOMContentLoaded", function () {
  /*  1ᵉʳ appel : colonnes 0-3 selon le pivot que vous aviez déjà.
   *  2ᵉ appel : colonnes 4-5 selon la colonne 4.
   */
  mergeCellsRecursiveTable([
    { pivotIndex: 3, columns: [0, 1, 2, 3, 4, 5], insertSeparator: true },
    { pivotIndex: 6, columns: [6, 7], insertSeparator: true },
    { pivotIndex: 8, columns: [8, 9], insertSeparator: true },
  ]);
});

/** =========================================================*/
async function fetchFournisseurs() {
  return await fetchManager.get("api/numero-libelle-fournisseur");
}

function displayFournisseur(item) {
  return `${item.num_fournisseur} - ${item.nom_fournisseur}`;
}

/**===================================================
 * Autocomplete champ numero FOURNISSEUR
 *====================================================*/
const numFournisseurInput = document.querySelector("#cde_frn_list_numFrn");

function onSelectNumFournisseur(item) {
  numFournisseurInput.value = `${item.num_fournisseur}`;
}

new AutoComplete({
  inputElement: numFournisseurInput,
  suggestionContainer: document.querySelector("#suggestion-num-fournisseur"),
  loaderElement: document.querySelector("#loader-num-fournisseur"), // Ajout du loader
  debounceDelay: 300, // Délai en ms
  fetchDataCallback: fetchFournisseurs,
  displayItemCallback: displayFournisseur,
  onSelectCallback: onSelectNumFournisseur,
});

/**===================================================
 * Autocomplete champ nom FOURNISSEUR
 *====================================================*/
const nomFournisseurInput = document.querySelector("#cde_frn_list_frn");

function onSelectNomFournisseur(item) {
  nomFournisseurInput.value = `${item.nom_fournisseur}`;
}

new AutoComplete({
  inputElement: nomFournisseurInput,
  suggestionContainer: document.querySelector("#suggestion-nom-fournisseur"),
  loaderElement: document.querySelector("#loader-nom-fournisseur"), // Ajout du loader
  debounceDelay: 300, // Délai en ms
  fetchDataCallback: fetchFournisseurs,
  displayItemCallback: displayFournisseur,
  onSelectCallback: onSelectNomFournisseur,
});

function adjustStickyPositions() {
  const stickyHead = document.querySelector(".sticky-header-titre");
  const tableHeader = document.querySelector(".table-plein-ecran thead tr");

  if (tableHeader) {
    tableHeader.style.top = `${stickyHead.offsetHeight}px`;
  }
}

// Ajoutez un écouteur d'événements pour surveiller l'ouverture/fermeture de l'accordéon
document
  .querySelectorAll("#formAccordion .accordion-button")
  .forEach((button) => {
    button.addEventListener("click", () => {
      setTimeout(adjustStickyPositions, 300); // Délai pour permettre l'animation de l'accordéon
    });
  });

// Exécutez le script une fois au chargement de la page
window.addEventListener("DOMContentLoaded", adjustStickyPositions);
window.addEventListener("resize", adjustStickyPositions);

/**=============================================================
 * Click droite sur le numero commande
 *===============================================================*/
const menu = document.getElementById("menuContextuelGlobal");
const hiddenInputCde = document.getElementById("da_soumission_commande_id");
const hiddenInputDa = document.getElementById("da_soumission_da_id");

document.addEventListener("contextmenu", function (event) {
  const targetCell = event.target.closest(".commande-cellule");
  if (!targetCell) return; // Ne fait rien si ce n’est pas une cellule cible

  event.preventDefault(); // Empêche le menu contextuel natif

  const commandeId = targetCell.dataset.commandeId;
  hiddenInputCde.value = commandeId;
  const numDa = targetCell.dataset.numDa;
  hiddenInputDa.value = numDa;

  menu.style.top = event.pageY + "px";
  menu.style.left = event.pageX + "px";
  menu.style.display = "block";
});

// Fermer le menu si clic ailleurs
document.addEventListener("click", function (event) {
  if (!menu.contains(event.target)) {
    menu.style.display = "none";
  }
});
