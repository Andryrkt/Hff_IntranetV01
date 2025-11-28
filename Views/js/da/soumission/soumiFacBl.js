import {
  initializeFileHandlersNouveau,
  initializeFileHandlersMultiple,
} from "../../utils/file_upload_Utils.js";
import { setupConfirmationButtons } from "../../utils/ui/boutonConfirmUtils.js";

/** ============================
 * CHAMP FORMULAIRE
 * =============================*/
const numLivInput = document.getElementById("da_soumission_fac_bl_numLiv");
numLivInput.addEventListener("input", function () {
  this.value = this.value.replace(/[^\d]/g, "").slice(0, 8);
});

/** ============================
 * FICHIER
 * =============================*/
const fileInput1 = document.querySelector("#da_soumission_fac_bl_pieceJoint1");
initializeFileHandlersNouveau("1", fileInput1);

const fileInput2 = document.querySelector("#da_soumission_fac_bl_pieceJoint2");
initializeFileHandlersMultiple("2", fileInput2);

/**==================================================
 * sweetalert pour le bouton Enregistrer
 *==================================================*/
setupConfirmationButtons();
