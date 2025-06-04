import {
  initializeFileHandlersNouveau,
  initializeFileHandlersMultiple,
} from "../../utils/file_upload_Utils.js";
/** ============================
 * FICHIER
 * =============================*/
const fileInput1 = document.querySelector("#da_soumission_bc_pieceJoint1");
initializeFileHandlersNouveau("1", fileInput1);
