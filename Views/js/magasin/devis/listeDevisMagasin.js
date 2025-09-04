import { AutoComplete } from "../../utils/AutoComplete.js";
import { FetchManager } from "../../api/FetchManager.js";
const fetchManager = new FetchManager();
import { handleAgenceChange } from "../../dit/fonctionUtils/fonctionListDit.js";


/**===================================================
 * Autocomplete champ code client
 *====================================================*/
async function fetchCodeClient() {
    return await fetchManager.get("api/code-client-fetch");
}

function displayCodeClient(item) {
    return `${item.code_client} - ${item.nom_client}`;
}

const codeClientInput = document.getElementById("devis_magasin_search_codeClient");

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

  // Attachement des événements pour les agences
  document.getElementById("devis_magasin_search_emetteur_agence").addEventListener("change", () =>
    handleAgenceChange("emetteur")
  );
