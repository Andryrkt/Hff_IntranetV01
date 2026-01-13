import { AutoComplete } from "../../utils/AutoComplete.js";
import { FetchManager } from "../../api/FetchManager.js";
const fetchManager = new FetchManager();

/**===================================================
 * Autocomplete champ FOURNISSEUR
 *====================================================*/
const fournisseurInput = document.querySelector("#bon_apayer_fournisseur");

async function fetchFournisseurs() {
  return await fetchManager.get("api/numero-libelle-fournisseur");
}

function displayFournisseur(item) {
  return `${item.num_fournisseur} - ${item.nom_fournisseur}`;
}

function onSelectNumFournisseur(item) {
  fournisseurInput.value = `${item.num_fournisseur} - ${item.nom_fournisseur}`;
}

new AutoComplete({
  inputElement: fournisseurInput,
  suggestionContainer: document.querySelector("#suggestion-fournisseur"),
  loaderElement: document.querySelector("#loader-fournisseur"),
  debounceDelay: 300, // Délai en ms
  fetchDataCallback: fetchFournisseurs,
  displayItemCallback: displayFournisseur,
  onSelectCallback: onSelectNumFournisseur,
});
