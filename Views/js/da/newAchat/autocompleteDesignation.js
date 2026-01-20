import { AutoComplete } from "../../utils/AutoComplete";

export function initializeAutoCompletionDesi(designation) {
  let baseId = designation.id.replace(
    "demande_appro_achat_form_demandeApproParentLines",
    ""
  );

  let fields = {
    constp: getFieldByGeneratedId(designation.id, "artConstp"),
    refp: getFieldByGeneratedId(designation.id, "artRefp"),
    numeroFournisseur: getFieldByGeneratedId(
      designation.id,
      "numeroFournisseur"
    ),
    nomFournisseur: getFieldByGeneratedId(designation.id, "nomFournisseur"),
    prixUnitaire: getFieldByGeneratedId(designation.id, "prixUnitaire"),
    articleStocke: getFieldByGeneratedId(designation.id, "articleStocke"),
  };

  new AutoComplete({
    inputElement: designation,
    suggestionContainer: document.getElementById(`suggestion${baseId}`),
    loaderElement: document.getElementById(`spinner_container${baseId}`),
    debounceDelay: 150,
    fetchDataCallback: async () => {
      return [];
    },
    displayItemCallback: (item) =>
      `Référence: ${item.refp} - Fournisseur: ${item.nomFournisseur} - Prix: ${item.prixUnitaire} <br>Désignation: ${item.designation}`,
    itemToStringCallback: (item) => `${item.designation}`,
    itemToStringForBlur: (item) => `${item.designation}`,
    onBlurCallback: (found) => onBlurEvent(found, designation, fields),
    onSelectCallback: (item) =>
      handleValueOfTheFields(item, designation, fields),
  });
}

function getFieldByGeneratedId(baseId, suffix) {
  return document.getElementById(baseId.replace("artDesi", suffix));
}

async function handleValueOfTheFields(item, designation, fields) {
  console.log(item);
  let constp = fields.constp;
  let refp = fields.refp;
  let numeroFournisseur = fields.numeroFournisseur;
  let nomFournisseur = fields.nomFournisseur;
  let prixUnitaire = fields.prixUnitaire;

  constp.value = item.constp;
  refp.value = item.refp;
  numeroFournisseur.value = item.numeroFournisseur;
  nomFournisseur.value = item.nomFournisseur;
  prixUnitaire.value = item.prixUnitaire;
  designation.value = item.designation;

  designation.classList.add("non-modifiable");
  nomFournisseur.classList.add("non-modifiable");
}

function onBlurEvent(found, designation, fields) {
  if (designation.value.trim() !== "") {
    let constp = fields.constp;
    let refp = fields.refp;
    let prixUnitaire = fields.prixUnitaire;
    let articleStocke = fields.articleStocke;

    constp.value = found ? constp.value : "-";
    refp.value = found ? refp.value : "-";
    prixUnitaire.value = found ? prixUnitaire.value : 0;

    articleStocke.checked = found;
  }
}
