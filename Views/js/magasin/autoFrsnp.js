import { FetchManager } from "../api/FetchManager";
import { AutoComplete } from "../utils/AutoComplete";

document.addEventListener("DOMContentLoaded", function () {
  let frs = document.getElementById(
    "liste_cde_frn_non_place_search_CodeNomFrs"
  );
  let suggestion_contenaire = document.getElementById("suggestion-numfrs");
  let loader = document.getElementById("loader-numfrs");
  new AutoComplete({
    inputElement: frs,
    suggestionContainer: suggestion_contenaire,
    loaderElement: loader,
    fetchDataCallback: fetchFrs,
    displayItemCallback: (item) => `${item.codefrs} -  ${item.libfrs}`,
    itemToStringCallback: (item) => `${item.codefrs} -  ${item.libfrs}`,
    onSelectCallback: (item) => {
      frs.value = item.codefrs;
    },
  });

  async function fetchFrs() {
    const fetchManager = new FetchManager();
    return await fetchManager.get(`api/frs-non-place-fetch`);
  }
});
