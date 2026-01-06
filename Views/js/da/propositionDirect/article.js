import { ajouterUneLigne } from "./dalr";

export function ajouterReference(addLineId) {
  const line = addLineId.replace("add_line_", "");
  const numPage = addLineId.split("_").pop();
  const { isCatalogueInput } = recupInput(numPage);
  let iscatalogue = isCatalogueInput.value;

  /**
   * Les champs à récupérer dans la proposition d'une article
   */
  const fields = {
    reference: getField("reference", line),
    designation: getField("designation", line),
    fournisseur: getField("fournisseur", line),
    qteDispo: getField("qte_dispo", line),
    motif: getField("motif", line),
    numeroFournisseur: getField("numeroFournisseur", line),
    prixUnitaire: getField("PU", line),
  };

  const divValidation = document.getElementById(`validationButtons`);
  const envoyerSelections = document.getElementById(`envoyerSelections`);
  const validerSelections = document.getElementById(`validerSelections`);
  const enregistrerDraft = document.getElementById(`enregistrerDraft`);
  const nePasAjouter = Object.values(fields).some(handleFieldValue);

  if (!nePasAjouter) {
    if (divValidation) {
      divValidation.classList.add("d-none"); // On le cache
    }
    if (envoyerSelections && envoyerSelections.dataset.daDirecteAppro != "1") {
      envoyerSelections.classList.remove("d-none"); // On l'affiche
    }
    if (validerSelections) {
      validerSelections.classList.remove("d-none"); // On l'affiche
    }
    if (enregistrerDraft) {
      enregistrerDraft.classList.remove("d-none"); // On l'affiche
    }
    ajouterUneLigne(line, fields, iscatalogue);
  }
}

function getField(fieldName, line) {
  return document.getElementById(
    `demande_appro_proposition_${fieldName}_${line}`
  );
}

function handleFieldValue(field) {
  /**
   * field.id.includes('qte_dispo'): pour savoir que c'est le champ qté dispo
   * Champ non requis
   */
  if (
    field.value ||
    field.id.includes("qte_dispo") ||
    field.id.includes("motif")
  ) {
    return false;
  } else {
    field.focus();
    return true;
  }
}

/**
 * Permet de récupérer les éléments HTML liés à une page/index spécifique
 * @param {string|number} numPage
 * @returns {object} - Un objet contenant tous les éléments utiles
 */
function recupInput(numPage) {
  return {
    sousFamilleInput: document.querySelector(
      `#demande_appro_proposition_codeFams2_${numPage}`
    ),
    codeFamilleInput: document.querySelector(`#codeFams1_${numPage}`),
    codeSousFamilleInput: document.querySelector(`#codeFams2_${numPage}`),
    spinnerElement: document.querySelector(`#spinner_codeFams2_${numPage}`),
    containerElement: document.querySelector(`#container_codeFams2_${numPage}`),
    designation: document.querySelector(
      `#demande_appro_proposition_designation_${numPage}`
    ),
    fournisseur: document.querySelector(
      `#demande_appro_proposition_fournisseur_${numPage}`
    ),
    reference: document.querySelector(
      `#demande_appro_proposition_reference_${numPage}`
    ),
    isCatalogueInput: document.querySelector(`#catalogue_${numPage}`),
  };
}
