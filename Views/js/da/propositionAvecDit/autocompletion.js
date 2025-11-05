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

  new AutoComplete({
    inputElement: field,
    suggestionContainer: suggestionContainer,
    loaderElement: loaderElement,
    debounceDelay: 300,
    fetchDataCallback: () => {
      const cache = JSON.parse(
        localStorage.getItem("autocompleteCache") || "{}"
      );
      const dataList =
        fieldName === "fournisseur"
          ? cache.fournisseurs || []
          : cache.designationsZST || [];

      return Promise.resolve(dataList);
    }, // non filtré par famille et sous-famille
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
  const numeroDa = document
    .querySelector(".tab-pane.fade.show.active.dalr")
    .id.split("_")
    .pop();
  const numPage = localStorage.getItem(`currentTab_${numeroDa}`);
  console.log("numeroDa = " + numeroDa);
  console.log("numPage = " + numPage);
  if (designation.value.trim() !== "") {
    const desi = `designation_${numPage}`;

    let baseId = designation.id.replace(desi, "");

    let allFields = document.querySelectorAll(`[id*="${baseId}"]`);
    console.log("baseId = " + baseId);
    console.log("allFields =");
    console.log(allFields);

    if (fieldName == "designation") {
      // Texte rouge ou non, ajout de valeur dans catalogue
      allFields.forEach((field) => {
        console.log("field.id = ");
        console.log(field.id);
        console.log("found = ");
        console.log(found);

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
          if (
            field.id.includes("reference") &&
            field.id.includes(`_${numPage}`)
          ) {
            field.value = "ST";
          }
        }
      });
    }
  }
}

function getValueCodeFams(fams, line) {
  return document.getElementById(`${fams}_${line}`).value;
}

function getField(id, fieldName, fieldNameReplace) {
  return document.getElementById(id.replace(fieldName, fieldNameReplace));
}

function displayValues(item, fieldName) {
  if (fieldName === "fournisseur") {
    return `N° Fournisseur: ${item.numerofournisseur} - Nom Fournisseur: ${item.nomfournisseur}`;
  } else {
    return `Référence: ${item.referencepiece} - Fournisseur: ${item.fournisseur} - Prix: ${item.prix} <br>Désignation: ${item.designation}`;
  }
}

function stringsToSearch(item, fieldName) {
  if (fieldName === "reference") {
    return `${item.referencepiece} - `;
  } else if (fieldName === "fournisseur") {
    return `${item.numerofournisseur} - ${item.nomfournisseur}`;
  } else {
    return `${item.designation} - `;
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
    const numeroDa = document
      .querySelector(".tab-pane.fade.show.active.dalr")
      .id.split("_")
      .pop();
    const numPage = localStorage.getItem(`currentTab_${numeroDa}`);
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

    famille.classList.add("non-modifiable");
    sousFamille.classList.add("non-modifiable");
    reference.classList.add("non-modifiable");
    fournisseur.classList.add("non-modifiable");
    designation.classList.add("non-modifiable");
  }
}
