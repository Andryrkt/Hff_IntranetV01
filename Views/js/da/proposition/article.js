import { ajouterUneLigne } from './dalr';

export function ajouterReference(addLineId) {
  const line = addLineId.replace('add_line_', '');
  const numPage = addLineId.split('_').pop();
  const { isCatalogueInput } = recupInput(numPage);
  let iscatalogue = isCatalogueInput.value;

  const fields = {
    famille: getField('codeFams1', line),
    sousFamille: getField('codeFams2', line),
    reference: getField('reference', line),
    designation: getField('designation', line),
    fournisseur: getField('fournisseur', line),
    qteDispo: getField('qte_dispo', line),
    motif: getField('motif', line),
    numeroFournisseur: getField('numeroFournisseur', line),
    prixUnitaire: getField('PU', line),
  };

  const divValidation = document.getElementById(`validationButtons`);
  if (iscatalogue == 1) {
    const nePasAjouter = Object.values(fields).some(handleFieldValue);
    if (!nePasAjouter) {
      if (divValidation) {
        divValidation.remove(); // On supprime le div de validation s'il existe
        // divValidation.classList.add('d-none'); // On le cache
      }
      ajouterUneLigne(line, fields, iscatalogue);
    }
  } else {
    if (!fields.prixUnitaire.value) {
      fields.prixUnitaire.focus();
      ajouterUneLigne(line, fields, iscatalogue);
    } else {
      fields.famille.value = getValueField(`artFams1_${line}`);
      fields.sousFamille.value = getValueField(`artFams2_${line}`);
      fields.reference.value = getValueField(`artRefp_${line}`);
      fields.designation.value = getValueField(`artDesi_${line}`);
      fields.fournisseur.value = getValueField(`nomFournisseur_${line}`);
      fields.qteDispo.value = '-';
      fields.motif.value = '*';
      if (divValidation) {
        divValidation.remove(); // On supprime le div de validation s'il existe
        // divValidation.classList.add('d-none'); // On le cache
      }
      ajouterUneLigne(line, fields, iscatalogue);
    }
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
  if (field.value || field.id.includes('qte_dispo')) {
    return false;
  } else {
    field.focus();
    return true;
  }
}

function getValueField(fieldName) {
  return document.getElementById(fieldName).value;
}

function handleFieldValueForNonCatalogue(field) {
  if (field.id.includes('PU') && !field) {
    field.focus();
    return true;
  } else {
    return false;
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
