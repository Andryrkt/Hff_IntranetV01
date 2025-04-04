import { FetchManager } from '../../api/FetchManager';
import { AutoComplete } from '../../utils/AutoComplete';

export function initializeAutoCompletionFrn(fournisseur) {
  let baseId = fournisseur.id.replace('demande_appro_form_DAL', '');
  let suggestionContainer = document.getElementById(`suggestion${baseId}`);
  let loaderElement = document.getElementById(`spinner_container${baseId}`);

  new AutoComplete({
    inputElement: fournisseur,
    suggestionContainer: suggestionContainer,
    loaderElement: loaderElement,
    debounceDelay: 150,
    fetchDataCallback: fetchFournisseurs,
    displayItemCallback: (item) =>
      `${item.numerofournisseur} - ${item.nomfournisseur}`,
    itemToStringCallback: (item) =>
      `${item.numerofournisseur} - ${item.nomfournisseur}`,
    onSelectCallback: (item) => {
      let numeroFournisseur = document.getElementById(
        fournisseur.id.replace('nom', 'numero')
      );
      fournisseur.value = item.nomfournisseur;
      numeroFournisseur.value = item.numerofournisseur;
    },
  });
}

async function fetchFournisseurs() {
  const fetchManager = new FetchManager();
  return await fetchManager.get(`demande-appro/autocomplete/all-fournisseur`);
}
