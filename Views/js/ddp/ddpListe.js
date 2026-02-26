import { AutoComplete } from "../utils/AutoComplete.js";
import { FetchManager } from "../api/FetchManager.js";
import { filterServiceByAgence } from "../utils/agenceService/filterServiceByAgence.js";

document.addEventListener("DOMContentLoaded", function () {
  const fetchManager = new FetchManager();
  /** ========================================================================
   * Evènement sur agence et service
   *============================================================================*/
  filterServiceByAgence({
    agenceSelector: "#ddp_search_Agence",
    serviceSelector: "#ddp_search_service",
  });

  /**===================================================
   * Autocomplete champ FOURNISSEUR
   *====================================================*/
  const fournisseurInput = document.querySelector("#ddp_search_fournisseur");

  async function fetchFournisseurs() {
    return await fetchManager.get("api/numero-libelle-fournisseur");
  }

  function displayFournisseur(item) {
    return `${item.num_fournisseur} - ${item.nom_fournisseur}`;
  }

  function onSelectFournisseur(item) {
    fournisseurInput.value = `${item.num_fournisseur}-${item.nom_fournisseur}`;
  }

  new AutoComplete({
    inputElement: fournisseurInput,
    suggestionContainer: document.querySelector("#suggestion-fournisseur"),
    loaderElement: document.querySelector("#loader-fournisseur"), // Ajout du loader
    debounceDelay: 300, // Délai en ms
    fetchDataCallback: fetchFournisseurs,
    displayItemCallback: displayFournisseur,
    onSelectCallback: onSelectFournisseur,
  });
});
