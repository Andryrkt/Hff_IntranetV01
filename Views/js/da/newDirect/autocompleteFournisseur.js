import { AutoComplete } from "../../utils/AutoComplete";
import { getAllFournisseurs } from "../data/fetchData";

export function initializeAutoCompletionFrn(fournisseur) {
  let baseId = fournisseur.id.replace("demande_appro_direct_form_DAL", "");
  let suggestionContainer = document.getElementById(`suggestion${baseId}`);
  let loaderElement = document.getElementById(`spinner_container${baseId}`);
  let numeroFournisseur = document.getElementById(
    fournisseur.id.replace("nom", "numero")
  );

  new AutoComplete({
    inputElement: fournisseur,
    suggestionContainer: suggestionContainer,
    loaderElement: loaderElement,
    debounceDelay: 150,
    fetchDataCallback: async () => {
      const cache = JSON.parse(
        localStorage.getItem("autocompleteCache") || "{}"
      );
      if (!cache.fournisseurs) {
        const data = await getAllFournisseurs(); // fetch si cache vide
        cache.fournisseurs = data;
        console.log("préchargement fournisseurs OK");
        localStorage.setItem("autocompleteCache", JSON.stringify(cache));
        return data;
      }

      return cache.fournisseurs;
    },
    displayItemCallback: (item) =>
      `${item.numerofournisseur} - ${item.nomfournisseur}`,
    itemToStringCallback: (item) =>
      `${item.numerofournisseur} - ${item.nomfournisseur}`,
    onSelectCallback: (item) => {
      fournisseur.value = item.nomfournisseur;
      numeroFournisseur.value = item.numerofournisseur;
    },
    itemToStringForBlur: (item) => `${item.nomfournisseur}`,
    onBlurCallback: (found) => {
      if (fournisseur.value.trim() === "") numeroFournisseur.value = "-";
      if (!found && fournisseur.value.trim() !== "") {
        Swal.fire({
          icon: "warning",
          title: "Attention ! Fournisseur non trouvé !",
          text: `Le fournisseur saisi n'existe pas, veuillez en sélectionner un dans la liste.`,
        }).then(() => {
          fournisseur.focus();
          fournisseur.value = "";
          numeroFournisseur.value = "-";
        });
      }
    },
  });
}
