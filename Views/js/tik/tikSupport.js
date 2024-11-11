/**
 * recuperer l'agence debiteur et changer le service debiteur selon l'agence
 */
const agenceDebiteurInput = document.querySelector(
  "#demande_support_informatique_agence"
);
const serviceDebiteurInput = document.querySelector(
  "#demande_support_informatique_service"
);
agenceDebiteurInput.addEventListener("change", selectAgence);

function selectAgence() {
  const agenceDebiteur = agenceDebiteurInput.value;
  console.log(agenceDebiteur);

  if (agenceDebiteur) {
    let url = `/Hffintranet/agence-fetch/${agenceDebiteur}`;
    fetch(url)
      .then((response) => response.json())
      .then((services) => {
        console.log(services);

        // Supprimer toutes les options existantes
        while (serviceDebiteurInput.options.length > 0) {
          serviceDebiteurInput.remove(0);
        }

        // Ajouter les nouvelles options à partir du tableau services
        for (var i = 0; i < services.length; i++) {
          var option = document.createElement("option");
          option.value = services[i].value;
          option.text = services[i].text;
          serviceDebiteurInput.add(option);
        }

        //Afficher les nouvelles valeurs et textes des options
        for (var i = 0; i < serviceDebiteurInput.options.length; i++) {
          var option = serviceDebiteurInput.options[i];
          console.log("Value: " + option.value + ", Text: " + option.text);
        }
      })
      .catch((error) => console.error("Error:", error));
  } else {
    serviceDebiteurInput.disabled = true;
    while (serviceDebiteurInput.options.length > 0) {
      serviceDebiteurInput.remove(0);
    }
  }
}

/**
 * FICHIER
 *
 */
document.addEventListener("DOMContentLoaded", function () {
  const fileInput = document.querySelector(".file-input");
  const dropzone = document.getElementById("dropzone");
  const fileList = document.getElementById("file-list");
  const paperclipIcon = document.getElementById("paperclip-icon");

  let filesArray = [];

  function displayFiles(files) {
    files.forEach((file) => {
      if (
        !filesArray.some((f) => f.name === file.name && f.size === file.size)
      ) {
        filesArray.push(file);

        const listItem = document.createElement("li");
        listItem.classList.add("file-item");

        const fileName = document.createElement("span");
        fileName.classList.add("file-name");
        fileName.textContent = file.name;

        const fileSize = document.createElement("span");
        fileSize.classList.add("file-size");
        fileSize.textContent = `(${(file.size / 1024).toFixed(0)} Ko)`;

        const removeButton = document.createElement("span");
        removeButton.textContent = "×";
        removeButton.classList.add("remove-file");
        removeButton.addEventListener("click", () => {
          filesArray = filesArray.filter((f) => f !== file);
          fileList.removeChild(listItem);
          updateFileInput();
        });

        // Spinner pour indiquer le chargement
        const spinner = document.createElement("div");
        spinner.classList.add("spinner");

        listItem.appendChild(fileName);
        listItem.appendChild(fileSize);
        listItem.appendChild(removeButton);
        listItem.appendChild(spinner);
        fileList.appendChild(listItem);

        // Démarrer le spinner pour simuler le chargement
        startLoading(spinner);
      }
    });
    updateFileInput();
  }

  function updateFileInput() {
    const dataTransfer = new DataTransfer();
    filesArray.forEach((file) => dataTransfer.items.add(file));
    fileInput.files = dataTransfer.files;
  }

  fileInput.addEventListener("change", function (event) {
    const files = Array.from(event.target.files);
    displayFiles(files);
  });

  paperclipIcon.addEventListener("click", function () {
    fileInput.click();
  });

  dropzone.addEventListener("dragover", (event) => {
    event.preventDefault();
    dropzone.classList.add("dragover");
  });

  dropzone.addEventListener("dragleave", () => {
    dropzone.classList.remove("dragover");
  });

  dropzone.addEventListener("drop", (event) => {
    event.preventDefault();
    dropzone.classList.remove("dragover");
    const files = Array.from(event.dataTransfer.files);
    displayFiles(files);
  });

  function startLoading(spinner) {
    // Simuler le chargement et retirer le spinner après 2 secondes
    setTimeout(() => {
      spinner.remove();
    }, 2000); // Retirer le spinner après 2 secondes
  }
});
