import {
  initializeFileHandlersNouveau,
  initializeFileHandlersMultiple,
} from "../../utils/file_upload_Utils.js";
import { setupConfirmationButtons } from "../../utils/ui/boutonConfirmUtils.js";
import {
  registerLocale,
  setLocale,
  formatNumberSpecial,
} from "../../utils/formatNumberUtils.js";

/**=======================================
 * traitement de telechargement du fichier
 *======================================*/

// Attendre que le DOM soit chargé
document.addEventListener("DOMContentLoaded", function () {
  const fileInput1 = document.querySelector("#bc_magasin_pieceJoint01");
  if (fileInput1) {
    initializeFileHandlersNouveau("1", fileInput1);
  }

  const fileInput2 = document.querySelector("#bc_magasin_pieceJoint2");
  if (fileInput2) {
    initializeFileHandlersMultiple("2", fileInput2);
  }

  // Gestion de la validation du formulaire
  const form = document.querySelector("#upload-form");
  if (form) {
    form.addEventListener("submit", function (e) {
      // Vérifier si les fichiers requis sont présents
      const fileInput1 = document.querySelector("#bc_magasin_pieceJoint01");
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

/** ======================================================
 * validation du donnée pour le champ montant bc
 *=========================================================*/
const montantBcInput = document.querySelector("#bc_magasin_montantBc");
registerLocale("fr-custom", { delimiters: { thousands: " ", decimal: "," } }); // Enregistrer une locale personnalisée "fr-custom"
setLocale("fr-custom"); // Utiliser la locale personnalisée
if (montantBcInput) {
  montantBcInput.addEventListener("input", (e) => {
    montantBcInput.value = formatNumberSpecial(montantBcInput.value);
  });
}
