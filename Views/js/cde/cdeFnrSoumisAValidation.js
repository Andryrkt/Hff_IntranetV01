import {
  initializeFileHandlersNouveau,
  initializeFileHandlersMultiple,
} from "../utils/file_upload_Utils.js";
import { setupConfirmationButtons } from "../utils/ui/boutonConfirmUtils.js";

document.addEventListener("DOMContentLoaded", function () {
  /**=================================================
   * FICHIER
   *=================================================*/
  const fileInput1 = document.querySelector(
    `#cde_fnr_soumis_a_validation_pieceJoint01`
  );
  initializeFileHandlersNouveau("1", fileInput1);

  const fileInput2 = document.querySelector(
    `#cde_fnr_soumis_a_validation_pieceJoint02`
  );
  initializeFileHandlersMultiple("2", fileInput2);

  /** ====================================================
   * bouton Enregistrer
   *===================================================*/
  setupConfirmationButtons();
});
