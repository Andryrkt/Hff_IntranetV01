function initializeFileHandlers(idSuffix) {
  const fileInput = document.querySelector(
    `#dit_ors_soumis_a_validation_pieceJoint${idSuffix}`
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

// Utilisation pour plusieurs fichiers
["01", "02", "03", "04"].forEach((idSuffix) => {
  initializeFileHandlers(idSuffix);
});

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
