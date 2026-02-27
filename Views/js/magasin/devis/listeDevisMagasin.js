import { AutoComplete } from "../../utils/AutoComplete.js";
import { FetchManager } from "../../api/FetchManager.js";
import { filterServiceByAgence } from "../../utils/agenceService/filterServiceByAgence.js";

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

  const codeClientInput = document.getElementById(
    "devis_magasin_search_codeClient"
  );

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
  });

  /**===========================================================================
   * Configuration des agences et services
   *============================================================================*/

  filterServiceByAgence({
    agenceSelector: "#devis_magasin_search_agenceEmetteur",
    serviceSelector: "#devis_magasin_search_serviceEmetteur",
  });
});
