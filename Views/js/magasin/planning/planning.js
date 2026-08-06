import { FetchManager } from "../../api/FetchManager.js";
import { AutoComplete } from "../../utils/AutoComplete.js";

document.addEventListener("DOMContentLoaded", function () {
  const fetchManager = new FetchManager();

  async function fetchFournisseurs() {
    return await fetchManager.get("api/magasin-planning-liste-fournisseur");
  }

  function displayFournisseur(item) {
    return `${item.num_fournisseur} - ${item.nom_fournisseur}`;
  }

  /**===================================================
   * Autocomplete champ nom fournisseur
   *====================================================*/
  const nomFournisseurInput = document.querySelector(
    "#planning_magasin_frn_search_nomFournisseur"
  );
  if (nomFournisseurInput) {
    new AutoComplete({
      inputElement: nomFournisseurInput,
      suggestionContainer: document.querySelector("#suggestion-nom-fournisseur"),
      loaderElement: document.querySelector("#loader-nom-fournisseur"),
      debounceDelay: 300,
      fetchDataCallback: fetchFournisseurs,
      displayItemCallback: (item) => displayFournisseur(item),
      itemToStringCallback: (item) => displayFournisseur(item),
      onSelectCallback: (item) => {
        nomFournisseurInput.value = item.nom_fournisseur;
      },
    });
  }

  /**===================================================
   * Autocomplete champ code fournisseur
   *====================================================*/
  const codeFournisseurInput = document.querySelector(
    "#planning_magasin_frn_search_codeFournisseur"
  );
  if (codeFournisseurInput) {
    new AutoComplete({
      inputElement: codeFournisseurInput,
      suggestionContainer: document.querySelector("#suggestion-code-fournisseur"),
      loaderElement: document.querySelector("#loader-code-fournisseur"),
      debounceDelay: 300,
      fetchDataCallback: fetchFournisseurs,
      displayItemCallback: (item) => displayFournisseur(item),
      itemToStringCallback: (item) => displayFournisseur(item),
      onSelectCallback: (item) => {
        codeFournisseurInput.value = item.num_fournisseur;
      },
    });
  }
});
