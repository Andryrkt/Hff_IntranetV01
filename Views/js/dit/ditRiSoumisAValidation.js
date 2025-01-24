/**
 * RECUPERATION DE FICHIER PDF ET AFFICHAGE POUR PIECE JOINT 1
 */
const fileInput1 = document.querySelector(
  "#dit_ri_soumis_a_validation_pieceJoint01"
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

/**
 * LIMITATION CARACTER ET OBLIGATION DE CARACTER EN CHIFFRE SUR LE CHAMP NUMERO OR
 */
const numOrInput = document.querySelector(
  "#dit_ri_soumis_a_validation_numeroOR"
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

/**
 * BLOCAGE DE SOUMISI SI AUCUNE CASE N'EST PAS COCHE
 */
document.addEventListener("DOMContentLoaded", function () {
  // Récupérer le formulaire
  var form = document.getElementById("upload-form");

  // Écouter l'événement de soumission
  form.addEventListener("submit", function (event) {
    // Sélectionner toutes les cases à cocher
    var checkboxes = document.querySelectorAll(
      'input[type="checkbox"][id^="dit_ri_soumis_a_validation_checkbox_"]'
    );
    console.log(checkboxes);

    var atLeastOneChecked = false;

    // Vérifier si au moins une case est cochée
    checkboxes.forEach(function (checkbox) {
      if (checkbox.checked) {
        atLeastOneChecked = true;
      }
    });

    // Si aucune case n'est cochée, bloquer la soumission et afficher un message d'erreur
    if (!atLeastOneChecked) {
      event.preventDefault(); // Empêche la soumission du formulaire
      alert("Veuillez cocher le(s) intervention(s) à valider.");
    }
  });
});
