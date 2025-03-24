import { FetchManager } from '../../api/FetchManager';
import { AutoComplete } from '../../utils/AutoComplete';
import { updateDropdown } from '../../utils/selectionHandler';

export function autocompleteTheFields() {
  let designations = document.querySelectorAll(
    `[id*="artDesi"][id*="form_DAL"]:not([id*="__name__"])`
  ); // éléments avec id contenant "artDesi" et "form_DAL" mais ne contenant pas "__name__"

  designations.forEach((designation) => {
    let baseId = designation.id.replace('demande_appro_form_DAL', '');

    let famille = document.getElementById(
      designation.id.replace('artDesi', 'fams1')
    );
    let sousFamille = document.getElementById(
      designation.id.replace('artDesi', 'fams2')
    );
    let familleLibelle = document.getElementById(
      designation.id.replace('artDesi', 'artFams1')
    );
    let sousFamilleLibelle = document.getElementById(
      designation.id.replace('artDesi', 'artFams2')
    );

    let suggestionContainer = document.getElementById(`suggestion${baseId}`);
    let loaderElement = document.getElementById(`spinner${baseId}`);

    function initAutoComplete() {
      new AutoComplete({
        inputElement: designation,
        suggestionContainer,
        loaderElement,
        debounceDelay: 150,
        fetchDataCallback: () => fetchDesignations(famille, sousFamille),
        displayItemCallback: displayDesignation,
        onSelectCallback: (item) =>
          handleValueOfTheFields(
            item,
            designation,
            famille,
            sousFamille,
            familleLibelle,
            sousFamilleLibelle
          ),
        itemToStringCallback: (item) =>
          `${item.fournisseur} - ${item.designation}`,
      });
    }

    designation.addEventListener('input', initAutoComplete);
    initAutoComplete();
  });
}

async function fetchDesignations(famille, sousFamille) {
  const fetchManager = new FetchManager();
  let codeFamille = famille.value !== '' ? famille.value : '-';
  let codeSousFamille = sousFamille.value !== '' ? sousFamille.value : '-';

  return await fetchManager.get(
    `demande-appro/autocomplete/all-designation/${codeFamille}/${codeSousFamille}`
  );
}

function displayDesignation(item) {
  return `Fournisseur: ${item.fournisseur} - Désignation: ${item.designation} - Prix: ${item.prix}`;
}

async function handleValueOfTheFields(
  item,
  designation,
  famille,
  sousFamille,
  familleLibelle,
  sousFamilleLibelle
) {
  console.log(item);

  if (famille.value !== item.codefamille) {
    famille.value = item.codefamille;
    familleLibelle.value = famille.options[famille.selectedIndex].text;
    await changeSousFamille(famille, sousFamille);
  } else if (sousFamille.value !== item.codesousfamille) {
    await changeSousFamille(famille, sousFamille);
  }
  sousFamille.value = item.codesousfamille;
  sousFamilleLibelle.value =
    sousFamille.options[sousFamille.selectedIndex].text;
  designation.value = item.designation;
}

async function changeSousFamille(famille, sousFamille) {
  let baseId = sousFamille.id.replace('demande_appro_form_DAL', '');

  try {
    await updateDropdown(
      sousFamille,
      `api/demande-appro/sous-famille/${famille.value}`,
      '-- Choisir une sous-famille --',
      document.getElementById(`spinner${baseId}`),
      document.getElementById(`container${baseId}`)
    );
  } catch (error) {
    console.error('Erreur dans changeSousFamille:', error);
  } finally {
    console.log('Fin de changeSousFamille');
  }
}
