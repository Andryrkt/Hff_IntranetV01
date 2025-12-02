import { AutoComplete } from "../../utils/AutoComplete";
import { updateDropdown } from "../../utils/selectionHandler";

export function autocompleteTheField(field, fieldName, iscatalogue = null) {
  let baseId = field.id.replace("demande_appro_proposition", "");
  let fields = {
    reference: getField(field.id, fieldName, "reference"),
    fournisseur: getField(field.id, fieldName, "fournisseur"),
    numeroFournisseur: getField(field.id, fieldName, "numeroFournisseur"),
    designation: getField(field.id, fieldName, "designation"),
    PU: getField(field.id, fieldName, "PU"),
    famille: getField(field.id, fieldName, "codeFams1"),
    sousFamille: getField(field.id, fieldName, "codeFams2"),
  };

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
          : cache.designationsZST || [];

      return Promise.resolve(dataList);
    }, // non filtré par famille et sous-famille
    displayItemCallback: (item) => displayValues(item, fieldName),
    onSelectCallback: (item) =>
      handleValuesOfFields(item, fieldName, fields, iscatalogue),
    itemToStringCallback: (item) => stringsToSearch(item, fieldName),
    itemToStringForBlur: (item) => stringsToSearchForBlur(item, fieldName),
    onBlurCallback: (found) => onBlurEvents(found, fieldName, fields),
  });
}

function displayValues(item, fieldName) {
  if (fieldName === "fournisseur") {
    return `N° Fournisseur: ${item.numerofournisseur} - Nom Fournisseur: ${item.nomfournisseur}`;
  } else {
    return `Référence: ${item.referencepiece} - Fournisseur: ${item.fournisseur} - Prix: ${item.prix} <br>Désignation: ${item.designation}`;
  }
}

function handleValuesOfFields(item, fieldName, fields, iscatalogue) {
  if (fieldName === "fournisseur") {
    let fournisseur = fields.fournisseur;
    let numeroFournisseur = fields.numeroFournisseur;

    fournisseur.value = item.nomfournisseur;
    numeroFournisseur.value = item.numerofournisseur;
  } else {
    let reference = fields.reference;
    let fournisseur = fields.fournisseur;
    let numeroFournisseur = fields.numeroFournisseur;
    let designation = fields.designation;
    let PU = fields.PU;
    let famille = fields.famille;
    let sousFamille = fields.sousFamille;

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

function onBlurEvents(found, fieldName, fields) {
  if (fieldName === "designation") {
    let designation = fields.designation;

    if (found) {
      Object.values(fields).forEach((field) => {
        field.classList.remove("text-danger");
      });
    } else if (designation.value.trim() !== "") {
      let PU = fields.PU;
      let numeroFournisseur = fields.numeroFournisseur;
      let sousFamille = fields.sousFamille;
      let famille = fields.famille;
      let reference = fields.reference;
      Object.values(fields).forEach((field) => {
        field.classList.add("text-danger");
      });
      PU.parentElement.classList.remove("d-none"); // afficher le div container du PU
      numeroFournisseur.value = 0;
      famille.value = "-";
      sousFamille.value = "-";
      reference.value = "ST";
    }
  } else if (fieldName === "fournisseur") {
    let fournisseur = fields.fournisseur;
    let numeroFournisseur = fields.numeroFournisseur;

    if (!found && fournisseur.value.trim() !== "") {
      Swal.fire({
        icon: "error",
        title: "Fournisseur inexistant !",
        html: `Le fournisseur <b class="text-danger">"${fournisseur.value}"</b> n'existe pas, veuillez en sélectionner un dans la liste s'il vous plaît!`,
        confirmButtonText: "OK",
        customClass: {
          htmlContainer: "swal-text-left",
        },
      }).then(() => {
        fournisseur.focus();
        fournisseur.value = "";
        numeroFournisseur.value = "-";
      });
    }
  } else if (fieldName === "reference") {
    let reference = fields.reference;
    let famille = fields.famille;
    let sousFamille = fields.sousFamille;
    let designation = fields.designation;
    let PU = fields.PU;
    let numeroFournisseur = fields.numeroFournisseur;

    if (!found && reference.value.trim() !== "") {
      Swal.fire({
        icon: "error",
        title: "Référence inexistant !",
        html: `La référence <b class="text-danger">"${reference.value}"</b> n'existe pas, veuillez en sélectionner une dans la liste s'il vous plaît!`,
        confirmButtonText: "OK",
        customClass: {
          htmlContainer: "swal-text-left",
        },
      }).then(() => {
        reference.focus();
        reference.value = "";
        famille.value = "-";
        sousFamille.value = "-";
        designation.value = "";
        PU.parentElement.classList.add("d-none"); // afficher le div container du PU
        numeroFournisseur.value = 0;
      });
    }
  }
}

function getField(id, fieldName, fieldNameReplace) {
  return document.getElementById(id.replace(fieldName, fieldNameReplace));
}
