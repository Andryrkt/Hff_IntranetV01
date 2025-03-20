import { FetchManager } from '../../api/FetchManager';
import { AutoComplete } from '../../utils/AutoComplete';

export function autocompleteTheFields() {
  let designations = document.querySelectorAll(
    `[id*="artDesi"][id*="form_DAL"]:not([id*="__name__"])`
  ); // éléments avec id contenant "artDesi" et "form_DAL" mais ne contenant pas "__name__"
  console.log(designations);

  designations.forEach((designation) => {
    let baseId = designation.id.replace('demande_appro_form_DAL', '');
    let famille = document.getElementById(
      designation.id.replace('artDesi', 'artFams1')
    );
    let sousFamille = document.getElementById(
      designation.id.replace('artDesi', 'artFams2')
    );
    let spinnerId = `spinner${baseId}`;
    let suggestionId = `suggestion${baseId}`;

    new AutoComplete({
      inputElement: designation,
      suggestionContainer: document.getElementById(suggestionId),
      loaderElement: document.getElementById(spinnerId),
      debounceDelay: 150,
      fetchDataCallback: () => fetchDesignations(famille, sousFamille),
      displayItemCallback: displayDesignation,
      onSelectCallback: (item) => {
        designation.value = item.designation;
      },
      itemToStringCallback: (item) =>
        `${item.fournisseur} - ${item.designation}`,
    });
  });
}

async function fetchDesignations(famille, sousFamille) {
  const fetchManager = new FetchManager();
  let codeFamille = famille.value !== '' ? famille.value : '-';
  let codeSousFamille = sousFamille.value !== '' ? sousFamille.value : '-';
  console.log(codeFamille, codeSousFamille);

  return await fetchManager.get(
    `demande-appro/autocomplete/all-designation/${codeFamille}/${codeSousFamille}`
  );
}

function displayDesignation(item) {
  return `Fournisseur: ${item.fournisseur} - Désignation: ${item.designation} - Prix: ${item.prix}`;
}
