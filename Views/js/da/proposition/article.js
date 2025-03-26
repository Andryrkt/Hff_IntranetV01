export function ajouterReference(id) {
  const line = id.replace('add_line_', '');
  const fields = [
    document.getElementById(`demande_appro_proposition_reference_${line}`),
    document.getElementById(`demande_appro_proposition_fournisseur_${line}`),
    document.getElementById(`demande_appro_proposition_designation_${line}`),
    document.getElementById(`demande_appro_proposition_qte_dispo_${line}`),
    document.getElementById(`demande_appro_proposition_motif_${line}`),
  ];
  const nePasAjouter = fields.some(handleFieldValue);

  if (!nePasAjouter) {
  }
}

function handleFieldValue(field) {
  if (field.value) {
    return false;
  } else {
    field.focus();
    return true;
  }
}
