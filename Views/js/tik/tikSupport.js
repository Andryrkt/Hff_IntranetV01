import { FetchManager } from "../api/FetchManager";

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

// Instanciation de FetchManager avec la base URL
const fetchManager = new FetchManager();

function selectAgence() {
  const agenceDebiteur = agenceDebiteurInput.value;
  console.log(agenceDebiteur);

  if (agenceDebiteur) {
    let url = `api/agence-fetch/${agenceDebiteur}`;
    fetchManager
      .get(url)
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
 * FICHIER (Ajout)
 *
 */
document.addEventListener("DOMContentLoaded", function () {
  const fileInput = document.querySelector(".file-input");
  const dropzone = document.getElementById("dropzone");
  const fileList = document.getElementById("file-list");
  const paperclipIcon = document.getElementById("paperclip-icon");
  const infoIcon = document.getElementById("info-icon");
  const myForm = document.querySelector("form");

  function validerEmail(email) {
    const regex =
      /^[a-zA-Z0-9._%+-]+@(hff\.mg|natema\.mg|airways\.hff\.mg|travel\.hff\.mg|somava\.mg)$/;
    return regex.test(email);
  }

  myForm.addEventListener("submit", function (event) {
    const email = document.getElementById("user-email").getAttribute("data");
    if (!validerEmail(email)) {
      const modal = new bootstrap.Modal(
        document.getElementById("modalNouveauTicket")
      );
      document.getElementById("modal-nouveau-ticket-content").innerHTML = email;
      modal.show();
      event.preventDefault();
    }
  });

  let filesArray = [];
  const existingFiles = Array.from(document.querySelectorAll(".file-item"));

  // Ajouter les fichiers existants à filesArray
  existingFiles.forEach((fileItem) => {
    filesArray.push({
      id: fileItem.getAttribute("data-id"),
      name: fileItem.querySelector(".file-name").textContent,
      size: parseInt(fileItem.querySelector(".file-size").textContent),
      existing: true, // Marque comme fichier existant en base
    });

    const removeButton = fileItem.querySelector(".remove-file");
    removeButton.addEventListener("click", () => {
      // Supprimer visuellement et logiquement le fichier
      filesArray = filesArray.filter(
        (f) => f.id !== removeButton.getAttribute("data-id")
      );
      fileList.removeChild(fileItem);
    });
  });

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
        fileSize.textContent = `(${(file.size / 1024).toFixed(1)} Ko)`;

        const removeButton = document.createElement("span");
        removeButton.textContent = "×";
        removeButton.classList.add("remove-file");
        removeButton.addEventListener("click", () => {
          filesArray = filesArray.filter((f) => f !== file);
          fileList.removeChild(listItem);
          updateFileInput();
        });

        const spinner = document.createElement("div");
        spinner.classList.add("spinner");

        listItem.appendChild(fileName);
        listItem.appendChild(fileSize);
        listItem.appendChild(removeButton);
        listItem.appendChild(spinner);
        fileList.appendChild(listItem);

        startLoading(spinner);
      } else {
        alert("Merci de choisir un autre fichier.");
      }
    });
    updateFileInput();
  }

  function updateFileInput() {
    const dataTransfer = new DataTransfer();
    filesArray
      .filter((file) => !file.existing) // Inclut uniquement les nouveaux fichiers dans l'input
      .forEach((file) => dataTransfer.items.add(file));
    fileInput.files = dataTransfer.files;
  }

  fileInput.addEventListener("change", function (event) {
    const files = Array.from(event.target.files);
    displayFiles(files);
  });

  paperclipIcon.addEventListener("click", function () {
    fileInput.click();
  });

  infoIcon.addEventListener("click", function () {
    const notice = document.getElementById("notice");
    notice.classList.add("emphasis");
    setTimeout(() => {
      notice.classList.remove("emphasis");
    }, 510);
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
    setTimeout(() => {
      spinner.remove();
    }, 2000);
  }
});
