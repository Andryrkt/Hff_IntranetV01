/**
 * RECUPERATION DE FICHIER PDF ET AFFICHAGE POUR PIECE JOINT 1
 */
const fileInput1 = document.querySelector(
  "#dit_cde_soumis_a_validation_pieceJoint01"
);
const fileName1 = document.querySelector(".file-name-1");
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
    fileName1.innerHTML = `<strong>Fichier sélectionné :</strong>  ${fileInput1.files[0].name} `;
    fileSize1.innerHTML = `<strong>Taille : </strong> ${formatFileSize(
      fileInput1.size
    )}`;
  } else {
    fileName1.textContent = "";
    fileSize1.textContent = "";
  }
});

function handleFiles1(files) {
  // Vérifiez si des fichiers ont été sélectionnés
  if (files.length === 0) {
    alert("Veuillez sélectionner un fichier.");
    return; // Sort de la fonction si aucun fichier n'est sélectionné
  }

  const file = files[0];
  if (file && file.type === "application/pdf") {
    const reader = new FileReader();
    reader.onload = function (e) {
      const embed = document.getElementById("pdf-embed-1");
      console.log(embed);
      embed.src = e.target.result;
      embed.title = fileInput1.files[0].name;
      document.getElementById("pdf-preview-1").style.display = "block";
    };
    reader.readAsDataURL(file);
  } else {
    alert("Veuillez déposer un fichier PDF.");
  }
}
