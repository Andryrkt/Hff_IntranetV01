import { FetchManager } from '../../api/FetchManager';
import { AutoComplete } from '../../utils/AutoComplete';

export function autocompleteTheField(field, fieldName) {
  let baseId = field.id.replace('demande_appro_proposition', '');

  let reference = getField(field.id, fieldName, 'reference');
  let fournisseur = getField(field.id, fieldName, 'fournisseur');
  let designation = getField(field.id, fieldName, 'designation');
  let PU = getField(field.id, fieldName, 'PU');
  let line = baseId.replace(`_${fieldName}_`, '');

  let codeFams1 = getValueCodeFams('codeFams1', line);
  let codeFams2 = getValueCodeFams('codeFams2', line);

  let suggestionContainer = document.getElementById(`suggestion${baseId}`);
  let loaderElement = document.getElementById(`loader${baseId}`);

  new AutoComplete({
    inputElement: field,
    suggestionContainer: suggestionContainer,
    loaderElement: loaderElement,
    debounceDelay: 300,
    fetchDataCallback: () => fetchAllData(fieldName, codeFams1, codeFams2),
    displayItemCallback: (item) => displayValues(item, fieldName),
    onSelectCallback: (item) =>
      handleValuesOfFields(
        item,
        fieldName,
        fournisseur,
        reference,
        designation,
        PU
      ),
    itemToStringCallback: (item) => stringsToSearch(item, fieldName),
  });
}

function getValueCodeFams(fams, line) {
  return document.getElementById(`${fams}_${line}`).value;
}

function getField(id, fieldName, fieldNameReplace) {
  return document.getElementById(id.replace(fieldName, fieldNameReplace));
}

async function fetchAllData(fieldName, codeFams1, codeFams2) {
  const fetchManager = new FetchManager();
  let url = `demande-appro/autocomplete/all-${
    fieldName === 'fournisseur'
      ? fieldName
      : `designation/${codeFams1}/${codeFams2}`
  }`;
  console.log(url);
  return await fetchManager.get(url);
}

function displayValues(item, fieldName) {
  if (fieldName === 'fournisseur') {
    return `N° Fournisseur: ${item.numerofournisseur} - Nom Fournisseur: ${item.nomfournisseur}`;
  } else {
    return `Référence: ${item.referencepiece} - Fournisseur: ${item.fournisseur} - Désignation: ${item.designation}`;
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

function handleValuesOfFields(
  item,
  fieldName,
  fournisseur,
  reference,
  designation,
  PU
) {
  if (fieldName === 'fournisseur') {
    fournisseur.value = item.nomfournisseur;
  } else {
    reference.value = item.referencepiece;
    fournisseur.value = item.fournisseur;
    designation.value = item.designation;
    PU.value = item.prix;
  }
}
