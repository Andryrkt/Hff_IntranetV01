import { replaceNameToNewIndex } from '../new/dal';

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
  insertCellData(row, '1'); // conditionnement TO DO
  insertCellData(row, fields.qteDispo.value);
  insertCellData(row, fields.motif.value);

  // Ajouter une ligne dans le formulaire d'ajout de DemandeApproLR
  ajouterLigneDansForm(line, fields, total);

  // Vider les valeurs dans les champs
  Object.values(fields).forEach((field) => {
    field.value = '';
  });
}

function insertCellData(row, $data) {
  let cell = row.insertCell();
  cell.innerHTML = $data;
}

function ajouterLigneDansForm(line, fields, total) {
  let newIndex = Date.now();
  let prototype = document
    .getElementById('child-prototype')
    .firstElementChild.cloneNode(true); // Clonage du prototype
  let container = document.getElementById('demande_appro_lr_collection_DALR'); // contenant du formulaire
  container.style.display = 'none'; // ne pas afficher le contenant

  prototype.id = replaceNameToNewIndex(prototype.id, newIndex);
  prototype.querySelectorAll('[id], [name]').forEach(function (element) {
    element.id = element.id
      ? replaceNameToNewIndex(element.id, newIndex)
      : element.id;
    element.name = element.name
      ? replaceNameToNewIndex(element.name, newIndex)
      : element.name;
  });

  ajouterValeur(prototype, 'numeroLigneDem', line);
  ajouterValeur(prototype, 'numeroFournisseur', fields.numeroFournisseur.value);
  ajouterValeur(prototype, 'nomFournisseur', fields.fournisseur.value);
  ajouterValeur(prototype, 'artRefp', fields.reference.value);
  ajouterValeur(prototype, 'artDesi', fields.designation.value);
  ajouterValeur(prototype, 'qteDispo', fields.qteDispo.value);
  ajouterValeur(prototype, 'prixUnitaire', fields.prixUnitaire.value);
  ajouterValeur(prototype, 'total', total);
  ajouterValeur(prototype, 'conditionnement', '1'); // conditionnement TO DO
  ajouterValeur(prototype, 'motif', fields.motif.value);

  container.append(prototype);
}

function ajouterValeur(prototype, fieldId, value) {
  prototype.querySelector(`[id*="${fieldId}"]`).value = value;
}
