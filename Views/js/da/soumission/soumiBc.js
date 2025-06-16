import { initializeFileHandlersNouveau } from "../../utils/file_upload_Utils.js";
import { setupConfirmationButtons } from "../../utils/ui/boutonConfirmUtils.js";
/** ============================
 * FICHIER
 * =============================*/
const fileInput1 = document.querySelector("#da_soumission_bc_pieceJoint1");
initializeFileHandlersNouveau("1", fileInput1);

/**==================================================
 * sweetalert pour le bouton Enregistrer
 *==================================================*/
setupConfirmationButtons();
