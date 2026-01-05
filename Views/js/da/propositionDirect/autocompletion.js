import { AutoComplete } from "../../utils/AutoComplete";
import { getAllDesignations, getAllFournisseurs } from "../data/fetchData";

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

  let suggestionContainer = document.getElementById(`suggestion${baseId}`);
  let loaderElement = document.getElementById(`spinner_container${baseId}`);

  new AutoComplete({
    inputElement: field,
    suggestionContainer: suggestionContainer,
    loaderElement: loaderElement,
    debounceDelay: 300,
    fetchDataCallback: async () => {
      const cache = JSON.parse(
        localStorage.getItem("autocompleteCache") || "{}"
      );

      if (fieldName === "fournisseur") {
        if (!cache.fournisseurs) {
          const data = await getAllFournisseurs(); // fetch si cache vide
          cache.fournisseurs = data;
          console.log("préchargement fournisseurs OK");
          localStorage.setItem("autocompleteCache", JSON.stringify(cache));
          return data;
        }

        return cache.fournisseurs;
      }

      if (!cache.designationsZDI) {
        const data = await getAllDesignations(true); // fetch si cache vide
        cache.designationsZDI = data;
        console.log("préchargement designationsZDI OK");
        localStorage.setItem("autocompleteCache", JSON.stringify(cache));
        return data;
      }

      return cache.designationsZDI;
    },
    displayItemCallback: (item) => displayValues(item, fieldName),
    itemToStringCallback: (item) => stringsToSearch(item, fieldName),
    onSelectCallback: (item) => handleValuesOfFields(item, fieldName, fields),
    itemToStringForBlur: (item) => stringsToSearchForBlur(item, fieldName),
    onBlurCallback: (found) => onBlurEvents(found, fieldName, fields),
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

function onBlurEvents(found, fieldName, fields) {
  if (fieldName === "reference") {
    let reference = fields.reference;

    if (!found && reference.value.trim() !== "") {
      Swal.fire({
        icon: "error",
        title: "Référence inexistant",
        html: `La référence <b class="text-danger">"${reference.value}"</b> n'existe pas dans le catalogue des référence ZDI. <br> Veuillez sélectionner une réference ZDI valide svp.`,
        confirmButtonText: "OK",
        customClass: {
          htmlContainer: "swal-text-left",
        },
      }).then(() => {
        reference.focus();
        reference.value = "";
      });
    }
  } else if (fieldName === "designation") {
    let designation = fields.designation;

    if (!found && designation.value.trim() !== "") {
      fields.numeroFournisseur.value = 0;
      fields.reference.value = "ST";
      fields.famille.value = "-";
      fields.sousFamille.value = "-";
    }
  } else if (fieldName == "fournisseur") {
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
