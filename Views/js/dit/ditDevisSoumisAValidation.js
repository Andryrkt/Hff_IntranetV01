import { initializeFileHandlers } from "../utils/file_upload_Utils.js";
import { setupConfirmationButtons } from "../utils/ui/boutonConfirmUtils.js";

/**=======================================
 * traitement de telechargement du fichier
 *======================================*/
const fileInput = document.querySelector(
  `#dit_devis_soumis_a_validation_pieceJoint01`
);
initializeFileHandlers(1, fileInput);

/**==================================================
 * sweetalert pour le bouton Enregistrer
 *==================================================*/

setupConfirmationButtons();
