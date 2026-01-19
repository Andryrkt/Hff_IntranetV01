import { displayOverlay } from "../../utils/ui/overlay";

window.addEventListener("load", () => {
  const conversationContainer = document.getElementById(
    "conversationContainer"
  );

  if (!conversationContainer) return;

  const interval = setInterval(() => {
    const firstChild = conversationContainer.firstElementChild;

    if (firstChild && firstChild.offsetHeight > 0) {
      // Le contenu est prêt, on peut scroller en bas
      conversationContainer.scrollTop = conversationContainer.scrollHeight;

      // Stoppe le setInterval
      clearInterval(interval);
    }
  }, 100);
});

document.addEventListener("DOMContentLoaded", function () {
  // ===================================================
  // GESTION DU TEXTAREA AUTO-RESIZE ET ENVOI FORMULAIRE
  // ===================================================
  const messageInput = document.getElementById("da_observation_observation");

  if (messageInput) {
    // Auto-resize du textarea
    messageInput.addEventListener("input", function () {
      this.style.height = "auto";
      this.style.height = Math.min(this.scrollHeight, 120) + "px";
    });

    const form = messageInput.closest("form");
    if (form) {
      form.addEventListener("submit", function () {
        displayOverlay(true, "Envoi en cours...");
        // Si des fichiers sont sélectionnés, créer un DataTransfer pour les ajouter
        if (selectedFiles.length > 0) {
          const dataTransfer = new DataTransfer();
          selectedFiles.forEach((file) => {
            dataTransfer.items.add(file);
          });
          fileInput.files = dataTransfer.files;
        }

        // Reset de la hauteur après envoi
        setTimeout(() => {
          messageInput.style.height = "auto";
          messageInput.value = "";
        }, 100);
      });
    }
  }

  // ========================================
  // GESTION DES FICHIERS JOINTS
  // ========================================
  const fileInput = document.getElementById("da_observation_fileNames");
  const attachedFilesPreview = document.querySelector(
    ".attached-files-preview"
  );
  const attachedFilesList = document.querySelector(".attached-files-list");
  let selectedFiles = [];

  if (fileInput) {
    fileInput.addEventListener("change", function () {
      const files = Array.from(this.files).filter((file) => isValidFile(file));

      // Ajouter les nouveaux fichiers
      files.forEach((file) => {
        selectedFiles.push(file);
      });

      updateFilePreview();
    });
  }

  const allOldFiles = document.querySelectorAll(".file-comment span");
  allOldFiles.forEach((oldFile) => {
    oldFile.addEventListener("click", function (event) {
      displayFile(event);
    });
  });

  // Modifier isValidFile pour éviter les doublons
  function isValidFile(file, maxSize = 5 * 1024 * 1024) {
    // Vérifier la taille
    if (file.size > maxSize) {
      Swal.fire({
        icon: "error",
        title: "Fichier trop volumineux",
        html: `Le fichier <strong>"${file.name}"</strong> dépasse la taille maximale autorisée de <strong>5 Mo</strong>.`,
        confirmButtonText: "OK",
      });
      return false;
    }

    // Vérifier les doublons dans selectedFilesMap
    const isDuplicate = selectedFiles.find(
      (f) => f.name === file.name && f.size === file.size
    );

    if (isDuplicate) {
      Swal.fire({
        icon: "warning",
        title: "Fichier déjà ajouté",
        html: `Le fichier <strong>"${file.name}"</strong> est déjà dans la liste.`,
        confirmButtonText: "OK",
      });
      return false;
    }

    return true;
  }

  function updateFilePreview() {
    if (selectedFiles.length === 0) {
      attachedFilesPreview.classList.add("d-none");
      return;
    }

    // Afficher la zone de preview
    attachedFilesPreview.classList.remove("d-none");

    // Mettre à jour le compteur
    const header = attachedFilesPreview.querySelector(
      ".attached-files-header small"
    );
    header.innerHTML = `<i class="fas fa-paperclip me-1"></i>Fichiers joints (${selectedFiles.length})`;

    // Vider et reconstruire la liste
    attachedFilesList.innerHTML = "";

    selectedFiles.forEach((file, index) => {
      const fileItem = createFileItem(file, index);
      attachedFilesList.appendChild(fileItem);
    });
  }

  function formatFileSize(bytes) {
    if (bytes === 0) return "0 B";

    const k = 1024;
    const sizes = ["B", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + " " + sizes[i];
  }

  function removeFile(index) {
    selectedFiles.splice(index, 1);
    updateFilePreview();

    // Réinitialiser l'input file
    if (fileInput) {
      fileInput.value = "";
    }
  }

  function createFileItem(file, index) {
    const div = document.createElement("div");
    div.className = "attached-file-item";

    // Formater la taille du fichier
    const size = formatFileSize(file.size);

    div.innerHTML = `
        <i class="fas fa-file-pdf text-primary"></i>
        <span class="file-name" title="${file.name}">${file.name}</span>
        <span class="file-size">${size}</span>
        <button class="btn-remove-file" type="button" data-index="${index}">
            <i class="fas fa-times"></i>
        </button>
    `;

    // Ajouter l'évènement au clic de l'élement
    div.addEventListener("click", function (e) {
      displayFile(e, file);
    });

    // Ajouter l'événement de suppression
    const removeBtn = div.querySelector(".btn-remove-file");
    removeBtn.addEventListener("click", function () {
      removeFile(index);
    });

    return div;
  }

  function displayFile(event, file = null) {
    if (event.target.closest("button.btn-remove-file")) return; // ignore le clic sur l'icône de suppression

    console.log(event.target);

    const filePath = file
      ? URL.createObjectURL(file)
      : event.target.dataset.filePath;
    const fileName = file ? file.name : event.target.dataset.fileName;
    const viewer = document.getElementById("file-viewer-observation");
    let textHtml = "";

    // Cas PDF
    if (fileName.endsWith(".pdf")) {
      viewer.innerHTML = `<embed src="${filePath}" type="application/pdf" width="100%" height="800"/>`;
    }
    // Cas format non supporté
    else {
      textHtml = `Le format du fichier du <strong class="text-danger">"${fileName}"</strong> n'est pas pris en charge pour l'affichage.`;
      Swal.fire({
        icon: "error",
        title: "Fichier non supporté",
        html: textHtml,
        confirmButtonText: "OK",
      });
      viewer.innerHTML = textHtml;
    }
  }
});
