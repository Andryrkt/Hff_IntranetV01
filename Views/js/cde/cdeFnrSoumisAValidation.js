import { initializeFileHandlers } from "../utils/file_upload_Utils.js";
import { setupConfirmationButtons } from "../utils/ui/boutonConfirmUtils.js";

document.addEventListener("DOMContentLoaded", function () {
  /**=================================================
   * FICHIER
   *=================================================*/
  const fileInput = document.querySelector(
    `#cde_fnr_soumis_a_validation_pieceJoint01`
  );

  initializeFileHandlers("1", fileInput);

  /** ====================================================
   * bouton Enregistrer
   *===================================================*/
  setupConfirmationButtons();
});
