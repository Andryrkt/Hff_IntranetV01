import { FetchManager } from '../../api/FetchManager';
import { AutoComplete } from '../../utils/AutoComplete';

export function autocompleteTheFields() {
  let designations = document.querySelectorAll(`[id*="artDesi"]`);
  designations.forEach((designation) => {
    let baseId = designation.id.replace('demande_appro_form_DAL', '');
    let spinnerId = `spinner${baseId}`;
    let suggestionId = `suggestion${baseId}`;
    new AutoComplete({
      inputElement: designation,
      suggestionContainer: document.getElementById(suggestionId),
      loaderElement: document.getElementById(spinnerId),
      debounceDelay: 150,
      fetchDataCallback: fetchDesignations,
      displayItemCallback: displayDesignation,
      onSelectCallback: (item) => {
        designation.value = item.designation;
      },
      itemToStringCallback: (item) =>
        `${item.fournisseur} - ${item.designation}`,
    });
  });
}

async function fetchDesignations() {
  const fetchManager = new FetchManager();
  return await fetchManager.get('demande-appro/autocomplete/all-designation');
}

function displayDesignation(item) {
  return `Fournisseur: ${item.fournisseur} - Désignation: ${item.designation} - Prix: ${item.prix}`;
}
