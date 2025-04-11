import { ajouterUneLigne } from "./dalr";

export function ajouterReference(addLineId) {
  const line = addLineId.replace("add_line_", "");

  const fields = {
    fournisseur: getField("fournisseur", line),
    numeroFournisseur: getField("numeroFournisseur", line),
    reference: getField("reference", line),
    designation: getField("designation", line),
    prixUnitaire: getField("PU", line),
    qteDispo: getField("qte_dispo", line),
    motif: getField("motif", line),
    famille: getField("codeFams1", line),
    sousFamille: getField("codeFams2", line),
  };
  const nePasAjouter = Object.values(fields).some(handleFieldValue);
  console.log(nePasAjouter);

  if (!nePasAjouter) {
    ajouterUneLigne(line, fields);
  }
}

function getField(fieldName, line) {
  return document.getElementById(
    `demande_appro_proposition_${fieldName}_${line}`
  );
}

function handleFieldValue(field) {
  console.log(field);

  console.log(field.value);

  if (field.value) {
    return false;
  } else {
    field.focus();
    return true;
  }
}
