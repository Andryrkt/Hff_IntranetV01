import { replaceNameToNewIndex } from "../new/dal";
import { formaterNombre } from "../../utils/formatNumberUtils";

export function ajouterUneLigne(line, fields) {
  const tableBody = document.getElementById(`tableBody_${line}`);
  const qteDem = parseFloat(document.getElementById(`qteDem_${line}`).value);
  const prixUnitaire = parseFloat(fields.prixUnitaire.value);
  const row = tableBody.insertRow();
  const rowIndex = tableBody.rows.length; // numero de ligne du tableau
  console.log("Ligne ajoutée n°", rowIndex);
  let total = (prixUnitaire * qteDem).toFixed(2);

  // Insérer des données dans le tableau
  const radioId = `radio_${line}_${rowIndex}`;

  // Déterminer la couleur selon la condition
  const color = prixUnitaire === 0 ? "red" : "#000";

  insertCellData(
    row,
    `<input type="radio" name="selectedRow_${line}" id="${radioId}" value="${
      line + "-" + rowIndex
    }" onclick="toggleRadio(this)">`
  );
  insertCellData(row, fields.fournisseur.value, "Center", color);
  insertCellData(row, fields.reference.value, "Center", color);
  insertCellData(row, fields.designation.value, "left", color);
  insertCellData(
    row,
    formaterNombre(fields.prixUnitaire.value),
    "right",
    color
  );
  insertCellData(row, formaterNombre(total), "right", color);
  insertCellData(row, "1", "center", color); // conditionnement TO DO
  insertCellData(row, fields.qteDispo.value, "center", color);
  insertCellData(row, fields.motif.value, "left", color);

  // Ajouter une ligne dans le formulaire d'ajout de DemandeApproLR
  ajouterLigneDansForm(line, fields, total, rowIndex);

  // Vider les valeurs dans les champs
  Object.values(fields).forEach((field) => {
    field.value = "";
  });
}

function insertCellData(row, $data, align = "center", color = "red") {
  let cell = row.insertCell();
  cell.innerHTML = $data;
  cell.style.textAlign = align;
  cell.style.color = color;
}

function ajouterLigneDansForm(line, fields, total, rowIndex) {
  // let newIndex = Date.now();
  let newIndex = rowIndex;
  let prototype = document
    .getElementById("child-prototype")
    .firstElementChild.cloneNode(true); // Clonage du prototype
  let container = document.getElementById("demande_appro_lr_collection_DALR"); // contenant du formulaire
  container.style.display = "none"; // ne pas afficher le contenant

  prototype.id = replaceNameToNewIndex(prototype.id, newIndex);
  prototype.querySelectorAll("[id], [name]").forEach(function (element) {
    element.id = element.id
      ? replaceNameToNewIndex(element.id, newIndex)
      : element.id;
    element.name = element.name
      ? replaceNameToNewIndex(element.name, newIndex)
      : element.name;
  });

  ajouterValeur(prototype, "numeroLigneDem", line); // numero de page
  ajouterValeur(prototype, "numeroFournisseur", fields.numeroFournisseur.value);
  ajouterValeur(prototype, "nomFournisseur", fields.fournisseur.value);
  ajouterValeur(prototype, "artRefp", fields.reference.value);
  ajouterValeur(prototype, "artDesi", fields.designation.value);
  ajouterValeur(prototype, "qteDispo", fields.qteDispo.value);
  ajouterValeur(prototype, "prixUnitaire", fields.prixUnitaire.value);
  ajouterValeur(prototype, "total", total);
  ajouterValeur(prototype, "conditionnement", "1"); // conditionnement TO DO
  ajouterValeur(prototype, "motif", fields.motif.value);
  ajouterValeur(prototype, "artFams1", fields.famille.value);
  ajouterValeur(prototype, "artFams2", fields.sousFamille.value);
  ajouterValeur(prototype, "numLigneTableau", rowIndex); // numero de ligne du tableau

  container.append(prototype);
}

function ajouterValeur(prototype, fieldId, value) {
  prototype.querySelector(`[id*="${fieldId}"]`).value = value;
}
