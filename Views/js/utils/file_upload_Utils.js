export function initializeFileHandlers(idSuffix, fileInpute) {
  const fileInput = fileInpute;
  const fileName = document.querySelector(`#file-name-${idSuffix}`);
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

export function handleFiles(
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
      if (pdfPreviewElement) {
        pdfPreviewElement.style.display = "block";
      }
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

export function formatFileSize(size) {
  const units = ["B", "KB", "MB", "GB"];
  let unitIndex = 0;
  let adjustedSize = size;

  while (adjustedSize >= 1024 && unitIndex < units.length - 1) {
    adjustedSize /= 1024;
    unitIndex++;
  }

  return `${adjustedSize.toFixed(2)} ${units[unitIndex]}`;
}

export function disableDropzone(id) {
  const dropzone = document.getElementById(`dropzone-${id}`);
  const uploadBtn = document.getElementById(`upload-btn-${id}`);
  const hiddenInput = document.getElementById(`hidden-input-${id}`);

  dropzone.classList.add("disabled-dropzone");
  uploadBtn.disabled = true;
  //   hiddenInput.disabled = true; // Si c'est un input type file
}

export function enableDropzone(id) {
  const dropzone = document.getElementById(`dropzone-${id}`);
  const uploadBtn = document.getElementById(`upload-btn-${id}`);
  const hiddenInput = document.getElementById(`hidden-input-${id}`);

  dropzone.classList.remove("disabled-dropzone");
  uploadBtn.disabled = false;
  //   hiddenInput.disabled = false;
}

/**==================================================================
 * TRAITEMENT DE FICHIER MULTIPLE
 *====================================================================*/
let fileStore = [];

export function initializeFileHandlersMultiple(idSuffix, fileInpute) {
  const fileInput = fileInpute;
  const fileList = document.querySelector(`#file-list-${idSuffix}`);
  const uploadBtn = document.getElementById(`upload-btn-${idSuffix}`);
  const dropzone = document.getElementById(`dropzone-${idSuffix}`);

  fileInput.multiple = true;

  // uploadBtn.addEventListener("click", () => fileInput.click());
  // dropzone.addEventListener("click", () => fileInput.click());

  uploadBtn.addEventListener("click", function () {
    fileInput.click();
  });

  fileInput.addEventListener("change", function () {
    mergeFiles(this.files);
    renderFiles(fileStore, fileList);
    updateInputFiles();
  });

  dropzone.addEventListener("dragover", function (e) {
    e.preventDefault();
    e.stopPropagation();
    this.style.backgroundColor = "#e2e6ea";
  });

  dropzone.addEventListener("dragleave", function (e) {
    e.preventDefault();
    e.stopPropagation();
    this.style.backgroundColor = "#ffffff";
  });

  dropzone.addEventListener("drop", function (e) {
    e.preventDefault();
    e.stopPropagation();
    mergeFiles(e.dataTransfer.files);
    renderFiles(fileStore, fileList);
    updateInputFiles();
    this.style.backgroundColor = "#ffffff";
  });

  function mergeFiles(newFiles) {
    const existingNames = fileStore.map((f) => f.name);
    for (const f of newFiles) {
      if (!existingNames.includes(f.name)) {
        fileStore.push(f);
      }
    }
  }

  function updateInputFiles() {
    const dataTransfer = new DataTransfer();
    fileStore.forEach((file) => dataTransfer.items.add(file));
    fileInput.files = dataTransfer.files;
  }
}

function renderFiles(files, container) {
  container.innerHTML = "";

  files.forEach((file, index) => {
    const wrapper = document.createElement("div");
    wrapper.className = "mb-3 p-2 border rounded position-relative";
    wrapper.style.position = "relative";

    // Bouton de suppression
    const removeBtn = document.createElement("button");
    removeBtn.innerHTML = "&times;";
    removeBtn.setAttribute("type", "button");
    removeBtn.className = "btn btn-danger btn-sm position-absolute";
    removeBtn.style.top = "5px";
    removeBtn.style.right = "5px";
    removeBtn.title = "Supprimer ce fichier";
    removeBtn.onclick = () => {
      fileStore.splice(index, 1); // Supprimer du tableau
      renderFiles(fileStore, container); // Re-rendre
    };

    const info = document.createElement("div");
    info.innerHTML = `<strong>${file.name}</strong> - ${(
      file.size / 1024
    ).toFixed(2)} KB`;
    wrapper.appendChild(removeBtn);
    wrapper.appendChild(info);

    if (file.type === "application/pdf") {
      const reader = new FileReader();
      reader.onload = function (e) {
        const embed = document.createElement("embed");
        embed.src = e.target.result;
        embed.type = "application/pdf";
        embed.width = "100%";
        embed.height = "200px";
        embed.className = "mt-2 border";
        wrapper.appendChild(embed);
      };
      reader.readAsDataURL(file);
    }

    container.appendChild(wrapper);
  });
}

/**================================================================
 *TRAITEMENT DE FICHIER NOUVEAU
 *================================================================*/
export function initializeFileHandlersNouveau(idSuffix, fileInputElement) {
  const fileInput = fileInputElement;
  const uploadBtn = document.getElementById(`upload-btn-${idSuffix}`);
  const dropzone = document.getElementById(`dropzone-${idSuffix}`);
  const fileList = document.getElementById(`file-list-${idSuffix}`);

  uploadBtn.addEventListener("click", function () {
    fileInput.click();
  });

  fileInput.addEventListener("change", function () {
    handleFile(this.files, fileList);
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
    handleFile(files, fileList);
    this.style.backgroundColor = "#f8f9fa";
  });
}

export function handleFile(files, fileListElement, maxSizeMB = 5) {
  const file = files[0];
  fileListElement.innerHTML = "";

  if (!file) return;

  const maxSizeBytes = maxSizeMB * 1024 * 1024;

  if (file.type !== "application/pdf") {
    alert("Veuillez sélectionner un fichier PDF.");
    return;
  }

  if (file.size > maxSizeBytes) {
    alert(`Le fichier est trop volumineux (max ${maxSizeMB} Mo).`);
    return;
  }

  const container = document.createElement("div");
  container.className = "position-relative border rounded p-2";

  // Bouton de suppression
  const removeBtn = document.createElement("button");
  removeBtn.innerHTML = "&times;";
  removeBtn.type = "button";
  removeBtn.className = "btn btn-sm btn-danger position-absolute";
  removeBtn.style.top = "5px";
  removeBtn.style.right = "5px";
  removeBtn.title = "Supprimer le fichier";
  removeBtn.onclick = () => {
    fileListElement.innerHTML = "";
  };

  // Infos du fichier
  const info = document.createElement("div");
  info.innerHTML = `<strong>${file.name}</strong> – ${(
    file.size / 1024
  ).toFixed(2)} KB`;

  container.appendChild(removeBtn);
  container.appendChild(info);

  // Aperçu PDF
  const reader = new FileReader();
  reader.onload = function (e) {
    const embed = document.createElement("embed");
    embed.src = e.target.result;
    embed.type = "application/pdf";
    embed.width = "100%";
    embed.height = "300px";
    embed.className = "mt-2 border";
    container.appendChild(embed);
  };
  reader.readAsDataURL(file);

  fileListElement.appendChild(container);
}
