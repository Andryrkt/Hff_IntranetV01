import {
  initializeFileHandlersNouveau,
  initializeFileHandlersMultiple,
  initializeFileHandlersExcel,
} from "../../utils/file_upload_Utils.js";
import { setupConfirmationButtons } from "../../utils/ui/boutonConfirmUtils.js";

/**=======================================
 * traitement de telechargement du fichier
 *======================================*/

// Attendre que le DOM soit chargé
document.addEventListener("DOMContentLoaded", function () {
  const fileInput1 = document.querySelector("#devis_magasin_pieceJoint01");
  const remoteUrl = document.querySelector("#tab1-tab").dataset.remoteUrl;
  if (fileInput1) initializeFileHandlersNouveau("1", fileInput1, remoteUrl);

  const fileInput2 = document.querySelector("#devis_magasin_pieceJoint2");
  if (fileInput2) initializeFileHandlersMultiple("2", fileInput2);

  const fileInput3 = document.querySelector("#devis_magasin_pieceJointExcel");
  if (fileInput3) initializeFileHandlersExcel("3", fileInput3);

  // Gestion de la validation du formulaire
  const form = document.querySelector("#upload-form");
  if (form) {
    form.addEventListener("submit", function (e) {
      // Vérifier si les fichiers requis sont présents
      const fileInput1 = document.querySelector("#devis_magasin_pieceJoint01");
      if (fileInput1 && fileInput1.files.length === 0) {
        e.preventDefault();
        alert("Veuillez sélectionner un fichier devis.");
        return false;
      }
    });
  }
});

/**===================================================
 * devis magasin - est validation PM
 *===================================================*/
document.addEventListener("DOMContentLoaded", function () {
  const devisPMCheckboxOui = document.getElementById(
    "devis_magasin_estValidationPm_0",
  );
  const devisPMCheckboxNon = document.getElementById(
    "devis_magasin_estValidationPm_1",
  );
  const tacheValidateurInput = document.querySelectorAll(
    "#devis_magasin_tacheValidateur input",
  );
  const labelTacheValidateur = document.querySelector(
    "#devis_magasin_tacheValidateur",
  );
  const legendElement = labelTacheValidateur
    .closest("fieldset")
    .querySelector("legend");

  function disableTacheValidateurInput(shouldEnable) {
    tacheValidateurInput.forEach((input) => {
      input.disabled = !shouldEnable;
      input.required = shouldEnable;
    });

    if (shouldEnable) {
      legendElement.classList.remove("text-secondary");
    } else {
      legendElement.classList.add("text-secondary");
    }
  }

  devisPMCheckboxOui.addEventListener("change", function () {
    if (this.checked) {
      disableTacheValidateurInput(true);
    } else {
      disableTacheValidateurInput(false);
    }
  });

  devisPMCheckboxNon.addEventListener("change", function () {
    if (this.checked) {
      disableTacheValidateurInput(false);
    } else {
      disableTacheValidateurInput(true);
    }
  });
});

/**==================================================
 * sweetalert pour le bouton Enregistrer
 *==================================================*/
setupConfirmationButtons();
