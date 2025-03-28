import { ajouterUneLigne } from './dalr';

export function ajouterReference(id) {
  const line = id.replace('add_line_', '');
  const fields = {
    reference: getField('reference', line),
    fournisseur: getField('fournisseur', line),
    designation: getField('designation', line),
    qteDispo: getField('qte_dispo', line),
    motif: getField('motif', line),
    prixUnitaire: getField('PU', line),
  };
  const nePasAjouter = Object.values(fields).some(handleFieldValue);

  if (!nePasAjouter) {
    ajouterUneLigne(addLineId, fields);
  }
}

function getField(fieldName, line) {
  return document.getElementById(
    `demande_appro_proposition_${fieldName}_${line}`
  );
}

function handleFieldValue(field) {
  if (field.value) {
    return false;
  } else {
    field.focus();
    return true;
  }
}
