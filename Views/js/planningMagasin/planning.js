import { FetchManager } from "../api/FetchManager.js";
import { AutoComplete } from "../utils/AutoComplete.js";
import { filterRowsByColumn } from "../utils/filtre.js";

document.addEventListener("DOMContentLoaded", function () {
  const fetchManager = new FetchManager();
  // const buttons = {
  //   "tout-livre": "tout-livre",
  //   "partiellement-livre": "partiellement-livre",
  //   "partiellement-dispo": "partiellement-dispo",
  //   "complet-non-livre": "complet-non-livre",
  //   "back-order": "back-order",
  //   "tout-afficher": null, // Tout afficher n'a pas de classe spécifique
  // };

  // // Ajoute un gestionnaire d'événement pour chaque bouton
  // for (const [buttonId, filterClass] of Object.entries(buttons)) {
  //   const button = document.getElementById(buttonId);
  //   if (button) {
  //     button.addEventListener("click", () => filterRowsByColumn(filterClass));
  //   }
  // }
  /**===================================================
   * Autocomplete champ numero client
   *====================================================*/
  async function fetchClient() {
    return await fetchManager.get("api/numero-libelle-client");
  }
  function displayClient(item) {
    return `${item.numclient} - ${item.nom_client}`;
  }
  const numClient = document.querySelector("#planning_magasin_search_numParc");
  function onSelectNumClient(item) {
    numClient.value = `${item.numclient}`;
  }
  //AUtoComplet nomLCients
  new AutoComplete({
    inputElement: numClient,
    suggestionContainer: document.querySelector("#suggestion-num-client"),
    loaderElement: document.querySelector("#loader-num-client"),
    debounceDelay: 300,
    fetchDataCallback:fetchClient,
    displayItemCallback: (item) =>displayClient(item),
    itemToStringCallback: (item) =>`${item.numclient}- ${item.nom_client}`,
    onSelectCallback: (item) => onSelectNumClient(item),
  });
});
