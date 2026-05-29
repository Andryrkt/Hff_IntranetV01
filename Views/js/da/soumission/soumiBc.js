import {
  initializeFileHandlersNouveau,
  initializeFileHandlersMultiple,
} from "../../utils/file_upload_Utils.js";
import { setupConfirmationButtons } from "../../utils/ui/boutonConfirmUtils.js";
/** ============================
 * FICHIER
 * =============================*/
const fileInput1 = document.querySelector("#da_soumission_bc_pieceJoint1");
initializeFileHandlersNouveau("1", fileInput1);

const fileInput2 = document.querySelector("#da_soumission_bc_pieceJoint2");
initializeFileHandlersMultiple("2", fileInput2);

/**==================================================
 * sweetalert pour le bouton Enregistrer
 *==================================================*/
setupConfirmationButtons();

/** =====================================================
 *  affichage ou non du bouton suivant et enregistrer
 *========================================================*/
const suivantButton = document.querySelector("button[name='suivant']");
const enregistrerButton = document.querySelector("button[name='enregistrer']");
const DdpAOui = document.querySelector(
  "#da_soumission_bc_demandePaiementAvance_0",
);
const DdpANon = document.querySelector(
  "#da_soumission_bc_demandePaiementAvance_1",
);
DdpAOui.addEventListener("change", function () {
  if (DdpAOui.checked) {
    suivantButton.style.display = "block";
    enregistrerButton.style.display = "none";
  } else {
    suivantButton.style.display = "none";
    enregistrerButton.style.display = "block";
  }
});
DdpANon.addEventListener("change", function () {
  if (DdpANon.checked) {
    suivantButton.style.display = "none";
    enregistrerButton.style.display = "block";
  } else {
    suivantButton.style.display = "block";
    enregistrerButton.style.display = "none";
  }
});
