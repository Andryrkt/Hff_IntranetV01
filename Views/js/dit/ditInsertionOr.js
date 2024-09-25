/**
 * RECUPERATION DE FICHIER PDF ET AFFICHAGE POUR PIECE JOINT 1
 */

const fileInput2 = document.querySelector(
  "#dit_ors_soumis_a_validation_pieceJoint02"
);
const fileName2 = document.getElementById("file-name-2");
const uploadBtn2 = document.getElementById("upload-btn-2");
const dropzone2 = document.getElementById("dropzone-2");
const fileSize2 = document.getElementById("file-size-2");

uploadBtn2.addEventListener("click", function () {
  fileInput2.click();
});

fileInput2.addEventListener("change", function () {
  handleFiles2(this.files);
});

dropzone2.addEventListener("dragover", function (e) {
  e.preventDefault();
  e.stopPropagation();
  this.style.backgroundColor = "#e2e6ea";
});

dropzone2.addEventListener("dragleave", function (e) {
  e.preventDefault();
  e.stopPropagation();
  this.style.backgroundColor = "#f8f9fa";
});

dropzone2.addEventListener("drop", function (e) {
  e.preventDefault();
  e.stopPropagation();
  const files = e.dataTransfer.files;
  fileInput2.files = files;
  handleFiles2(files);
  this.style.backgroundColor = "#f8f9fa";
});

fileInput2.addEventListener("change", function () {
  if (fileInput2.files.length > 0) {
    fileName2.textContent = "Fichier sélectionné : " + fileInput2.files[0].name;
    fileSize2.textContent = "Taille : " + formatFileSize(fileInput2.size);
  } else {
    fileName2.textContent = "";
  }
});

function handleFiles2(files) {
  const file = files[0];
  if (file && file.type === "application/pdf") {
    const reader = new FileReader();
    reader.onload = function (e) {
      const embed = document.getElementById("pdf-embed-2");
      embed.src = e.target.result;
      document.getElementById("pdf-preview-2").style.display = "block";
    };
    reader.readAsDataURL(file);
  } else {
    alert("Veuillez déposer un fichier PDF.");
  }
}

/**
 * RECUPERATION DE FICHIER PDF ET AFFICHAGE POUR PIECE JOINT 1
 */
const fileInput1 = document.querySelector(
  "#dit_ors_soumis_a_validation_pieceJoint01"
);
const fileName1 = document.getElementById("file-name-1");
const uploadBtn1 = document.getElementById("upload-btn-1");
const dropzone1 = document.getElementById("dropzone-1");
const fileSize1 = document.getElementById("file-size-1");

uploadBtn1.addEventListener("click", function () {
  fileInput1.click();
});

fileInput1.addEventListener("change", function () {
  handleFiles1(this.files);
});

dropzone1.addEventListener("dragover", function (e) {
  e.preventDefault();
  e.stopPropagation();
  this.style.backgroundColor = "#e2e6ea";
});

dropzone1.addEventListener("dragleave", function (e) {
  e.preventDefault();
  e.stopPropagation();
  this.style.backgroundColor = "#f8f9fa";
});

dropzone1.addEventListener("drop", function (e) {
  e.preventDefault();
  e.stopPropagation();
  const files = e.dataTransfer.files;
  fileInput1.files = files;
  handleFiles1(files);
  this.style.backgroundColor = "#f8f9fa";
});

fileInput1.addEventListener("change", function () {
  if (fileInput1.files.length > 0) {
    fileName1.textContent = "Fichier sélectionné : " + fileInput1.files[0].name;
    fileSize1.textContent = "Taille : " + formatFileSize(fileInput1.size);
  } else {
    fileName1.textContent = "";
  }
});

function handleFiles1(files) {
  const file = files[0];
  if (file && file.type === "application/pdf") {
    const reader = new FileReader();
    reader.onload = function (e) {
      const embed = document.getElementById("pdf-embed-1");
      embed.src = e.target.result;
      document.getElementById("pdf-preview-1").style.display = "block";
    };
    reader.readAsDataURL(file);
  } else {
    alert("Veuillez déposer un fichier PDF.");
  }
}

/**
 * LIMITATION CARACTER ET OBLIGATION DE CARACTER EN CHIFFRE SUR LE CHAMP NUMERO OR
 */
const numOrInput = document.querySelector(
  "#dit_ors_soumis_a_validation_numeroOR"
);

numOrInput.addEventListener("input", function () {
  let value = numOrInput.value;

  // Retirer tous les caractères qui ne sont pas des chiffres
  value = value.replace(/[^0-9]/g, "");

  // Limiter la longueur à 8 caractères maximum
  value = value.slice(0, 8);

  // Appliquer la valeur filtrée au champ d'entrée
  numOrInput.value = value;
});

// Fonction pour formater la taille des fichiers en Ko ou Mo
function formatFileSize(bytes) {
  if (bytes >= 1048576) {
    return (bytes / 1048576).toFixed(2) + " MB";
  } else {
    return (bytes / 1024).toFixed(2) + " KB";
  }
}
