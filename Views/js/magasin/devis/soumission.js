import { initializeFileHandlers } from "../../utils/file_upload_Utils.js";

/**=======================================
 * traitement de telechargement du fichier
 *======================================*/

const fileInput = document.querySelector(`#devis_magasin_pieceJoint01`);
initializeFileHandlers(1, fileInput);
