import { FetchManager } from '../../api/FetchManager';
import { AutoComplete } from '../../utils/AutoComplete';

export function autocompleteTheField(field, fieldName) {
  let baseId = field.id.replace('demande_appro_proposition', '');

  let reference = getField(field.id, fieldName, 'reference');
  let fournisseur = getField(field.id, fieldName, 'fournisseur');
  let designation = getField(field.id, fieldName, 'designation');

  let suggestionContainer = document.getElementById(`suggestion${baseId}`);
  let loaderElement = document.getElementById(`loader${baseId}`);

  new AutoComplete({
    inputElement: field,
    suggestionContainer: suggestionContainer,
    loaderElement: loaderElement,
    debounceDelay: 300,
    fetchDataCallback: () => fetchAllData(fieldName),
    displayItemCallback: (item) => displayValues(item, fieldName),
    onSelectCallback: (item) => handleValuesOfFields(item),
    itemToStringCallback: (item) => stringsToSearch(item, fieldName),
  });
}

function getField(id, fieldName, fieldNameReplace) {
  return document.getElementById(id.replace(fieldName, fieldNameReplace));
}

async function fetchAllData(fieldName) {
  console.log(fieldName);

  const fetchManager = new FetchManager();
  let url = `demande-appro/autocomplete/all-${
    fieldName === 'fournisseur' ? fieldName : 'designation-sans'
  }`;
  console.log(url);
  return await fetchManager.get(url);
}

function displayValues(item, fieldName) {
  if (fieldName === 'fournisseur') {
    return `N° Fournisseur: ${item.numerofournisseur} - Nom Fournisseur: ${item.nomfournisseur}`;
  } else {
    return `Référence: ${item.referencepiece} - Fournisseur: ${item.nomfournisseur} - Désignation: ${item.designation}`;
  }
}

function stringsToSearch(item, fieldName) {
  if (fieldName === 'reference') {
    return `${item.referencepiece}`;
  } else if (fieldName === 'fournisseur') {
    return `${item.numerofournisseur} - ${item.nomfournisseur}`;
  } else {
    return `${item.designation}`;
  }
}

function handleValuesOfFields(item) {
  console.log(item);
  console.log(reference, fournisseur, designation);
}
