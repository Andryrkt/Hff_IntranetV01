import { AutoComplete } from "../utils/AutoComplete.js";
import { FetchManager } from "../api/FetchManager.js";
const fetchManager = new FetchManager();

/** ========================================================
 * Autocomplete champ Matricule, nom et prénom
 *==========================================================*/
const champMatricule = document.querySelector("#demande_conge_matricule")

async function fetchFournisseurs() {
    return await fetchManager.get("rh/demande-de-conge/api/matricule-nom-prenom");
  }
  
  function displayFournisseur(item) {
    return `${item.matricule} - ${item.nomPrenoms}`;
  }

function onSelectNumFournisseur(item) {
    champMatricule.value = `${item.matricule}`;
}

new AutoComplete({
    inputElement: champMatricule,
    suggestionContainer: document.querySelector("#suggestion-matricule-nom-prenom"),
    loaderElement: document.querySelector("#loader-matricule-nom-prenom"), // Ajout du loader
    debounceDelay: 300, // Délai en ms
    fetchDataCallback: fetchFournisseurs,
    displayItemCallback: displayFournisseur,
    onSelectCallback: onSelectNumFournisseur,
  });