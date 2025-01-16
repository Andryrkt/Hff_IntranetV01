import { configDevis } from "./config/devisDitConfig";
import {
  affichageOverlay,
  affichageSpinner,
} from "../utils/ui/uiSpinnerUtils.js";

function initializeFileHandlers(idSuffix) {
  const fileInput = document.querySelector(
    `#dit_devis_soumis_a_validation_pieceJoint0${idSuffix}`
  );
  const fileName = document.querySelector(`.file-name-${idSuffix}`);
  const uploadBtn = document.getElementById(`upload-btn-${idSuffix}`);
  const dropzone = document.getElementById(`dropzone-${idSuffix}`);
  const fileSize = document.getElementById(`file-size-${idSuffix}`);
  const pdfPreview = document.getElementById(`pdf-preview-${idSuffix}`);
  const pdfEmbed = document.getElementById(`pdf-embed-${idSuffix}`);

  uploadBtn.addEventListener("click", function () {
    fileInput.click();
  });

  fileInput.addEventListener("change", function () {
    handleFiles(this.files, fileName, fileSize, pdfPreview, pdfEmbed);
  });

  dropzone.addEventListener("dragover", function (e) {
    e.preventDefault();
    e.stopPropagation();
    this.style.backgroundColor = "#e2e6ea";
  });

  dropzone.addEventListener("dragleave", function (e) {
    e.preventDefault();
    e.stopPropagation();
    this.style.backgroundColor = "#f8f9fa";
  });

  dropzone.addEventListener("drop", function (e) {
    e.preventDefault();
    e.stopPropagation();
    const files = e.dataTransfer.files;
    fileInput.files = files;
    handleFiles(files, fileName, fileSize, pdfPreview, pdfEmbed);
    this.style.backgroundColor = "#f8f9fa";
  });
}

function handleFiles(
  files,
  fileNameElement,
  fileSizeElement,
  pdfPreviewElement,
  pdfEmbedElement
) {
  const file = files[0];
  if (file && file.type === "application/pdf") {
    const reader = new FileReader();
    reader.onload = function (e) {
      pdfEmbedElement.src = e.target.result;
      pdfPreviewElement.style.display = "block";
    };
    reader.readAsDataURL(file);

    fileNameElement.innerHTML = `<strong>Fichier sélectionné :</strong> ${file.name}`;
    fileSizeElement.innerHTML = `<strong>Taille :</strong> ${formatFileSize(
      file.size
    )}`;
  } else {
    alert("Veuillez déposer un fichier PDF.");
    fileNameElement.textContent = "";
    fileSizeElement.textContent = "";
  }
}

function formatFileSize(size) {
  const units = ["B", "KB", "MB", "GB"];
  let unitIndex = 0;
  let adjustedSize = size;

  while (adjustedSize >= 1024 && unitIndex < units.length - 1) {
    adjustedSize /= 1024;
    unitIndex++;
  }

  return `${adjustedSize.toFixed(2)} ${units[unitIndex]}`;
}

// Utilisation pour plusieurs fichier
initializeFileHandlers("1");

// Fonction pour formater la taille des fichiers en Ko ou Mo
// function formatFileSize(bytes) {
//   if (bytes >= 1048576) {
//     return (bytes / 1048576).toFixed(2) + " MB";
//   } else {
//     return (bytes / 1024).toFixed(2) + " KB";
//   }
// }

/**==================================================
 * sweetalert pour le bouton cloturer dit
 *==================================================*/

configDevis.btnEnregistre.addEventListener("click", (e) => {
  e.preventDefault();
  let numDevis = configDevis.btnEnregistre.getAttribute("data-devis");
  let numDit = configDevis.btnEnregistre.getAttribute("data-dit");
  console.log(numDevis, numDit);

  Swal.fire({
    title: "Êtes-vous sûr ?",
    text: `Vous êtes en train de soumettre le devis N° ${numDevis} à validation dans DocuWare `,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#fbbb01",
    cancelButtonColor: "#d33",
    confirmButtonText: "OUI",
  }).then((result) => {
    if (result.isConfirmed) {
      Swal.fire({
        title: "Soumission!",
        text: "Veuillez de ne pas fermer l’onglet durant le traitement.",
        icon: "warning",
      }).then((res) => {
        // Afficher un overlay de chargement
        affichageOverlay();

        // Ajouter un spinner CSS
        affichageSpinner();
        // Soumettre le formulaire
        const form = document.querySelector("#upload-form");
        form.submit();
      });
    }
  });
});

// Redirection après confirmation
//  window.location.href = `/Hffintranet/insertion-devis/${numDit}`;
