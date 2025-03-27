export class FileHandler {
  constructor(fileInput, fileList) {
    this.fileInput = fileInput;
    this.fileList = fileList;
    this.filesArray = [];
  }

  addFile(file) {
    if (
      !this.filesArray.some((f) => f.name === file.name && f.size === file.size)
    ) {
      this.filesArray.push(file);
      this.displayFile(file);
    }
  }

  displayFile(file) {
    const listItem = document.createElement('li');
    listItem.textContent = `${file.name} (${(file.size / 1024).toFixed(1)} Ko)`;
    this.fileList.appendChild(listItem);
  }

  removeFile(file) {
    this.filesArray = this.filesArray.filter((f) => f !== file);
  }
}

 /**
* Methode pour le draw and drop du fichier
* @param {*} idSuffix
*/
function initializeFileHandlers(idSuffix, idFichier) {
 const fileInput = document.querySelector(idFichier);
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
