
document.addEventListener("DOMContentLoaded", function () {

  /**
   * BOUTON REFUSER ET VALIDER DU VALIDATEUR
   */
  const validerBtn       = document.querySelector("#btn_valider");
  const refuserBtn       = document.querySelector("#btn_refuser");

  const tikCategorie     = document.querySelector("#detail_tik_categorie");
  const tikNiveauUrgence = document.querySelector("#detail_tik_niveauUrgence");
  const tikIntervenant   = document.querySelector("#detail_tik_intervenant");
  const tikCommentaires  = document.querySelector("#detail_tik_commentaires");

  if (refuserBtn !== null) {
    refuserBtn.addEventListener("click", function () {
      tikCategorie.removeAttribute("required");
      tikNiveauUrgence.removeAttribute("required");
      tikIntervenant.removeAttribute("required");
      tikCommentaires.setAttribute("required", "required");
    });
  }

  if (validerBtn !== null) {
    validerBtn.addEventListener("click", function () {
      tikCategorie.setAttribute("required", "required");
      tikNiveauUrgence.setAttribute("required", "required");
      tikIntervenant.setAttribute("required", "required");
      tikCommentaires.removeAttribute("required");
    });
  }

  /**
   * BOUTON RESOUDRE, PLANIFIER ET TRANSFERER DE L'INTERVENANT
   */
  const resoudreBtn   = document.querySelector("#btn_resoudre");
  const transfererBtn = document.querySelector("#btn_transferer");
  const planifierBtn  = document.querySelector("#btn_planifier");

  const dateDebutPlanning = document.querySelector("#detail_tik_dateDebutPlanning");
  const dateFinPlanning   = document.querySelector("#detail_tik_dateFinPlanning");

  if (resoudreBtn !== null) {
    resoudreBtn.addEventListener("click", function () {
      tikCommentaires.setAttribute("required", "required");
      tikIntervenant.removeAttribute("required");
      dateDebutPlanning.removeAttribute("required");
      dateFinPlanning.removeAttribute("required");
    });
  }

  if (transfererBtn !== null) {
    transfererBtn.addEventListener("click", function () {
      tikCommentaires.removeAttribute("required");
      tikIntervenant.setAttribute("required", "required");
      dateDebutPlanning.removeAttribute("required");
      dateFinPlanning.removeAttribute("required");
    });
  }

  if (planifierBtn !== null) {
    planifierBtn.addEventListener("click", function () {
      tikCommentaires.removeAttribute("required");
      tikIntervenant.removeAttribute("required");
      dateDebutPlanning.setAttribute("required", "required");
      dateFinPlanning.setAttribute("required", "required");
    });
  }

  dateDebutPlanning.addEventListener("change", validateDates);
  dateFinPlanning.addEventListener("change", validateDates);

  /**
   * Formulaire 
   */
  const myForm = document.getElementById('formTik');
  
  function validateDates() {
    const errorMessage = document.querySelector('.error-message');
    const startDate = new Date(dateDebutPlanning.value);
    const endDate   = new Date(dateFinPlanning.value);

    // Vérifier si la date de fin est après la date de début
    if (startDate && endDate && endDate < startDate) {
      errorMessage.style.display = 'block'; // Afficher le message d'erreur
      return false;
    } else {
      errorMessage.style.display = 'none';
      return true;
    }
  }

  // Valider la date lors de l'envoi du formulaire
  myForm.addEventListener('submit', function(event) {
    if (!validateDates()) {
        event.preventDefault(); // Empêcher l'envoi du formulaire si les dates ne sont pas valides
    }
  });
  
  /**
   * Fonction pour switcher les champs disabled du formulaire avec id=formId
   * @param {string} formId 
   */
  function toggleFormDisabled(formId) {
    const form = document.getElementById(formId);
    if (!form) {
        console.error(`Element with ID "${formId}" not found.`);
        return;
    }
    const isDisabled = form.getAttribute('disabledEdit'); 

    if (form) {
        Array.from(form.elements).forEach((element) => {
          if (isDisabled == 'true') {
            element.disabled = isDisabled;
          }
        });
    }
  }

  toggleFormDisabled('formTik');

  /** 
   * AJOUT DE FICHIERS DANS LES COMMENTAIRES
   */
  const fileInput         = document.querySelector(".file-input");
  const fileList          = document.getElementById("file-list");
  const paperclipIcon     = document.getElementById("paperclip-icon");
  const fileUploadWrapper = document.getElementById("file-upload-wrapper");

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
    fileUploadWrapper.style.display = "block";
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

  function startLoading(spinner) {
    setTimeout(() => {
      spinner.remove();
    }, 2000);
  }
});

/**
 * recupérer le catégorie et afficher les sous catégorie et autre categorie correspondant
 */
const categorieInput = document.querySelector(".categorie");
const sousCategorieInput = document.querySelector(".sous-categorie");
const autreCategorieInput = document.querySelector(".autre-categorie");

if (categorieInput !== null) {
  //AFFICHAGE SOUS CATEGORIES
categorieInput.addEventListener("change", selectCategorieSousCategorie);

function selectCategorieSousCategorie() {
  const categorie = categorieInput.value;

  if (categorie === "") {
    while (sousCategorieInput.options.length > 0) {
      sousCategorieInput.remove(0);
    }

    const defaultOption = document.createElement("option");
    defaultOption.value = "";
    defaultOption.text = " -- Choisir une sous-catégorie -- ";
    sousCategorieInput.add(defaultOption);
    return; // Sortir de la fonction
  }

  let url = `/Hffintranet/api/sous-categorie-fetch/${categorie}`;
  fetch(url)
    .then((response) => response.json())
    .then((sousCategories) => {
      console.log(sousCategories);

      // Supprimer toutes les options existantes
      while (sousCategorieInput.options.length > 0) {
        sousCategorieInput.remove(0);
      }

      const defaultOption = document.createElement("option");
      defaultOption.value = "";
      defaultOption.text = " -- Choisir une sous-catégorie -- ";
      sousCategorieInput.add(defaultOption);

      // Ajouter les nouvelles options à partir du tableau services
      for (var i = 0; i < sousCategories.length; i++) {
        var option = document.createElement("option");
        option.value = sousCategories[i].value;
        option.text = sousCategories[i].text;
        sousCategorieInput.add(option);
      }

      //Afficher les nouvelles valeurs et textes des options
      for (var i = 0; i < sousCategorieInput.options.length; i++) {
        var option = sousCategorieInput.options[i];
        console.log("Value: " + option.value + ", Text: " + option.text);
      }
    })
    .catch((error) => console.error("Error:", error));

  //AFFICHAGE AUTRES CATEGORIE
  sousCategorieInput.addEventListener(
    "change",
    selectSousCategorieAutresCategories
  );

  function selectSousCategorieAutresCategories() {
    const sousCategorie = sousCategorieInput.value;

    if (sousCategorie === "") {
      while (autreCategorieInput.options.length > 0) {
        autreCategorieInput.remove(0);
      }

      const defaultOption = document.createElement("option");
      defaultOption.value = "";
      defaultOption.text = " -- Choisir une sous catégorie -- ";
      autreCategorieInput.add(defaultOption);
      return; // Sortir de la fonction
    }

    console.log(sousCategorie);

    let url = `/Hffintranet/api/autres-categorie-fetch/${sousCategorie}`;
    fetch(url)
      .then((response) => response.json())
      .then((autresCategories) => {
        console.log(autresCategories);

        // Supprimer toutes les options existantes
        while (autreCategorieInput.options.length > 0) {
          autreCategorieInput.remove(0);
        }

        const defaultOption = document.createElement("option");
        defaultOption.value = "";
        defaultOption.text = " -- Choisir une autre categorie-- ";
        autreCategorieInput.add(defaultOption);

        // Ajouter les nouvelles options à partir du tableau services
        for (var i = 0; i < autresCategories.length; i++) {
          var option = document.createElement("option");
          option.value = autresCategories[i].value;
          option.text = autresCategories[i].text;
          autreCategorieInput.add(option);
        }

        //Afficher les nouvelles valeurs et textes des options
        for (var i = 0; i < autreCategorieInput.options.length; i++) {
          var option = autreCategorieInput.options[i];
          console.log("Value: " + option.value + ", Text: " + option.text);
        }
      })
      .catch((error) => console.error("Error:", error));
  }
}
}
