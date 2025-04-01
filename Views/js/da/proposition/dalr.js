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
  insertCellData(row, '1'); // conditionnement
  insertCellData(row, fields.qteDispo.value);
  insertCellData(row, fields.motif.value);

  // Ajouter une ligne dans le formulaire d'ajout de DemandeApproLR
  ajouterLigneDansForm(line, fields);

  // Vider les valeurs dans les champs
  Object.values(fields).forEach((field) => {
    field.value = '';
  });
}

function insertCellData(row, $data) {
  let cell = row.insertCell();
  cell.innerHTML = $data;
}

function ajouterLigneDansForm(line, fields) {
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

  prototype.querySelector('[id*="numeroLigneDem"]').value = line;
  prototype.querySelector('[id*="numeroFournisseur"]').value =
    fields.numeroFournisseur.value;
  prototype.querySelector('[id*="nomFournisseur"]').value =
    fields.fournisseur.value;
  prototype.querySelector('[id*="artRefp"]').value = fields.reference.value;
  prototype.querySelector('[id*="artDesi"]').value = fields.designation.value;
  prototype.querySelector('[id*="qteDispo"]').value = fields.qteDispo.value;

  container.append(prototype);
}
