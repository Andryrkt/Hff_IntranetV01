import { ajouterUneLigne } from './dalr';

export function ajouterReference(addLineId, iscatalogue) {
  const line = addLineId.replace('add_line_', '');

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

  const nePasAjouter = Object.values(fields).some(handleFieldValue);

  if (!nePasAjouter) {
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
  if (field.value || field.id.includes('qte_dispo')) {
    return false;
  } else {
    field.focus();
    return true;
  }
}
