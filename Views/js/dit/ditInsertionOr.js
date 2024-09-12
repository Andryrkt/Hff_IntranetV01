document.getElementById("upload-btn").addEventListener("click", function () {
  document.querySelector('input[type="file"]').click();
});

document
  .querySelector('input[type="file"]')
  .addEventListener("change", function () {
    handleFiles(this.files);
  });

document.getElementById("dropzone").addEventListener("dragover", function (e) {
  e.preventDefault();
  e.stopPropagation();
  this.style.backgroundColor = "#e2e6ea";
});

document.getElementById("dropzone").addEventListener("dragleave", function (e) {
  e.preventDefault();
  e.stopPropagation();
  this.style.backgroundColor = "#f8f9fa";
});

document.getElementById("dropzone").addEventListener("drop", function (e) {
  e.preventDefault();
  e.stopPropagation();
  const files = e.dataTransfer.files;
  document.querySelector('input[type="file"]').files = files;
  handleFiles(files);
  this.style.backgroundColor = "#f8f9fa";
});

function handleFiles(files) {
  const file = files[0];
  if (file && file.type === "application/pdf") {
    const reader = new FileReader();
    reader.onload = function (e) {
      const embed = document.getElementById("pdf-embed");
      embed.src = e.target.result;
      document.getElementById("pdf-preview").style.display = "block";
    };
    reader.readAsDataURL(file);
  } else {
    alert("Veuillez déposer un fichier PDF.");
  }
}

const numOrInput = document.querySelector("#dit_insertion_or_numeroOR");

numOrInput.addEventListener("input", function () {
  let value = numOrInput.value;

  // Retirer tous les caractères qui ne sont pas des chiffres
  value = value.replace(/[^0-9]/g, "");

  // Limiter la longueur à 8 caractères maximum
  value = value.slice(0, 8);

  // Appliquer la valeur filtrée au champ d'entrée
  numOrInput.value = value;
});
