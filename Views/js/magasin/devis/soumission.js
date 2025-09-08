import { initializeFileHandlers } from "../../utils/file_upload_Utils.js";
import { setupConfirmationButtons } from "../../utils/ui/boutonConfirmUtils.js";

/**=======================================
 * traitement de telechargement du fichier
 *======================================*/

const fileInput = document.querySelector(`#devis_magasin_pieceJoint01`);
initializeFileHandlers(1, fileInput);


/**==================================================
 * sweetalert pour le bouton Enregistrer
 *==================================================*/
setupConfirmationButtons();