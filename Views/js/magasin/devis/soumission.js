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

/**==================================================
 * sweetalert pour le bouton Enregistrer
 *==================================================*/
setupConfirmationButtons();

/**===================================================
 * devis magasin - est validation PM
 *===================================================*/

const devisPMCheckboxOui = document.getElementById(
  "devis_magasin_estValidationPm_0",
);
const devisPMCheckboxNon = document.getElementById(
  "devis_magasin_estValidationPm_1",
);
const tacheValidateurInput = document.querySelectorAll(
  "#devis_magasin_tacheValidateur input",
);

function disableTacheValidateurInput(bool) {
  tacheValidateurInput.forEach((input) => {
    if (input.disabled && bool) {
      return (input.disabled = false);
    } else {
      return (input.disabled = true);
    }
  });
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
