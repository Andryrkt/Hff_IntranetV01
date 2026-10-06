import { initializeFileHandlersNouveau } from "../../../utils/file_upload_Utils.js";
import { setupConfirmationButtons } from "../../../utils/ui/boutonConfirmUtils.js";

/**=======================================
 * traitement de telechargement du fichier
 *======================================*/

// Attendre que le DOM soit chargé
document.addEventListener("DOMContentLoaded", function () {
  const fileInput1 = document.querySelector("#validation_devis_pieceJoint01");
  const remoteUrl = document.querySelector("#tab1-tab").dataset.remoteUrl;
  if (fileInput1) initializeFileHandlersNouveau("1", fileInput1, remoteUrl);

  // Gestion de la validation du formulaire
  const form = document.querySelector("#upload-form");
  if (form) {
    form.addEventListener("submit", function (e) {
      // Vérifier si les fichiers requis sont présents
      const fileInput1 = document.querySelector(
        "#validation_devis_pieceJoint01",
      );
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
