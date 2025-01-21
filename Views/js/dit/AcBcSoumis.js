/**
 * Methode pour le draw and drop du fichier
 * @param {*} idSuffix
 */
function initializeFileHandlers(idSuffix) {
  const fileInput = document.querySelector(`#ac_soumis_pieceJoint0${idSuffix}`);
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

/**
 * Methode pour l'autocomplet nom client
 */
document.addEventListener("DOMContentLoaded", function () {
  let preloadedData = [];

  /**
   * Fonction pour charger tous les données au début (avant l'evenement)
   */
  async function preloadData(url) {
    try {
      const response = await fetch(url);
      preloadedData = await response.json(); // Stocke les données
    } catch (error) {
      console.error("Erreur lors du préchargement des données :", error);
    }
  }

  const url = "/Hffintranet/autocomplete/all-client";
  preloadData(url); //recupérer les donner à partir de l'url

  const nomClientInput = document.querySelector("#ac_soumis_nomClient");
  const suggestionContainer = document.querySelector("#suggestion");

  nomClientInput.addEventListener("input", filtrerLesDonner);

  /**
   * Methode permet de filtrer les donner selon les donnée saisi dans l'input
   */
  function filtrerLesDonner() {
    const nomClient = nomClientInput.value.trim();

    // Si l'input est vide, efface les suggestions et arrête l'exécution
    if (nomClient === "") {
      suggestionContainer.innerHTML = ""; // Efface les suggestions
      return;
    }

    // let filteredData = [];

    const filteredData = preloadedData.filter((item) => {
      const phrase = item.label + " - " + item.value;
      return phrase.toLowerCase().includes(nomClient.toLowerCase());
    });

    showSuggestions(suggestionContainer, filteredData);
  }

  /**
   * Methode permet d'afficher les donner sur le div du suggestion
   * @param {HTMLElement} suggestionsContainer
   * @param {Array} data
   */
  function showSuggestions(suggestionsContainer, data) {
    console.log(data.length === 0);

    // Vérifie si le tableau est vide
    if (data.length === 0) {
      suggestionsContainer.innerHTML = ""; // Efface les suggestions
      return; // Arrête l'exécution de la fonction
    }

    suggestionsContainer.innerHTML = ""; // Efface les suggestions existantes
    data.forEach((item) => {
      const suggestion = document.createElement("div");
      suggestion.textContent = item.label + " - " + item.value; // Affiche le label
      suggestion.addEventListener("click", () => {
        nomClientInput.value = item.label + " - " + item.value; // Remplit le champ avec la sélection
        suggestionsContainer.innerHTML = ""; // Efface les suggestions
      });
      suggestionsContainer.appendChild(suggestion);
    });
  }
});
