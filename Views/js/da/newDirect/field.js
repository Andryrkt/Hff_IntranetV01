// Dictionnaire pour stocker les fichiers sélectionnés par champ input
const selectedFilesMap = {};

export function createFieldAndAppendTo(
  classe,
  prototype,
  fieldName,
  parentField
) {
  // Création du conteneur principal
  let fieldContainer = document.createElement("div");
  fieldContainer.classList.add(classe);

  // Champ à mettre dans le conteneur
  let field = prototype.querySelector(`[id*="${fieldName}"]`);
  // console.log(fieldName, field);

  let dateFinSouhaitee = document.getElementById(
    "demande_appro_direct_form_dateFinSouhaite"
  ).value;
  field.required = ![
    "commentaire",
    "catalogue",
    "numeroLigne",
    "fileNames",
    "artRefp",
    "numeroFournisseur",
    "estFicheTechnique",
    "deleted",
  ].includes(fieldName);

  if (fieldName === "dateFinSouhaite") {
    field.value = dateFinSouhaitee;
  } else if (fieldName === "numeroLigne") {
    field.value = localStorage.getItem("index");
  } else if (fieldName === "fileNames") {
    field.accept = ".pdf, image/*"; // Accepter les fichiers PDF et images
    field.addEventListener("change", (event) => onFileNamesInputChange(event));
  }

  // Append the field
  fieldContainer.appendChild(field);
  parentField.appendChild(fieldContainer);
}

export function createRemoveButtonAndAppendTo(prototype, parentField) {
  // Création du conteneur principal
  let fieldContainer = document.createElement("div");
  fieldContainer.classList.add("w-2");

  // Bouton supprimer
  let removeButton = document.createElement("span");
  removeButton.title = "Supprimer la ligne de DA";
  removeButton.style.cursor = "pointer";
  removeButton.innerHTML = '<i class="fas fa-times fs-4"></i>';
  removeButton.addEventListener("click", function () {
    document.getElementById(prototype.id).remove();
  });

  // Append the field
  fieldContainer.appendChild(removeButton);
  parentField.appendChild(fieldContainer);
}

export function createFileContainerAndAppendTo(
  className,
  prototype,
  parentField
) {
  // Création du conteneur principal
  let fieldContainer = document.createElement("div");
  fieldContainer.classList.add(className);

  fieldContainer.id = prototype
    .querySelector(`[id*="fileNames"]`)
    .id.replace("fileNames", "fileNamesContainer"); // Génération de l'ID pour le conteneur

  parentField.appendChild(fieldContainer);
}

export function createFileNamesLabelAndAppendTo(
  className,
  prototype,
  parentField
) {
  // Création du conteneur principal
  let fieldContainer = document.createElement("div");
  fieldContainer.classList.add(className);

  // Sélection de l'élément cible
  let fieldFileNames = prototype.querySelector(`[id*="fileNames"]`);

  let icon = document.createElement("i");
  icon.classList.add("fas", "fa-paperclip", "text-primary");
  icon.title = "Ajouter une pièce jointe";
  icon.style.cursor = "pointer";

  icon.addEventListener("click", function () {
    // Ouvrir le sélecteur de fichiers
    fieldFileNames.click();
  });

  // Append the label and field to the container
  fieldContainer.append(icon);
  parentField.appendChild(fieldContainer);
}

export function createFieldAutocompleteAndAppendTo(
  className,
  prototype,
  fieldName,
  parentField
) {
  // Création du conteneur principal
  let fieldContainer = document.createElement("div");
  fieldContainer.classList.add(className);

  // Sélection de l'élément cible
  let field = prototype.querySelector(`[id*="${fieldName}"]`);
  field.required = true; // champ requis

  // Génération des nouveaux IDs pour le spinner et le conteneur
  let baseId = field.id.replace("demande_appro_direct_form_DAL", "");

  // Création du conteneur du spinner
  let spinnerContainer = document.createElement("div");
  spinnerContainer.id = `spinner_container${baseId}`;
  spinnerContainer.style.display = "none";
  spinnerContainer.classList.add("text-center");

  // Création du conteneur de l'élément cible
  let containerDiv = document.createElement("div");
  containerDiv.id = `suggestion${baseId}`;
  containerDiv.classList.add("suggestions-container");

  // Ajout des éléments au conteneur principal
  fieldContainer.append(field, containerDiv, spinnerContainer);

  // Ajout du conteneur principal au parent
  parentField.appendChild(fieldContainer);
}

export function formatAllField(line) {
  let designation = getTheField(line, "artDesi");
  let fournisseur = getTheField(line, "nomFournisseur");
  let quantite = getTheField(line, "qteDem");
  designation.addEventListener("input", function () {
    designation.value = designation.value.toUpperCase();
  });
  fournisseur.addEventListener("input", function () {
    fournisseur.value = fournisseur.value.toUpperCase();
  });
  quantite.addEventListener("input", function () {
    quantite.value = quantite.value.replace(/[^\d]/g, "");
  });
}

export function getTheField(
  line,
  fieldName,
  prefixId = "demande_appro_direct_form_DAL"
) {
  return document.getElementById(`${prefixId}_${line}_${fieldName}`);
}

export function onFileNamesInputChange(event) {
  let inputFile = event.target; // input file field
  let inputId = inputFile.id; // id de l'input

  // Initialiser la liste si elle n'existe pas encore
  if (!selectedFilesMap[inputId]) {
    selectedFilesMap[inputId] = [];
  }

  // Récupérer les fichiers sélectionnés et valider leur taille
  const currentFiles = Array.from(inputFile.files).filter((file) =>
    isValidFile(file)
  );

  // Ajouter uniquement les fichiers valides
  selectedFilesMap[inputId].push(...currentFiles);

  // Nettoyer le champ file (pour permettre de re-sélectionner le même fichier plus tard si besoin)
  inputFile.value = "";
  // Assigner les fichiers à l'input file
  transfererDonnees(selectedFilesMap[inputId], inputFile);
  // Afficher la liste des fichiers cumulés
  renderFileList(inputId, inputFile);
}

function isValidFile(file, maxSize = 5 * 1024 * 1024) {
  if (file.size > maxSize) {
    Swal.fire({
      icon: "error",
      title: "Fichier trop volumineux",
      html: `Le fichier <strong>"${file.name}"</strong> dépasse la taille maximale autorisée de <strong>5 Mo</strong>. Il ne sera donc pas ajouté.`,
      confirmButtonText: "OK",
    });
    return false;
  }
  return true;
}

function renderFileList(inputId, inputFile) {
  const containerId = inputId.replace("fileNames", "fileNamesContainer");
  const fieldContainer = document.getElementById(containerId); // Conteneur du champ de fichier correspondant
  const files = selectedFilesMap[inputId];

  // Vider l'affichage
  fieldContainer.innerHTML = "";

  // Vérifier si un fichier a été sélectionné
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
      a.textContent = file.name; // Afficher le nom du fichier
      a.target = "_blank";
      fileNameSpan.appendChild(a);

      const deleteBtn = document.createElement("span");
      deleteBtn.textContent = "x";
      deleteBtn.classList.add("remove-file");
      deleteBtn.onclick = () => {
        // Supprimer le fichier de la liste et re-render
        selectedFilesMap[inputId].splice(index, 1);
        transfererDonnees(selectedFilesMap[inputId], inputFile);
        renderFileList(inputId, inputFile);
      };

      listItem.appendChild(fileNameSpan);
      listItem.appendChild(deleteBtn);
      fileList.appendChild(listItem);
    });
    fieldContainer.appendChild(fileList); // Ajouter le lien au conteneur
  }
}

/** * Transfère les données d'un tableau de fichiers vers un champ input de type file.
 * @param {File[]} filesArray - Le tableau de fichiers à transférer.
 * @param {HTMLInputElement} inputFile - Le champ input file où les fichiers seront assignés.
 */
function transfererDonnees(filesArray, inputFile) {
  // Créer un objet DataTransfer pour gérer les fichiers
  const dataTransfer = new DataTransfer();

  // Ajouter chaque fichier à l'objet DataTransfer
  filesArray.forEach((file) => {
    dataTransfer.items.add(file);
  });

  // Assigner les fichiers à l'input file
  inputFile.files = dataTransfer.files;
}
