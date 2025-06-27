import { replaceNameToNewIndex } from "../new/dal";
import { formaterNombre } from "../../utils/formatNumberUtils";
import { boutonRadio } from "./boutonRadio.js";
import { generateCustomFilename } from "../../utils/dateUtils.js";

export function ajouterUneLigne(line, fields, iscatalogue) {
  const tableBody = document.getElementById(`tableBody_${line}`);
  const qteDem = parseFloat(document.getElementById(`qteDem_${line}`).value);
  const prixUnitaire = parseFloat(fields.prixUnitaire.value);
  const row = tableBody.insertRow(0);
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
    }" checked>`
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
  let qteDispo = fields.qteDispo.value === "" ? "-" : fields.qteDispo.value;
  insertCellData(row, qteDispo, "center", color);
  insertCellData(row, fields.motif.value, "left", color);

  let nbrColonnes = tableBody.previousElementSibling.rows[0].cells.length;

  if (nbrColonnes > 10) {
    insertCellsFicheTechnique(row, color, line, rowIndex);
  }
  insertCellPiecesJointes(row, color, line, rowIndex);
  insertCellDeleteLine(row, color, line, rowIndex);

  // Ajouter une ligne dans le formulaire d'ajout de DemandeApproLR
  ajouterLigneDansForm(line, fields, total, rowIndex);

  boutonRadio();

  // Vider les valeurs dans les champs

  if (iscatalogue == 1) {
    Object.values(fields).forEach((field) => {
      if (!field.id.includes("_codeFams")) {
        field.value = "";
      }
    });
  } else {
    Object.values(fields).forEach((field) => {
      if (!field.id.includes("_codeFams")) {
        field.value = "";
      } else {
        field.value = "-";
      }
    });
  }
}

function insertCellData(row, $data, align = "center", color = "red") {
  let cell = row.insertCell();
  cell.innerHTML = $data;
  cell.style.textAlign = align;
  cell.style.color = color;
}

function insertCellToRow(row, htmlContent, align = "center", color = "red") {
  let cell = row.insertCell();
  cell.style.textAlign = align;
  cell.style.color = color;
  cell.append(htmlContent);
}

function insertCellsFicheTechnique(
  row,
  color,
  numeroLigneDem,
  numLigneTableau
) {
  /** Icône d'ajout de fichier */
  const addFile = document.createElement("a");
  addFile.href = "#";
  addFile.title = "Joindre une fiche technique";
  addFile.dataset.nbrLine = numeroLigneDem;
  addFile.dataset.nbrLineTable = numLigneTableau;

  const icon = document.createElement("i");
  icon.className = "fas fa-paperclip";

  addFile.appendChild(icon);

  addFile.addEventListener("click", function () {
    const nbrLine = addFile.dataset.nbrLine;
    const numLigneTableau = addFile.dataset.nbrLineTable;
    const inputFile = document.getElementById(
      `demande_appro_lr_collection_DALR_${nbrLine}${numLigneTableau}_nomFicheTechnique`
    );
    createFicheTechnique(nbrLine, numLigneTableau, inputFile);
  });
  insertCellToRow(row, addFile, "center", color);

  /** Lien du fichier */
  const lienFicheTechnique = document.createElement("a");
  lienFicheTechnique.href = "#";
  lienFicheTechnique.target = "_blank";
  lienFicheTechnique.id = `lien_fiche_technique_${numeroLigneDem}_${numLigneTableau}`;
  lienFicheTechnique.textContent = "";

  insertCellToRow(row, lienFicheTechnique, "left", color);
}

function insertCellPiecesJointes(row, color, numeroLigneDem, numLigneTableau) {
  /** Icône d'ajout de fichiers */
  const addFile = document.createElement("a");
  addFile.href = "#";
  addFile.title = "Joindre des pièces jointes";
  addFile.dataset.nbrLine = numeroLigneDem;
  addFile.dataset.nbrLineTable = numLigneTableau;

  const icon = document.createElement("i");
  icon.className = "fas fa-paperclip";

  addFile.appendChild(icon);

  addFile.addEventListener("click", function () {
    const nbrLine = addFile.dataset.nbrLine;
    const numLigneTableau = addFile.dataset.nbrLineTable;
    const inputFile = document.getElementById(
      `demande_appro_lr_collection_DALR_${nbrLine}${numLigneTableau}_fileNames`
    );
    createPieceJointe(nbrLine, numLigneTableau, inputFile);
  });
  insertCellToRow(row, addFile, "center", color);

  /** contenant des fichiers */
  let fieldContainer = document.createElement("div");
  fieldContainer.id = `demande_appro_lr_collection_DALR_${numeroLigneDem}${numLigneTableau}_fileNamesContainer`;
  insertCellToRow(row, fieldContainer, "left", color);
}

function insertCellDeleteLine(row, color, line, rowIndex) {
  /** Icône de suppression de ligne */
  const deleteLineIcon = document.createElement("i");
  deleteLineIcon.classList.add("fas", "fa-times", "fs-7");
  deleteLineIcon.style.cursor = "pointer";
  deleteLineIcon.title = "Supprimer la ligne de proposition";

  deleteLineIcon.addEventListener("click", function () {
    let row = this.parentElement.parentElement; // ligne sur le tableau (ce que l'utilisateur voit)
    let formRow = document.getElementById(
      `demande_appro_lr_collection_DALR_${line}${rowIndex}`
    ); // ligne de formulaire à envoyer dans la BDD (ce que l'utilisateur ne voit pas)
    row.remove();
    formRow.remove();
  });

  insertCellToRow(row, deleteLineIcon, "center", color);
}

function ajouterLigneDansForm(line, fields, total, rowIndex) {
  // let newIndex = Date.now();
  let newIndex = line + rowIndex;
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

export function createFicheTechnique(line, rowIndex, inputFile) {
  if (!inputFile) {
    console.log("input file inexistant");

    let newIndex = line + rowIndex;
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
    ajouterValeur(prototype, "numLigneTableau", rowIndex); // numero de ligne du tableau

    container.append(prototype);
    // 🔄 Maintenant que le prototype est dans le DOM, retrouve l'input file
    const inputFileInserted = prototype.querySelector(
      'input[type="file"][id*="nomFicheTechnique"]'
    );

    if (inputFileInserted) {
      inputFileInserted.accept = ".pdf";
      inputFileInserted.addEventListener("change", (e) =>
        onFileInputChange(e, line, rowIndex)
      );
      inputFileInserted.click();
    } else {
      console.warn(
        "Le nouvel input file est introuvable dans le prototype cloné."
      );
    }
  } else {
    inputFile.accept = ".pdf";
    inputFile.addEventListener("change", (e) =>
      onFileInputChange(e, line, rowIndex)
    );
    inputFile.click();
  }
}

export function createPieceJointe(line, rowIndex, inputFile) {
  if (!inputFile) {
    console.log("input file inexistant");

    let newIndex = line + rowIndex;
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
    ajouterValeur(prototype, "numLigneTableau", rowIndex); // numero de ligne du tableau

    container.append(prototype);
    // 🔄 Maintenant que le prototype est dans le DOM, retrouve l'input file
    const inputFileInserted = prototype.querySelector(
      'input[type="file"][id*="fileNames"]'
    );

    if (inputFileInserted) {
      inputFileInserted.accept = ".pdf, image/*";
      inputFileInserted.addEventListener("change", (e) =>
        onFileNamesInputChangeDalr(e)
      );
      inputFileInserted.click();
    } else {
      console.warn(
        "Le nouvel input file est introuvable dans le prototype cloné."
      );
    }
  } else {
    inputFile.accept = ".pdf, image/*";
    inputFile.addEventListener("change", (e) => onFileNamesInputChangeDalr(e));
    inputFile.click();
  }
}

export function onFileInputChange(event, nbrLine, numLigneTableau) {
  console.log("tong ato", nbrLine, numLigneTableau);

  const input = event.currentTarget;

  console.log(input);

  const fileLink = document.getElementById(
    `lien_fiche_technique_${nbrLine}_${numLigneTableau}`
  );

  console.log(fileLink);
  const file = input.files[0];

  console.log(file);
  if (file && fileLink) {
    const fileURL = URL.createObjectURL(file);
    fileLink.href = fileURL;
    fileLink.textContent =
      generateCustomFilename("FT") +
      `.${file.name.split(".").pop().toLowerCase()}`;
  }
}

export function onFileNamesInputChangeDalr(event) {
  let inputFile = event.target; // input file field
  let fieldContainer = document.getElementById(
    inputFile.id.replace("fileNames", "fileNamesContainer")
  ); // Conteneur du champ de fichier correspondant

  // Vérifier si un fichier a été sélectionné
  if (inputFile.files.length > 0) {
    // Vider le conteneur avant d'ajouter les nouveaux liens
    fieldContainer.innerHTML = ""; // Vider le conteneur

    let ul = document.createElement("ul");
    ul.classList.add("ps-3", "mb-0"); // Ajouter des classes pour le style
    for (let index = 0; index < inputFile.files.length; index++) {
      const file = inputFile.files[index];
      let li = document.createElement("li");
      let a = document.createElement("a");
      a.href = URL.createObjectURL(file);
      a.textContent =
        generateCustomFilename("PJ") +
        `${index + 1}.${file.name.split(".").pop().toLowerCase()}`; // nom généré exemple: PJ
      a.target = "_blank"; // Ouvrir le fichier dans un nouvel onglet
      li.appendChild(a); // Ajouter le lien à l'élément de liste
      ul.appendChild(li); // Ajouter l'élément de liste
    }
    fieldContainer.appendChild(ul); // Ajouter le lien au conteneur
  }
}
