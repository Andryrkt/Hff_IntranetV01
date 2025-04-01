export function ajouterUneLigne(line, fields) {
  const tableBody = document.getElementById(`tableBody_${line}`);
  const qteDem = parseInt(document.getElementById(`qteDem_${line}`).value);
  const row = tableBody.insertRow();
  let total = parseFloat(fields.prixUnitaire.value) * qteDem;

  // Insérer des données dans le tableau
  insertCellData(row, fields.fournisseur.value);
  insertCellData(row, fields.reference.value);
  insertCellData(row, fields.designation.value);
  insertCellData(row, fields.prixUnitaire.value);
  insertCellData(row, total);
  insertCellData(row, '1');
  insertCellData(row, fields.qteDispo.value);
  insertCellData(row, fields.motif.value);

  // Ajouter une ligne dans le formulaire d'ajout de DemandeApproLR
  ajouterLigneDansForm();

  // Vider les valeurs dans les champs
  Object.values(fields).forEach((field) => {
    field.value = '';
  });
}

function insertCellData(row, $data) {
  let cell = row.insertCell();
  cell.innerHTML = $data;
}

function ajouterLigneDansForm() {}
