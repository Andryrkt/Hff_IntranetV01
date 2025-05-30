import { displayOverlay } from '../../utils/spinnerUtils';
import { mergeCellsRecursiveTable } from './tableHandler';
import { AutoComplete } from '../../utils/AutoComplete.js';
import { FetchManager } from '../../api/FetchManager.js';
const fetchManager = new FetchManager();

window.addEventListener('load', () => {
  displayOverlay(false);
});

document.addEventListener('DOMContentLoaded', function () {
  /*  1ᵉʳ appel : colonnes 0-3 selon le pivot que vous aviez déjà.
   *  2ᵉ appel : colonnes 4-5 selon la colonne 4.
   */
  mergeCellsRecursiveTable([
    { pivotIndex: 2, columns: [0, 1, 2, 3], insertSeparator: true },
    { pivotIndex: 4, columns: [4, 5], insertSeparator: true },
    { pivotIndex: 6, columns: [6], insertSeparator: true },
  ]);
});

/** =========================================================*/
async function fetchFournisseurs() {
  return await fetchManager.get('api/numero-libelle-fournisseur');
}

function displayFournisseur(item) {
  return `${item.num_fournisseur} - ${item.nom_fournisseur}`;
}

/**===================================================
 * Autocomplete champ numero FOURNISSEUR
 *====================================================*/
const numFournisseurInput = document.querySelector('#cde_frn_list_numFrn');

function onSelectNumFournisseur(item) {
  numFournisseurInput.value = `${item.num_fournisseur}`;
}

new AutoComplete({
  inputElement: numFournisseurInput,
  suggestionContainer: document.querySelector('#suggestion-num-fournisseur'),
  loaderElement: document.querySelector('#loader-num-fournisseur'), // Ajout du loader
  debounceDelay: 300, // Délai en ms
  fetchDataCallback: fetchFournisseurs,
  displayItemCallback: displayFournisseur,
  onSelectCallback: onSelectNumFournisseur,
});

/**===================================================
 * Autocomplete champ nom FOURNISSEUR
 *====================================================*/
const nomFournisseurInput = document.querySelector('#cde_frn_list_frn');

function onSelectNomFournisseur(item) {
  nomFournisseurInput.value = `${item.nom_fournisseur}`;
}

new AutoComplete({
  inputElement: nomFournisseurInput,
  suggestionContainer: document.querySelector('#suggestion-nom-fournisseur'),
  loaderElement: document.querySelector('#loader-nom-fournisseur'), // Ajout du loader
  debounceDelay: 300, // Délai en ms
  fetchDataCallback: fetchFournisseurs,
  displayItemCallback: displayFournisseur,
  onSelectCallback: onSelectNomFournisseur,
});

function adjustStickyPositions() {
  const stickyHead = document.querySelector('.sticky-header-titre');
  const tableHeader = document.querySelector('.table-plein-ecran thead tr');

  if (tableHeader) {
    tableHeader.style.top = `${stickyHead.offsetHeight}px`;
  }
}

// Ajoutez un écouteur d'événements pour surveiller l'ouverture/fermeture de l'accordéon
document
  .querySelectorAll('#formAccordion .accordion-button')
  .forEach((button) => {
    button.addEventListener('click', () => {
      setTimeout(adjustStickyPositions, 300); // Délai pour permettre l'animation de l'accordéon
    });
  });

// Exécutez le script une fois au chargement de la page
window.addEventListener('DOMContentLoaded', adjustStickyPositions);
window.addEventListener('resize', adjustStickyPositions);
