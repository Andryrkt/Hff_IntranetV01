import { FetchManager } from "../../api/FetchManager.js";
import { AutoComplete } from "../../utils/AutoComplete.js";
import { filterServiceByAgence } from "../../utils/agenceService/filterServiceByAgence.js";

document.addEventListener("DOMContentLoaded", function () {
  const fetchManager = new FetchManager();

  /** ========================================================================
   * Filtre le champ service en fonction de l'agence choisie
   *============================================================================*/
  filterServiceByAgence({
    agenceSelector: "#planning_magasin_frn_search_agenceService_agence",
    serviceSelector: "#planning_magasin_frn_search_agenceService_service",
  });

  async function fetchFournisseurs() {
    return await fetchManager.get("api/magasin-planning-liste-fournisseur");
  }

  function displayFournisseur(item) {
    return `${item.num_fournisseur} - ${item.nom_fournisseur}`;
  }

  /**===================================================
   * Autocomplete champ fournisseur (recherche par nom ou par code)
   *====================================================*/
  const fournisseurInput = document.querySelector(
    "#planning_magasin_frn_search_fournisseur"
  );
  if (fournisseurInput) {
    new AutoComplete({
      inputElement: fournisseurInput,
      suggestionContainer: document.querySelector("#suggestion-fournisseur"),
      loaderElement: document.querySelector("#loader-fournisseur"),
      debounceDelay: 300,
      fetchDataCallback: fetchFournisseurs,
      displayItemCallback: (item) => displayFournisseur(item),
      itemToStringCallback: (item) => displayFournisseur(item),
      onSelectCallback: (item) => {
        fournisseurInput.value = item.nom_fournisseur;
      },
    });
  }
});
