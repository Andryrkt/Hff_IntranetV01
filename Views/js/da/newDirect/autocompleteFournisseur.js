import { AutoComplete } from "../../utils/AutoComplete";

export function initializeAutoCompletionFrn(fournisseur) {
  let baseId = fournisseur.id.replace("demande_appro_direct_form_DAL", "");
  let suggestionContainer = document.getElementById(`suggestion${baseId}`);
  let loaderElement = document.getElementById(`spinner_container${baseId}`);

  new AutoComplete({
    inputElement: fournisseur,
    suggestionContainer: suggestionContainer,
    loaderElement: loaderElement,
    debounceDelay: 150,
    fetchDataCallback: () => {
      const cache = JSON.parse(
        localStorage.getItem("autocompleteCache") || "{}"
      );
      return Promise.resolve(cache.fournisseurs || []);
    },
    displayItemCallback: (item) =>
      `${item.numerofournisseur} - ${item.nomfournisseur}`,
    itemToStringCallback: (item) =>
      `${item.numerofournisseur} - ${item.nomfournisseur}`,
    itemToStringForBlur: (item) => `${item.nomfournisseur}`,
    onBlurCallback: (found) => {
      let numeroFournisseur = document.getElementById(
        fournisseur.id.replace("nom", "numero")
      );
      if (!found) {
        numeroFournisseur.value = "-";
      }
    },
    onSelectCallback: (item) => {
      let numeroFournisseur = document.getElementById(
        fournisseur.id.replace("nom", "numero")
      );
      fournisseur.value = item.nomfournisseur;
      numeroFournisseur.value = item.numerofournisseur;
    },
  });
}
