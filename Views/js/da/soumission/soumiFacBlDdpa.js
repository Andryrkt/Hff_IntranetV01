import {
  initializeFileHandlersNouveau,
  initializeFileHandlersMultiple,
} from "../../utils/file_upload_Utils.js";
import { setupConfirmationButtons } from "../../utils/ui/boutonConfirmUtils.js";

/** ============================
 * FICHIER
 * =============================*/
const fileInput1 = document.querySelector(
  "#da_soumission_fac_bl_ddpa_pieceJoint1",
);
initializeFileHandlersNouveau("1", fileInput1);

const fileInput2 = document.querySelector(
  "#da_soumission_fac_bl_ddpa_pieceJoint2",
);
initializeFileHandlersMultiple("2", fileInput2);

/**==================================================
 * sweetalert pour le bouton Enregistrer
 *==================================================*/
setupConfirmationButtons();
