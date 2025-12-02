import { AutoComplete } from "../../utils/AutoComplete";

export function autocompleteTheField(field, fieldName) {
  let baseId = field.id.replace("demande_appro_proposition", "");
  let fields = {
    reference: getField(field.id, fieldName, "reference"),
    fournisseur: getField(field.id, fieldName, "fournisseur"),
    numeroFournisseur: getField(field.id, fieldName, "numeroFournisseur"),
    designation: getField(field.id, fieldName, "designation"),
    famille: getField(field.id, fieldName, "codeFams1"),
    sousFamille: getField(field.id, fieldName, "codeFams2"),
  };

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
    itemToStringCallback: (item) => stringsToSearch(item, fieldName),
    onSelectCallback: (item) => handleValuesOfFields(item, fieldName, fields),
    itemToStringForBlur: (item) => stringsToSearchForBlur(item, fieldName),
    onBlurCallback: (found) => onBlurEvents(found, designation, fieldName),
  });
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

function handleValuesOfFields(item, fieldName, fields) {
  if (fieldName === "fournisseur") {
    fields.fournisseur.value = item.nomfournisseur;
    fields.numeroFournisseur.value = item.numerofournisseur;
  } else {
    fields.reference.value = item.referencepiece;
    fields.famille.value = item.codefamille ?? "-";
    fields.sousFamille.value = item.codesousfamille ?? "-";
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

function onBlurEvents(found, designation, fieldName) {
  const numeroDa = document
    .querySelector(".tab-pane.fade.show.active.dalr")
    .id.split("_")
    .pop();
  const numPage = localStorage.getItem(`currentTab_${numeroDa}`);
  const desi = `designation_${numPage}`;
  let baseId = designation.id.replace(desi, "");
  let allFields = document.querySelectorAll(`[id*="${baseId}"]`);
  let referencePiece = document.querySelector(
    `#demande_appro_proposition_reference_${numPage}`
  );

  if (fieldName == "reference") {
    console.log("baseID = " + baseId);

    let foundInput = document.querySelector(
      `[id*="${baseId}"][id*="found_${numPage}"]`
    );
    foundInput.value = found ? "1" : "0";
    console.log(foundInput.value);
  } else if (fieldName == "designation") {
    if (designation.value.trim() !== "") {
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
  } else if (fieldName == "fournisseur") {
    if (!found) {
      let numFrnInput = document.querySelector(
        `[id*="${baseId}"][id*="numeroFournisseur_${numPage}"]`
      );
      numFrnInput.value = "-";
    }
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
