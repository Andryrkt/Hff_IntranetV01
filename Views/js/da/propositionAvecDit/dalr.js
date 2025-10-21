import { formaterNombre } from "../../utils/formatNumberUtils.js";
import { boutonRadio } from "./boutonRadio.js";
import { generateCustomFilename } from "../../utils/dateUtils.js";
import { replaceNameToNewIndex } from "../newAvecDit/dal.js";
import { normalizeData } from "../../utils/dataUtils.js";

// Dictionnaire pour stocker les fichiers sélectionnés par champ input
const selectedFilesMap = {};

export function ajouterUneLigne(line, fields, iscatalogue) {
  const tableBody = document.getElementById(`tableBody_${line}`);
  const qteDem = parseFloat(document.getElementById(`qteDem_${line}`).value);
  const prixUnitaire = parseFloat(fields.prixUnitaire.value);
  const row = tableBody.insertRow(0);
  row.setAttribute("role", "button");
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
  insertCellData(row, normalizeData(fields.qteDispo.value), "center", color);
  insertCellData(row, normalizeData(fields.motif.value), "left", color);

  let nbrColonnes = tableBody.previousElementSibling.rows[0].cells.length;

  if (nbrColonnes > 11) {
    insertCellsFicheTechnique(row, color, line, rowIndex);
  }
  insertCellPiecesJointes(row, color, line, rowIndex);
  insertCellDeleteLine(row, color, line, rowIndex);

  // Ajouter une ligne dans le formulaire d'ajout de DemandeApproLR
  ajouterLigneDansForm(line, fields, total, rowIndex);

  // Evènement pour les bouton radio
  boutonRadio();

  // Evènement de clic sur une ligne de proposition
  row.addEventListener("click", handleRowClick);

  // Vider les valeurs dans les champs

  if (iscatalogue == 1) {
    Object.values(fields).forEach((field) => {
      if (!field.id.includes("_codeFams")) {
        field.value = "";
      }
      console.log(field.id, field.id.includes("_codeFams"), field.value);
    });
  } else {
    Object.values(fields).forEach((field) => {
      if (!field.id.includes("_codeFams")) {
        field.value = "";
      } else {
        field.value = "-";
      }
      console.log(field.id, field.id.includes("_codeFams"), field.value);
    });
  }

  fields.famille.classList.remove("non-modifiable");
  fields.sousFamille.classList.remove("non-modifiable");
  fields.reference.classList.remove("non-modifiable");
  fields.fournisseur.classList.remove("non-modifiable");
  fields.designation.classList.remove("non-modifiable");
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
  addFile.classList.add("link-primary");
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
  let cell = row.insertCell();
  cell.style.padding = "10px 0";
  cell.style.textAlign = "center";
  cell.style.color = color;
  cell.append(addFile);

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

  ajouterValeur(prototype, "numeroLigne", line); // numero de page
  ajouterValeur(prototype, "numeroFournisseur", fields.numeroFournisseur.value);
  ajouterValeur(prototype, "nomFournisseur", fields.fournisseur.value);
  ajouterValeur(prototype, "artRefp", fields.reference.value);
  ajouterValeur(prototype, "artDesi", fields.designation.value);
  ajouterValeur(prototype, "qteDispo", fields.qteDispo.value || 0);
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

    ajouterValeur(prototype, "numeroLigne", line); // numero de page
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

function handleFileNamesInputChange(e) {
  onFileNamesInputChangeDalr(e);
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

    ajouterValeur(prototype, "numeroLigne", line); // numero de page
    ajouterValeur(prototype, "numLigneTableau", rowIndex); // numero de ligne du tableau

    container.append(prototype);
    // 🔄 Maintenant que le prototype est dans le DOM, retrouve l'input file
    const inputFileInserted = prototype.querySelector(
      'input[type="file"][id*="fileNames"]'
    );

    if (inputFileInserted) {
      inputFileInserted.accept = ".pdf, image/*";
      // 🔁 Supprimer l'ancien listener si déjà ajouté
      inputFileInserted.removeEventListener(
        "change",
        handleFileNamesInputChange
      );
      inputFileInserted.addEventListener("change", handleFileNamesInputChange);

      inputFileInserted.click();
    } else {
      console.warn(
        "Le nouvel input file est introuvable dans le prototype cloné."
      );
    }
  } else {
    inputFile.accept = ".pdf, image/*";

    // 🔁 Supprimer l'ancien listener si déjà ajouté
    inputFile.removeEventListener("change", handleFileNamesInputChange);
    inputFile.addEventListener("change", handleFileNamesInputChange);

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
  const inputFile = event.target; // input file field
  const inputId = inputFile.id; // id de l'input

  // Initialiser la liste si elle n'existe pas encore
  if (!selectedFilesMap[inputId]) {
    selectedFilesMap[inputId] = [];
  }

  // Ajouter les nouveaux fichiers à la liste existante
  const currentFiles = Array.from(inputFile.files);
  selectedFilesMap[inputId].push(...currentFiles);

  // Nettoyer le champ file (pour permettre de re-sélectionner le même fichier plus tard si besoin)
  inputFile.value = "";

  transfererDonnees(inputId);

  // Afficher la liste des fichiers cumulés
  renderFileList(inputId);
}

function renderFileList(inputId) {
  const containerId = inputId.replace("fileNames", "fileNamesContainer");
  const fieldContainer = document.getElementById(containerId);
  const files = selectedFilesMap[inputId];

  // Vider l'affichage
  fieldContainer.innerHTML = "";

  if (files.length > 0) {
    const fileList = document.createElement("ul");
    fileList.classList.add("ps-0", "mb-0", "file-list");

    files.forEach((file, index) => {
      const listItem = document.createElement("li");
      listItem.classList.add("file-item");

      const fileNameSpan = document.createElement("span");
      fileNameSpan.classList.add("file-name");
      const a = document.createElement("a");
      a.href = URL.createObjectURL(file);
      a.textContent = file.name;
      // generateCustomFilename("PJ") +
      // `${index + 1}.${file.name.split(".").pop().toLowerCase()}`;
      a.target = "_blank";
      fileNameSpan.appendChild(a);

      const deleteBtn = document.createElement("span");
      deleteBtn.textContent = "x";
      deleteBtn.classList.add("remove-file");
      deleteBtn.onclick = () => {
        // Supprimer le fichier de la liste et re-render
        selectedFilesMap[inputId].splice(index, 1);
        transfererDonnees(inputId);
        renderFileList(inputId);
      };

      listItem.appendChild(fileNameSpan);
      listItem.appendChild(deleteBtn);
      fileList.appendChild(listItem);
    });

    fieldContainer.appendChild(fileList);
  }
}

/**
 * Déclenche un 'change' sur la première cellule si l'élément cliqué n'est pas un lien.
 * @param {MouseEvent} event - L'événement de clic
 */
export function handleRowClick(event) {
  // Si on a cliqué sur un <a> ou un de ses enfants, on ne fait rien
  if (event.target.closest("a")) return;

  /**
   * this représente le tr de la table
   * Déclencher l’événement "change" sur le premier élément de la première cellule
   */
  const target = this.cells[0].firstElementChild;
  if (target) {
    target.click();
  }
}

/** * Transfère les données d'un tableau de fichiers vers un champ input de type file.
 * @param {string} inputId - L'ID de l'inputFile
 */
function transfererDonnees(inputId) {
  // Créer un objet DataTransfer pour gérer les fichiers
  const dataTransfer = new DataTransfer();
  // Ajouter chaque fichier à l'objet DataTransfer
  selectedFilesMap[inputId].forEach((file) => {
    dataTransfer.items.add(file);
  });

  // Assigner les fichiers à l'input file
  document.getElementById(inputId).files = dataTransfer.files;
}
