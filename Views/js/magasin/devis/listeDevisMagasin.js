import { AutoComplete } from "../../utils/AutoComplete.js?v=2026.03.23.01";
import { FetchManager } from "../../api/FetchManager.js?v=2026.03.23.01";
import { filterServiceByAgence } from "../../utils/agenceService/filterServiceByAgence.js?v=2026.03.23.01";

document.addEventListener("DOMContentLoaded", () => {
  const fetchManager = new FetchManager();
  /**===================================================
   * Autocomplete champ code client
   *====================================================*/
  async function fetchCodeClient() {
    return await fetchManager.get("api/code-client-fetch");
  }

  function displayCodeClient(item) {
    return `${item.code_client} - ${item.nom_client}`;
  }

const codeClientInput = document.getElementById("devis_neg_search_codeClient") || document.getElementById("devis_magasin_search_codeClient");

if (codeClientInput) {
    function onSelectCodeClient(item) {
        codeClientInput.value = `${item.code_client}`;
    }

    new AutoComplete({
        inputElement: codeClientInput,
        suggestionContainer: document.getElementById("suggestion-code-client"),
        loaderElement: document.getElementById("loader-code-client"),
        fetchDataCallback: fetchCodeClient,
        displayItemCallback: displayCodeClient,
        onSelectCallback: onSelectCodeClient,
        lazyLoad: true
    });
}

  /**===========================================================================
   * Configuration des agences et services
   *============================================================================*/

// Configuration du filtrage des services par agence
if (document.getElementById("devis_neg_search_emetteur_agence")) {
    filterServiceByAgence({
        agenceSelector: "#devis_neg_search_emetteur_agence",
        serviceSelector: "#devis_neg_search_emetteur_service"
    });
}

if (document.getElementById("devis_magasin_search_agenceEmetteur")) {
    filterServiceByAgence({
        agenceSelector: "#devis_magasin_search_agenceEmetteur",
        serviceSelector: "#devis_magasin_search_serviceEmetteur"
    });
}

});
