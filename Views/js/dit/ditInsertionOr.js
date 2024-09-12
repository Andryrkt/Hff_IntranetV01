const dropzone = document.getElementById("dropzone");
const fileInput = document.getElementById("dit_insertion_or_file");
const uploadBtn = document.getElementById("upload-btn");
const form = document.getElementById("upload-form");

// Activer le click pour le sélecteur de fichier sur dropzone
dropzone.addEventListener("click", () => fileInput.click());

// Activer le click pour le sélecteur de fichier sur le bouton
uploadBtn.addEventListener("click", () => fileInput.click());

// Gestion du drag and drop
dropzone.addEventListener("dragover", (e) => {
  e.preventDefault();
  dropzone.style.backgroundColor = "#e8e8e8";
});

dropzone.addEventListener("dragleave", () => {
  dropzone.style.backgroundColor = "#fff";
});

dropzone.addEventListener("drop", (e) => {
  e.preventDefault();
  dropzone.style.backgroundColor = "#fff";

  if (e.dataTransfer.files.length) {
    fileInput.files = e.dataTransfer.files;
    form.submit();
  }
});

// Soumission du formulaire si un fichier est sélectionné via le bouton
fileInput.addEventListener("change", () => {
  form.submit();
});
