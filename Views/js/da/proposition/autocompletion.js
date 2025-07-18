import { FetchManager } from "../../api/FetchManager";
import { AutoComplete } from "../../utils/AutoComplete";
import { updateDropdown } from "../../utils/selectionHandler";

export function autocompleteTheField(
  field,
  fieldName,
  numPage = null,
  iscatalogue = null
) {
  let baseId = field.id.replace("demande_appro_proposition", "");

  let reference = getField(field.id, fieldName, "reference");
  let fournisseur = getField(field.id, fieldName, "fournisseur");
  let numeroFournisseur = getField(field.id, fieldName, "numeroFournisseur");
  let designation = getField(field.id, fieldName, "designation");
  let PU = getField(field.id, fieldName, "PU");
  let line = baseId.replace(`_${fieldName}_`, "");

  let codeFams1 = getValueCodeFams("codeFams1", line);
  let codeFams2 = getValueCodeFams("codeFams2", line);

  let suggestionContainer = document.getElementById(`suggestion${baseId}`);
  let loaderElement = document.getElementById(`spinner_container${baseId}`);

  if (numPage) {
    const sousFamille = document.querySelector(
      "#demande_appro_proposition_codeFams2_" + numPage
    );
    const famille = document.querySelector(
      "#demande_appro_proposition_codeFams1_" + numPage
    );

    codeFams1 = safeValue(famille.value);
    codeFams2 = safeValue(sousFamille.value);
  }

  // const isCatalogueInput = document.querySelector(`#catalogue_${numPages}`);

  // console.log(isCatalogueInput);

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
        numeroFournisseur,
        reference,
        designation,
        PU,
        getField(field.id, fieldName, "codeFams1"),
        getField(field.id, fieldName, "codeFams2"),
        iscatalogue
      ),
    itemToStringCallback: (item) => stringsToSearch(item, fieldName),
    itemToStringForBlur: (item) => stringsToSearchForBlur(item, fieldName),
    onBlurCallback: (found) => onBlurEvents(found, designation, fieldName),
  });
}

function safeValue(val) {
  return val && val.trim() !== "" ? val : "-";
}

function getFieldByGeneratedId(baseId, suffix) {
  return document.getElementById(baseId.replace("artDesi", suffix));
}

function onBlurEvents(found, designation, fieldName) {
  const numPage = localStorage.getItem("currentTab");
  if (designation.value.trim() !== "") {
    const desi = `designation_${numPage}`;

    let baseId = designation.id.replace(desi, "");

    let allFields = document.querySelectorAll(`[id*="${baseId}"]`);
    let fournisseur = getFieldByGeneratedId(
      designation.id,
      `fournisseur_${numPage}`
    );
    let referencePiece = document.querySelector(
      `#demande_appro_proposition_reference_${numPage}`
    );

    if (fieldName == "designation") {
      // Texte rouge ou non, ajout de valeur dans catalogue
      allFields.forEach((field) => {
        if (found) {
          field.classList.remove("text-danger");
        } else {
          field.classList.add("text-danger");
          if (field.id.includes(`PU_${numPage}`)) {
            field.parentElement.classList.remove("d-none"); // afficher le div container du PU
          }
          if (field.id.includes(`numeroFournisseur_${numPage}`)) {
            field.value = 0;
          }
          if (
            field.id.includes("codeFams") &&
            field.id.includes(`_${numPage}`)
          ) {
            console.log("codeFams");

            field.value = "-";
          }
        }
      });
    }

    // Si non trouvé alors valeur de reférence pièce = ''
    referencePiece.value = found ? referencePiece.value : "ST";
  }
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
    fieldName === "fournisseur"
      ? fieldName
      : `designation/${codeFams1}/${codeFams2}`
  }`;
  return await fetchManager.get(url);
}

function displayValues(item, fieldName) {
  if (fieldName === "fournisseur") {
    return `N° Fournisseur: ${item.numerofournisseur} - Nom Fournisseur: ${item.nomfournisseur}`;
  } else {
    return `Référence: ${item.referencepiece} - Fournisseur: ${item.fournisseur} - Désignation: ${item.designation}`;
  }
}

function stringsToSearch(item, fieldName) {
  if (fieldName === "reference") {
    return `${item.referencepiece}`;
  } else if (fieldName === "fournisseur") {
    return `${item.numerofournisseur} - ${item.nomfournisseur}`;
  } else {
    return `${item.designation}`;
  }
}

function stringsToSearchForBlur(item, fieldName) {
  if (fieldName === "reference") {
    return `${item.referencepiece}`;
  } else if (fieldName === "fournisseur") {
    return `${item.nomfournisseur}`;
  } else {
    return `${item.designation}`;
  }
}

function handleValuesOfFields(
  item,
  fieldName,
  fournisseur,
  numeroFournisseur,
  reference,
  designation,
  PU,
  famille,
  sousFamille,
  iscatalogue
) {
  if (fieldName === "fournisseur") {
    fournisseur.value = item.nomfournisseur;
    numeroFournisseur.value = item.numerofournisseur;
    console.log(PU.value);
  } else {
    reference.value = item.referencepiece;
    fournisseur.value = item.fournisseur;
    numeroFournisseur.value = item.numerofournisseur;
    designation.value = item.designation;
    PU.parentElement.classList.add("d-none"); // cacher le div container du PU
    PU.value = item.prix;
    famille.value = item.codefamille;
    sousFamille.value = item.codesousfamille;
    const numPage = localStorage.getItem("currentTab");
    const spinnerElement = document.querySelector(
      "#spinner_codeFams2_" + numPage
    );
    const containerElement = document.querySelector(
      "#container_codeFams2_" + numPage
    );

    if (iscatalogue == "") {
      updateDropdown(
        sousFamille,
        `api/demande-appro/sous-famille/${famille.value}`,
        "-- Choisir une sous-famille --",
        spinnerElement,
        containerElement,
        item.codesousfamille
      );
    }
  }
}
