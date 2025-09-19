import { AutoComplete } from "../../utils/AutoComplete";

export function autocompleteTheField(field, fieldName) {
  let baseId = field.id.replace("demande_appro_proposition", "");

  let reference = getField(field.id, fieldName, "reference");
  let fournisseur = getField(field.id, fieldName, "fournisseur");
  let numeroFournisseur = getField(field.id, fieldName, "numeroFournisseur");
  let designation = getField(field.id, fieldName, "designation");

  let suggestionContainer = document.getElementById(`suggestion${baseId}`);
  let loaderElement = document.getElementById(`spinner_container${baseId}`);

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
          : cache.designationsZDI || [];

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
        getField(field.id, fieldName, "codeFams1"),
        getField(field.id, fieldName, "codeFams2")
      ),
    itemToStringCallback: (item) => stringsToSearch(item, fieldName),
    itemToStringForBlur: (item) => stringsToSearchForBlur(item, fieldName),
    onBlurCallback: (found) => onBlurEvents(found, designation, fieldName),
  });
}

function safeValue(val) {
  return val && val.trim() !== "" ? val : "-";
}

function onBlurEvents(found, designation, fieldName) {
  const numeroDa = document
    .querySelector(".tab-pane.fade.show.active.dalr")
    .id.split("_")
    .pop();
  const numPage = localStorage.getItem(`currentTab_${numeroDa}`);
  if (designation.value.trim() !== "") {
    const desi = `designation_${numPage}`;

    let baseId = designation.id.replace(desi, "");

    let allFields = document.querySelectorAll(`[id*="${baseId}"]`);
    let referencePiece = document.querySelector(
      `#demande_appro_proposition_reference_${numPage}`
    );

    if (fieldName == "designation") {
      // Texte rouge ou non, ajout de valeur dans catalogue
      allFields.forEach((field) => {
        if (!found) {
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
      // Si non trouvé alors valeur de reférence pièce = ''
      referencePiece.value = found ? referencePiece.value : "ST";
    }
  }
}

function getField(id, fieldName, fieldNameReplace) {
  return document.getElementById(id.replace(fieldName, fieldNameReplace));
}

function displayValues(item, fieldName) {
  if (fieldName === "fournisseur") {
    return `N° Fournisseur: ${item.numerofournisseur} - Nom Fournisseur: ${item.nomfournisseur}`;
  } else {
    return `Référence: ${item.referencepiece} <br>Désignation: ${item.designation}`;
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
  famille,
  sousFamille
) {
  if (fieldName === "fournisseur") {
    fournisseur.value = item.nomfournisseur;
    numeroFournisseur.value = item.numerofournisseur;
  } else {
    reference.value = item.referencepiece;
    /* fournisseur.value = item.fournisseur;
    numeroFournisseur.value = item.numerofournisseur;
    designation.value = item.designation; */
    famille.value = item.codefamille ?? "-";
    sousFamille.value = item.codesousfamille ?? "-";
  }
}
